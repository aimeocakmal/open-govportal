<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinisterProfile extends Model
{
    /** @use HasFactory<\Database\Factories\MinisterProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'title_ms',
        'title_en',
        'bio_ms',
        'bio_en',
        'photo',
        'is_current',
        'appointed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'appointed_at' => 'date',
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
