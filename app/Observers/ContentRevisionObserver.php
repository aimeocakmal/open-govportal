<?php

namespace App\Observers;

use App\Models\Concerns\HasContentRevisions;
use App\Models\ContentRevision;
use Illuminate\Database\Eloquent\Model;

class ContentRevisionObserver
{
    /**
     * Before updating, snapshot the current state as a revision.
     */
    public function updating(Model $model): void
    {
        if (! in_array(HasContentRevisions::class, class_uses_recursive($model))) {
            return;
        }

        // Only create revision if content-related fields changed
        if (empty($model->getDirty())) {
            return;
        }

        // Use original attributes to snapshot the state before the update
        ContentRevision::create([
            'revisionable_type' => $model->getMorphClass(),
            'revisionable_id' => $model->getKey(),
            'user_id' => auth()->id(),
            'reason' => null,
            'content' => $model->getOriginal(),
        ]);
    }
}
