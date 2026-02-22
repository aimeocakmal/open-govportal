<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SearchableContent extends Model
{
    /** This table has no created_at column. */
    const CREATED_AT = null;

    protected $table = 'searchable_content';

    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'title_ms',
        'title_en',
        'content_ms',
        'content_en',
        'url_ms',
        'url_en',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Insert or update the searchable content row for the given model.
     *
     * @param  array{title_ms?: string|null, title_en?: string|null, content_ms?: string|null, content_en?: string|null, url_ms?: string|null, url_en?: string|null, priority?: int}  $data
     */
    public static function upsertForModel(Model $model, array $data): void
    {
        static::updateOrCreate(
            [
                'searchable_type' => $model->getMorphClass(),
                'searchable_id' => $model->getKey(),
            ],
            $data,
        );
    }

    /**
     * Remove the searchable content row for the given model.
     */
    public static function removeForModel(Model $model): void
    {
        static::query()
            ->where('searchable_type', $model->getMorphClass())
            ->where('searchable_id', $model->getKey())
            ->delete();
    }
}
