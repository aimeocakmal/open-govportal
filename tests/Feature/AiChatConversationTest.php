<?php

namespace Tests\Feature;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_conversation(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session-123',
            'ip_address' => '127.0.0.1',
            'locale' => 'ms',
            'started_at' => now(),
        ]);

        $this->assertDatabaseHas('ai_chat_conversations', [
            'id' => $conversation->id,
            'session_id' => 'test-session-123',
            'locale' => 'ms',
        ]);
    }

    public function test_tags_cast_to_array(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'tags' => ['soalan-umum', 'teknikal'],
            'started_at' => now(),
        ]);

        $conversation = $conversation->fresh();
        $this->assertIsArray($conversation->tags);
        $this->assertCount(2, $conversation->tags);
        $this->assertContains('soalan-umum', $conversation->tags);
    }

    public function test_total_tokens_helper(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'total_prompt_tokens' => 500,
            'total_completion_tokens' => 300,
            'started_at' => now(),
        ]);

        $this->assertEquals(800, $conversation->totalTokens());
    }

    public function test_messages_relationship(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
        ]);

        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello',
            'created_at' => now(),
        ]);

        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hi there',
            'prompt_tokens' => 10,
            'completion_tokens' => 5,
            'created_at' => now(),
        ]);

        $this->assertCount(2, $conversation->messages);
    }

    public function test_cascade_delete_removes_messages(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
        ]);

        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Test message',
            'created_at' => now(),
        ]);

        $this->assertDatabaseCount('ai_chat_messages', 1);

        $conversation->delete();

        $this->assertDatabaseCount('ai_chat_messages', 0);
    }

    public function test_factory_creates_valid_conversation(): void
    {
        $conversation = AiChatConversation::factory()->create();

        $this->assertDatabaseHas('ai_chat_conversations', ['id' => $conversation->id]);
        $this->assertNotNull($conversation->session_id);
    }

    public function test_factory_with_title_state(): void
    {
        $conversation = AiChatConversation::factory()->withTitle()->create();

        $this->assertNotNull($conversation->title);
        $this->assertNotNull($conversation->summary);
        $this->assertIsArray($conversation->tags);
    }

    public function test_factory_ended_state(): void
    {
        $conversation = AiChatConversation::factory()->ended()->create();

        $this->assertNotNull($conversation->ended_at);
    }

    public function test_factory_with_messages_state(): void
    {
        $conversation = AiChatConversation::factory()->withMessages(4)->create();

        $this->assertCount(4, $conversation->messages);
        $this->assertEquals(4, $conversation->fresh()->message_count);
    }

    public function test_timestamps_are_cast_correctly(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
            'last_message_at' => now(),
            'ended_at' => now(),
        ]);

        $conversation = $conversation->fresh();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $conversation->started_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $conversation->last_message_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $conversation->ended_at);
    }

    public function test_nullable_fields(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
        ]);

        $conversation = $conversation->fresh();
        $this->assertNull($conversation->title);
        $this->assertNull($conversation->summary);
        $this->assertNull($conversation->tags);
        $this->assertNull($conversation->ended_at);
    }
}
