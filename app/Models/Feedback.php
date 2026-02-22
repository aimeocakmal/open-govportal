<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'feedbacks';

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'page_url',
        'rating',
        'status',
        'reply',
        'replied_at',
        'replied_by',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'replied_at' => 'datetime',
        ];
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reply', 'replied_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
