<?php

namespace Tests\Feature;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeOldChatConversationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_old_conversations(): void
    {
        // Old conversation (100 days ago)
        $old = AiChatConversation::create([
            'session_id' => 'old-session',
            'started_at' => now()->subDays(100),
            'last_message_at' => now()->subDays(100),
        ]);
        AiChatMessage::create([
            'conversation_id' => $old->id,
            'role' => 'user',
            'content' => 'Old message',
            'created_at' => now()->subDays(100),
        ]);

        // Recent conversation
        $recent = AiChatConversation::create([
            'session_id' => 'recent-session',
            'started_at' => now()->subDays(10),
            'last_message_at' => now()->subDays(10),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseMissing('ai_chat_conversations', ['id' => $old->id]);
        $this->assertDatabaseMissing('ai_chat_messages', ['conversation_id' => $old->id]);
        $this->assertDatabaseHas('ai_chat_conversations', ['id' => $recent->id]);
    }

    public function test_purges_old_usage_logs(): void
    {
        $old = AiUsageLog::create([
            'operation' => 'chat',
            'source' => 'public_chat',
        ]);
        AiUsageLog::query()->where('id', $old->id)->update(['created_at' => now()->subDays(100)]);

        AiUsageLog::create([
            'operation' => 'chat',
            'source' => 'public_chat',
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_usage_logs', 1);
    }

    public function test_respects_custom_retention_setting(): void
    {
        Setting::set('ai_chat_retention_days', '30');

        // 40 days old — should be purged with 30-day retention
        AiChatConversation::create([
            'session_id' => 'old-session',
            'started_at' => now()->subDays(40),
            'last_message_at' => now()->subDays(40),
        ]);

        // 20 days old — should be kept
        AiChatConversation::create([
            'session_id' => 'recent-session',
            'started_at' => now()->subDays(20),
            'last_message_at' => now()->subDays(20),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);
    }

    public function test_minimum_retention_is_seven_days(): void
    {
        Setting::set('ai_chat_retention_days', '1');

        // 5 days old — should NOT be purged (min retention is 7)
        AiChatConversation::create([
            'session_id' => 'recent-session',
            'started_at' => now()->subDays(5),
            'last_message_at' => now()->subDays(5),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);
    }

    public function test_dry_run_does_not_delete(): void
    {
        AiChatConversation::create([
            'session_id' => 'old-session',
            'started_at' => now()->subDays(100),
            'last_message_at' => now()->subDays(100),
        ]);

        $this->artisan('ai:purge-conversations', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);
    }
}
