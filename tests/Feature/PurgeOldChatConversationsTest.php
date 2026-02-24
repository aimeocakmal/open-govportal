<?php

namespace Tests\Feature;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeOldChatConversationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_archives_and_purges_old_conversations(): void
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

        // Verify archive ZIP was created
        $files = Storage::disk('public')->allFiles('archives/ai-purge');
        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.zip', $files[0]);
    }

    public function test_archive_contains_csv_files(): void
    {
        $old = AiChatConversation::create([
            'session_id' => 'old-session',
            'title' => 'Test Conversation',
            'started_at' => now()->subDays(100),
            'last_message_at' => now()->subDays(100),
        ]);
        AiChatMessage::create([
            'conversation_id' => $old->id,
            'role' => 'user',
            'content' => 'Hello',
            'created_at' => now()->subDays(100),
        ]);

        $oldLog = AiUsageLog::create([
            'operation' => 'chat',
            'source' => 'public_chat',
        ]);
        AiUsageLog::query()->where('id', $oldLog->id)->update(['created_at' => now()->subDays(100)]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $files = Storage::disk('public')->allFiles('archives/ai-purge');
        $this->assertCount(1, $files);

        // Download and inspect the ZIP contents
        $zipContent = Storage::disk('public')->get($files[0]);
        $tempZip = tempnam(sys_get_temp_dir(), 'purge_test_');
        file_put_contents($tempZip, $zipContent);

        $zip = new \ZipArchive;
        $zip->open($tempZip);

        $csvNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $csvNames[] = $zip->getNameIndex($i);
        }

        $this->assertContains('conversations.csv', $csvNames);
        $this->assertContains('messages.csv', $csvNames);
        $this->assertContains('usage_logs.csv', $csvNames);

        // Verify conversations CSV has header + data row
        $conversationsCsv = $zip->getFromName('conversations.csv');
        $lines = array_filter(explode("\n", trim($conversationsCsv)));
        $this->assertCount(2, $lines); // header + 1 data row
        $this->assertStringContainsString('Test Conversation', $conversationsCsv);

        $zip->close();
        unlink($tempZip);
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

    public function test_minimum_retention_is_thirty_days(): void
    {
        Setting::set('ai_chat_retention_days', '1');

        // 25 days old — should NOT be purged (min retention is 30)
        AiChatConversation::create([
            'session_id' => 'recent-session',
            'started_at' => now()->subDays(25),
            'last_message_at' => now()->subDays(25),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);
    }

    public function test_dry_run_does_not_delete_or_archive(): void
    {
        AiChatConversation::create([
            'session_id' => 'old-session',
            'started_at' => now()->subDays(100),
            'last_message_at' => now()->subDays(100),
        ]);

        $this->artisan('ai:purge-conversations', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);

        // No archive should be created
        $files = Storage::disk('public')->allFiles('archives/ai-purge');
        $this->assertCount(0, $files);
    }

    public function test_skips_when_nothing_to_purge(): void
    {
        // Recent conversation — not eligible
        AiChatConversation::create([
            'session_id' => 'recent-session',
            'started_at' => now()->subDays(10),
            'last_message_at' => now()->subDays(10),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $this->assertDatabaseCount('ai_chat_conversations', 1);

        // No archive should be created
        $files = Storage::disk('public')->allFiles('archives/ai-purge');
        $this->assertCount(0, $files);
    }

    public function test_archive_filename_includes_date(): void
    {
        AiChatConversation::create([
            'session_id' => 'old-session',
            'started_at' => now()->subDays(100),
            'last_message_at' => now()->subDays(100),
        ]);

        $this->artisan('ai:purge-conversations')
            ->assertSuccessful();

        $files = Storage::disk('public')->allFiles('archives/ai-purge');
        $this->assertCount(1, $files);

        $today = now()->format('Y-m-d');
        $this->assertStringContainsString($today, $files[0]);
        $this->assertStringContainsString('ai-purge-', $files[0]);
    }
}
