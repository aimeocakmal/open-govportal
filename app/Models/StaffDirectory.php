<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDirectory extends Model
{
    /** @use HasFactory<\Database\Factories\StaffDirectoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'position_ms',
        'position_en',
        'department_ms',
        'department_en',
        'division_ms',
        'division_en',
        'email',
        'phone',
        'fax',
        'photo',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
