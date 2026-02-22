<?php

namespace App\Models\Concerns;

use App\Models\SearchableContent;

/**
 * Provides full-text search indexing for Eloquent models.
 *
 * Models using this trait must implement toSearchableContent()
 * which returns the data to be indexed.
 */
trait HasSearchableContent
{
    /**
     * Return the data to be stored in the searchable_content table.
     *
     * @return array{title_ms?: string|null, title_en?: string|null, content_ms?: string|null, content_en?: string|null, url_ms?: string|null, url_en?: string|null, priority?: int}
     */
    abstract public function toSearchableContent(): array;

    /**
     * Insert or update this model's row in the searchable_content table.
     */
    public function syncSearchContent(): void
    {
        SearchableContent::upsertForModel($this, $this->toSearchableContent());
    }

    /**
     * Remove this model's row from the searchable_content table.
     */
    public function removeSearchContent(): void
    {
        SearchableContent::removeForModel($this);
    }
}
