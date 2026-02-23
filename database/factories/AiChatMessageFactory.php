<?php

namespace Database\Factories;

use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiChatMessage> */
class AiChatMessageFactory extends Factory
{
    protected $model = AiChatMessage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'conversation_id' => AiChatConversation::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'prompt_tokens' => null,
            'completion_tokens' => null,
            'duration_ms' => null,
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'user',
            'content' => fake()->sentence(),
            'prompt_tokens' => null,
            'completion_tokens' => null,
            'duration_ms' => null,
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'assistant',
            'content' => fake()->paragraph(),
            'prompt_tokens' => fake()->numberBetween(50, 500),
            'completion_tokens' => fake()->numberBetween(20, 300),
            'duration_ms' => fake()->numberBetween(200, 3000),
        ]);
    }
}
