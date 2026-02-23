<?php

namespace App\Jobs;

use App\Models\Concerns\HasEmbeddings;
use App\Models\ContentEmbedding;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $modelClass,
        public int $modelId,
    ) {
        $this->onQueue('embeddings');
    }

    public function handle(AiService $aiService): void
    {
        // pgvector only works on PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        /** @var Model|null $model */
        $model = $this->modelClass::find($this->modelId);

        if (! $model) {
            return;
        }

        // Delete existing embeddings for this model
        ContentEmbedding::where('embeddable_type', $this->modelClass)
            ->where('embeddable_id', $this->modelId)
            ->delete();

        if (! $aiService->isEmbeddingAvailable()) {
            Log::info('GenerateEmbeddingJob: skipping — embedding API not configured', [
                'model' => $this->modelClass,
                'id' => $this->modelId,
            ]);

            return;
        }

        if (! in_array(HasEmbeddings::class, class_uses_recursive($model))) {
            return;
        }

        $config = $model->toEmbeddableContent();
        $fields = $config['fields'] ?? [];
        $bilingualFields = $config['bilingual'] ?? [];

        foreach (['ms', 'en'] as $locale) {
            $text = $this->buildText($model, $fields, $bilingualFields, $locale);

            if (trim($text) === '') {
                continue;
            }

            $chunks = $this->chunkText($text);

            foreach ($chunks as $index => $chunk) {
                $embedding = $aiService->embed($chunk);

                if (empty($embedding)) {
                    Log::warning('GenerateEmbeddingJob: empty embedding returned', [
                        'model' => $this->modelClass,
                        'id' => $this->modelId,
                        'locale' => $locale,
                        'chunk_index' => $index,
                    ]);

                    continue;
                }

                ContentEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => $this->modelClass,
                        'embeddable_id' => $this->modelId,
                        'chunk_index' => $index,
                        'locale' => $locale,
                    ],
                    [
                        'content' => $chunk,
                        'embedding' => $embedding,
                        'metadata' => $this->buildMetadata($model, $locale),
                    ]
                );
            }
        }
    }

    /**
     * Build the text to embed for a given locale.
     *
     * @param  array<string>  $fields
     * @param  array<string>  $bilingualFields
     */
    private function buildText(Model $model, array $fields, array $bilingualFields, string $locale): string
    {
        $parts = [];

        foreach ($fields as $field) {
            if (in_array($field, $bilingualFields)) {
                $value = $model->getAttribute("{$field}_{$locale}") ?? '';
            } else {
                $value = $model->getAttribute($field) ?? '';
            }

            $value = strip_tags((string) $value);

            if (trim($value) !== '') {
                $parts[] = $value;
            }
        }

        return implode("\n\n", $parts);
    }

    /**
     * Split text into chunks of ~2000 characters at word boundaries.
     *
     * @return array<int, string>
     */
    private function chunkText(string $text, int $maxChars = 2000): array
    {
        $text = trim($text);

        if (mb_strlen($text) <= $maxChars) {
            return [$text];
        }

        $chunks = [];
        $words = preg_split('/\s+/', $text);
        $current = '';

        foreach ($words as $word) {
            if ($current !== '' && mb_strlen($current) + mb_strlen($word) + 1 > $maxChars) {
                $chunks[] = trim($current);
                $current = $word;
            } else {
                $current .= ($current !== '' ? ' ' : '').$word;
            }
        }

        if (trim($current) !== '') {
            $chunks[] = trim($current);
        }

        return $chunks;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMetadata(Model $model, string $locale): array
    {
        $metadata = [
            'type' => class_basename($this->modelClass),
        ];

        if ($model->getAttribute('slug')) {
            $metadata['slug'] = $model->getAttribute('slug');
        }

        $titleField = "title_{$locale}";
        if ($model->getAttribute($titleField)) {
            $metadata['title'] = $model->getAttribute($titleField);
        } elseif ($model->getAttribute('name')) {
            $metadata['title'] = $model->getAttribute('name');
        }

        return $metadata;
    }
}
