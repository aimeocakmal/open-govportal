<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchOverride extends Model
{
    /** @use HasFactory<\Database\Factories\SearchOverrideFactory> */
    use HasFactory;

    protected $fillable = [
        'query',
        'title_ms',
        'title_en',
        'url',
        'description_ms',
        'description_en',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderByDesc('priority');
    }
}
