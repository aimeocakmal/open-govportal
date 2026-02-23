<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentEmbedding extends Model
{
    /** @use HasFactory<\Database\Factories\ContentEmbeddingFactory> */
    use HasFactory;

    protected $fillable = [
        'embeddable_type',
        'embeddable_id',
        'chunk_index',
        'locale',
        'content',
        'embedding',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'chunk_index' => 'integer',
            'embedding' => 'array',
            'metadata' => 'array',
        ];
    }

    public function embeddable(): MorphTo
    {
        return $this->morphTo();
    }
}
