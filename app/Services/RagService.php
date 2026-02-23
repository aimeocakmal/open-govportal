<?php

namespace App\Services;

use App\Models\ContentEmbedding;
use Illuminate\Support\Facades\DB;

class RagService
{
    public function __construct(private AiService $ai) {}

    /**
     * Retrieve the most relevant content chunks for a given query.
     *
     * @return array<int, array{content: string, metadata: array<string, mixed>}>
     */
    public function retrieveChunks(string $query, string $locale, int $limit = 5): array
    {
        // pgvector similarity search only works on PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return [];
        }

        if (! $this->ai->isEmbeddingAvailable()) {
            return [];
        }

        $embedding = $this->ai->embed($query);

        if (empty($embedding)) {
            return [];
        }

        $results = ContentEmbedding::query()
            ->where('locale', $locale)
            ->whereVectorSimilarTo('embedding', $embedding, minSimilarity: 0.3)
            ->limit($limit)
            ->get(['content', 'metadata']);

        return $results->map(fn (ContentEmbedding $row): array => [
            'content' => $row->content,
            'metadata' => $row->metadata ?? [],
        ])->all();
    }

    /**
     * Format retrieved chunks into a context string for the LLM prompt.
     */
    public function buildContext(array $chunks): string
    {
        if (empty($chunks)) {
            return '';
        }

        return collect($chunks)
            ->map(fn (array $chunk, int $i): string => '['.(__('ai.source').' '.($i + 1))."]\n".$chunk['content'])
            ->implode("\n\n");
    }
}
