<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name_ms',
        'name_en',
        'slug',
        'description_ms',
        'description_en',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PageCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function staticPages(): HasMany
    {
        return $this->hasMany(StaticPage::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
