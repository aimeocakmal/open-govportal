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

class Achievement extends Model
{
    use HasContentRevisions;

    /** @use HasFactory<\Database\Factories\AchievementFactory> */
    use HasFactory;

    use HasSearchableContent;
    use LogsActivity;

    protected $fillable = [
        'title_ms',
        'title_en',
        'slug',
        'description_ms',
        'description_en',
        'date',
        'icon',
        'is_featured',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_featured' => 'boolean',
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
            'url_ms' => '/ms/pencapaian/'.$this->slug,
            'url_en' => '/en/pencapaian/'.$this->slug,
            'priority' => 10,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'status', 'published_at', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
