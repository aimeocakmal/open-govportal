<?php

namespace App\Observers;

use App\Models\Concerns\HasSearchableContent;
use Illuminate\Database\Eloquent\Model;

class SearchContentObserver
{
    /**
     * Handle the "saved" event (covers both created and updated).
     *
     * For models with a status field, only index published content.
     * Draft or non-published content is removed from the search index.
     */
    public function saved(Model $model): void
    {
        if (! in_array(HasSearchableContent::class, class_uses_recursive($model))) {
            return;
        }

        // If the model has a status field, only index when published
        if (isset($model->status)) {
            if ($model->status === 'published') {
                $model->syncSearchContent();
            } else {
                $model->removeSearchContent();
            }

            return;
        }

        // Models without a status field (e.g. StaffDirectory) are always indexed
        $model->syncSearchContent();
    }

    /**
     * Handle the "deleted" event — remove from search index.
     */
    public function deleted(Model $model): void
    {
        if (! in_array(HasSearchableContent::class, class_uses_recursive($model))) {
            return;
        }

        $model->removeSearchContent();
    }
}
