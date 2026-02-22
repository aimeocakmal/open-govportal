<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HeroBanner extends Model
{
    /** @use HasFactory<\Database\Factories\HeroBannerFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'title_ms',
        'title_en',
        'subtitle_ms',
        'subtitle_en',
        'image',
        'image_alt_ms',
        'image_alt_en',
        'cta_label_ms',
        'cta_label_en',
        'cta_url',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'is_active', 'sort_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
