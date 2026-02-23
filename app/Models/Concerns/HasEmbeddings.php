<?php

namespace App\Models\Concerns;

use App\Models\ContentEmbedding;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasEmbeddings
{
    public function embeddings(): MorphMany
    {
        return $this->morphMany(ContentEmbedding::class, 'embeddable');
    }

    /**
     * Return the fields to embed for each locale.
     *
     * Bilingual fields should omit the locale suffix — e.g. 'title' not 'title_ms'.
     * The embedding job appends '_{locale}' automatically.
     * Non-bilingual fields (like 'name') are used as-is for both locales.
     *
     * @return array{fields: array<string>, bilingual: array<string>}
     */
    abstract public function toEmbeddableContent(): array;
}
