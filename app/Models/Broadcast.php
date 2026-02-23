<?php

namespace App\Models;

use App\Models\Concerns\HasContentRevisions;
use App\Models\Concerns\HasEmbeddings;
use App\Models\Concerns\HasSearchableContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Broadcast extends Model
{
    use HasContentRevisions;
    use HasEmbeddings;

    /** @use HasFactory<\Database\Factories\BroadcastFactory> */
    use HasFactory;

    use HasSearchableContent;
    use LogsActivity;

    protected $fillable = [
        'title_ms',
        'title_en',
        'slug',
        'content_ms',
        'content_en',
        'excerpt_ms',
        'excerpt_en',
        'featured_image',
        'type',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to only published records.
     */
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
            'content_ms' => strip_tags($this->content_ms ?? ''),
            'content_en' => strip_tags($this->content_en ?? ''),
            'url_ms' => '/ms/siaran/'.$this->slug,
            'url_en' => '/en/siaran/'.$this->slug,
            'priority' => 20,
        ];
    }

    public function toEmbeddableContent(): array
    {
        return [
            'fields' => ['title', 'content', 'excerpt'],
            'bilingual' => ['title', 'content', 'excerpt'],
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'status', 'published_at', 'type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
