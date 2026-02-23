<?php

namespace Tests\Feature;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_message(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
        ]);

        $message = AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello world',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'id' => $message->id,
            'role' => 'user',
            'content' => 'Hello world',
        ]);
    }

    public function test_belongs_to_conversation(): void
    {
        $conversation = AiChatConversation::create([
            'session_id' => 'test-session',
            'started_at' => now(),
        ]);

        $message = AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Reply text',
            'created_at' => now(),
        ]);

        $this->assertEquals($conversation->id, $message->conversation->id);
    }

    public function test_factory_user_state(): void
    {
        $conversation = AiChatConversation::factory()->create();
        $message = AiChatMessage::factory()->user()->create([
            'conversation_id' => $conversation->id,
        ]);

        $this->assertEquals('user', $message->role);
        $this->assertNull($message->prompt_tokens);
        $this->assertNull($message->completion_tokens);
    }

    public function test_factory_assistant_state(): void
    {
        $conversation = AiChatConversation::factory()->create();
        $message = AiChatMessage::factory()->assistant()->create([
            'conversation_id' => $conversation->id,
        ]);

        $this->assertEquals('assistant', $message->role);
        $this->assertNotNull($message->prompt_tokens);
        $this->assertNotNull($message->completion_tokens);
        $this->assertNotNull($message->duration_ms);
    }

    public function test_casts(): void
    {
        $conversation = AiChatConversation::factory()->create();
        $message = AiChatMessage::factory()->assistant()->create([
            'conversation_id' => $conversation->id,
        ]);

        $message = $message->fresh();
        $this->assertIsInt($message->prompt_tokens);
        $this->assertIsInt($message->completion_tokens);
        $this->assertIsInt($message->duration_ms);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $message->created_at);
    }
}
