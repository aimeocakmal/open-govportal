<?php

namespace App\Models;

use App\Models\Concerns\HasContentRevisions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StaticPage extends Model
{
    use HasContentRevisions;
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'category_id',
        'title_ms',
        'title_en',
        'slug',
        'content_ms',
        'content_en',
        'excerpt_ms',
        'excerpt_en',
        'status',
        'is_in_sitemap',
        'meta_title_ms',
        'meta_title_en',
        'meta_desc_ms',
        'meta_desc_en',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_in_sitemap' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
