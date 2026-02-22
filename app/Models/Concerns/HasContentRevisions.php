<?php

namespace App\Models\Concerns;

use App\Models\ContentRevision;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasContentRevisions
{
    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable');
    }

    public function createRevision(?string $reason = null): ContentRevision
    {
        return ContentRevision::createFromModel($this, $reason);
    }

    public function restoreRevision(ContentRevision $revision): void
    {
        $restorable = $revision->content;

        // Remove non-fillable keys
        unset($restorable['id'], $restorable['created_at'], $restorable['updated_at']);

        $this->fill($restorable);
        $this->save();
    }

    public function latestRevisions(int $limit = 10): Collection
    {
        return $this->revisions()->latest()->limit($limit)->get();
    }
}
