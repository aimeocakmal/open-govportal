<?php

namespace App\Models;

use App\Observers\MenuItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(MenuItemObserver::class)]
class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label_ms',
        'label_en',
        'url',
        'route_name',
        'route_params',
        'icon',
        'sort_order',
        'target',
        'is_active',
        'is_system',
        'required_roles',
        'mega_columns',
    ];

    protected function casts(): array
    {
        return [
            'route_params' => 'json',
            'required_roles' => 'json',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
            'mega_columns' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
