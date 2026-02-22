<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'filename',
        'original_name',
        'file_url',
        'mime_type',
        'file_size',
        'width',
        'height',
        'alt_ms',
        'alt_en',
        'caption_ms',
        'caption_en',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['filename', 'original_name', 'alt_ms', 'alt_en'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
