<?php

namespace Tests\Feature;

use App\Jobs\GenerateConversationTitleJob;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\Setting;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class GenerateConversationTitleJobTest extends TestCase
{
    use RefreshDatabase;

    private function createConversationWithMessages(int $messageCount = 6): AiChatConversation
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'locale' => 'ms',
            'message_count' => $messageCount,
            'started_at' => now(),
        ]);

        for ($i = 0; $i < $messageCount; $i++) {
            AiChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => $i % 2 === 0 ? 'user' : 'assistant',
                'content' => 'Message '.$i,
                'created_at' => now(),
            ]);
        }

        return $conversation;
    }

    public function test_generates_title_from_llm_json_response(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test'));

        $conversation = $this->createConversationWithMessages();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(true);
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('{"title": "Soalan tentang perkhidmatan", "tags": ["soalan-umum", "perkhidmatan"]}');
            $mock->shouldReceive('getLastUsage')->andReturn(null);
        });

        GenerateConversationTitleJob::dispatchSync($conversation->id);

        $conversation->refresh();
        $this->assertEquals('Soalan tentang perkhidmatan', $conversation->title);
        $this->assertEquals(['soalan-umum', 'perkhidmatan'], $conversation->tags);
    }

    public function test_falls_back_to_first_user_message_on_invalid_json(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test'));

        $conversation = $this->createConversationWithMessages();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(true);
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('This is not valid JSON');
            $mock->shouldReceive('getLastUsage')->andReturn(null);
        });

        GenerateConversationTitleJob::dispatchSync($conversation->id);

        $conversation->refresh();
        $this->assertEquals('Message 0', $conversation->title);
    }

    public function test_skips_if_conversation_already_has_title(): void
    {
        $conversation = $this->createConversationWithMessages();
        $conversation->update(['title' => 'Existing title']);

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldNotReceive('chat');
        });

        GenerateConversationTitleJob::dispatchSync($conversation->id);

        $conversation->refresh();
        $this->assertEquals('Existing title', $conversation->title);
    }

    public function test_skips_if_conversation_not_found(): void
    {
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldNotReceive('chat');
        });

        // Should not throw
        GenerateConversationTitleJob::dispatchSync(99999);
    }

    public function test_skips_if_ai_not_available(): void
    {
        $conversation = $this->createConversationWithMessages();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(false);
            $mock->shouldNotReceive('chat');
        });

        GenerateConversationTitleJob::dispatchSync($conversation->id);

        $conversation->refresh();
        $this->assertNull($conversation->title);
    }

    public function test_skips_if_empty_response(): void
    {
        Setting::set('ai_llm_api_key', Crypt::encrypt('sk-test'));

        $conversation = $this->createConversationWithMessages();

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('isAvailable')->andReturn(true);
            $mock->shouldReceive('chat')->once()->andReturn('');
            $mock->shouldReceive('getLastUsage')->andReturn(null);
        });

        GenerateConversationTitleJob::dispatchSync($conversation->id);

        $conversation->refresh();
        $this->assertNull($conversation->title);
    }
}
