<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyFile extends Model
{
    /** @use HasFactory<\Database\Factories\PolicyFileFactory> */
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'title_ms',
        'title_en',
        'description_ms',
        'description_en',
        'filename',
        'file_url',
        'mime_type',
        'file_size',
        'category',
        'download_count',
        'is_public',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
