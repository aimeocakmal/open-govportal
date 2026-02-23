<?php

namespace App\Observers;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Concerns\HasEmbeddings;
use App\Models\ContentEmbedding;
use App\Models\StaffDirectory;
use Illuminate\Database\Eloquent\Model;

class EmbeddingObserver
{
    /**
     * Handle the "saved" event (covers both created and updated).
     *
     * For models with a status field, only embed published content.
     * For StaffDirectory, only embed active records.
     */
    public function saved(Model $model): void
    {
        if (! in_array(HasEmbeddings::class, class_uses_recursive($model))) {
            return;
        }

        // Models with a status field: only embed when published
        if (isset($model->status)) {
            if ($model->status !== 'published') {
                $this->removeEmbeddings($model);

                return;
            }
        }

        // StaffDirectory: only embed active records
        if ($model instanceof StaffDirectory && ! $model->is_active) {
            $this->removeEmbeddings($model);

            return;
        }

        GenerateEmbeddingJob::dispatch(get_class($model), $model->getKey());
    }

    /**
     * Handle the "deleted" event — remove all embeddings for this model.
     */
    public function deleted(Model $model): void
    {
        if (! in_array(HasEmbeddings::class, class_uses_recursive($model))) {
            return;
        }

        $this->removeEmbeddings($model);
    }

    private function removeEmbeddings(Model $model): void
    {
        ContentEmbedding::where('embeddable_type', get_class($model))
            ->where('embeddable_id', $model->getKey())
            ->delete();
    }
}
