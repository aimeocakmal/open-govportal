<?php

namespace App\Models;

use App\Models\Concerns\HasContentRevisions;
use App\Models\Concerns\HasSearchableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Policy extends Model
{
    use HasContentRevisions;

    /** @use HasFactory<\Database\Factories\PolicyFactory> */
    use HasFactory;

    use HasSearchableContent;
    use LogsActivity;

    protected $fillable = [
        'title_ms',
        'title_en',
        'slug',
        'description_ms',
        'description_en',
        'category',
        'file_url',
        'file_size',
        'download_count',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function toSearchableContent(): array
    {
        return [
            'title_ms' => $this->title_ms,
            'title_en' => $this->title_en,
            'content_ms' => strip_tags($this->description_ms ?? ''),
            'content_en' => strip_tags($this->description_en ?? ''),
            'url_ms' => '/ms/dasar',
            'url_en' => '/en/dasar',
            'priority' => 40,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'status', 'published_at', 'category'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
