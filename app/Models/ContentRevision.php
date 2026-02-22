<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRevision extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'reason',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a revision snapshot from the given model's current state.
     */
    public static function createFromModel(Model $model, ?string $reason = null): self
    {
        return static::create([
            'revisionable_type' => $model->getMorphClass(),
            'revisionable_id' => $model->getKey(),
            'user_id' => auth()->id(),
            'reason' => $reason,
            'content' => $model->getAttributes(),
        ]);
    }
}
