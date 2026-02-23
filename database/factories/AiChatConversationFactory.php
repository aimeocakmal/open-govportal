<?php

namespace Database\Factories;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiChatConversation> */
class AiChatConversationFactory extends Factory
{
    protected $model = AiChatConversation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'title' => null,
            'summary' => null,
            'tags' => null,
            'locale' => fake()->randomElement(['ms', 'en']),
            'message_count' => 0,
            'total_prompt_tokens' => 0,
            'total_completion_tokens' => 0,
            'started_at' => now(),
            'last_message_at' => null,
            'ended_at' => null,
        ];
    }

    public function withTitle(): static
    {
        return $this->state(fn (array $attributes): array => [
            'title' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'tags' => fake()->randomElements(['soalan-umum', 'dasar', 'perkhidmatan', 'teknikal', 'aduan'], 2),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'ended_at' => now(),
        ]);
    }

    public function withMessages(int $count = 4): static
    {
        return $this->afterCreating(function (AiChatConversation $conversation) use ($count) {
            $totalPromptTokens = 0;
            $totalCompletionTokens = 0;

            for ($i = 0; $i < $count; $i++) {
                $isUser = $i % 2 === 0;
                $message = AiChatMessage::factory()
                    ->{$isUser ? 'user' : 'assistant'}()
                    ->create(['conversation_id' => $conversation->id]);

                if (! $isUser) {
                    $totalPromptTokens += $message->prompt_tokens ?? 0;
                    $totalCompletionTokens += $message->completion_tokens ?? 0;
                }
            }

            $conversation->update([
                'message_count' => $count,
                'total_prompt_tokens' => $totalPromptTokens,
                'total_completion_tokens' => $totalCompletionTokens,
                'last_message_at' => now(),
            ]);
        });
    }
}
