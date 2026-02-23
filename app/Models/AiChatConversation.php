<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatConversation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'ip_address',
        'title',
        'summary',
        'tags',
        'locale',
        'message_count',
        'total_prompt_tokens',
        'total_completion_tokens',
        'started_at',
        'last_message_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'message_count' => 'integer',
            'total_prompt_tokens' => 'integer',
            'total_completion_tokens' => 'integer',
            'started_at' => 'datetime',
            'last_message_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /** @return HasMany<AiChatMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'conversation_id');
    }

    public function totalTokens(): int
    {
        return $this->total_prompt_tokens + $this->total_completion_tokens;
    }
}
