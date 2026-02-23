<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'operation',
        'locale',
        'duration_ms',
        'prompt_tokens',
        'completion_tokens',
        'provider',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
