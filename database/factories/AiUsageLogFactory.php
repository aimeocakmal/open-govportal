<?php

namespace Database\Factories;

use App\Models\AiUsageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiUsageLog> */
class AiUsageLogFactory extends Factory
{
    protected $model = AiUsageLog::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'operation' => fake()->randomElement([
                'grammar_check', 'translate', 'expand', 'summarise',
                'tldr', 'write_excerpt', 'generate', 'chat', 'embed',
            ]),
            'source' => fake()->randomElement(['admin_editor', 'public_chat', 'admin_embedding']),
            'locale' => fake()->randomElement(['ms', 'en']),
            'duration_ms' => fake()->numberBetween(50, 5000),
            'prompt_tokens' => fake()->numberBetween(10, 2000),
            'completion_tokens' => fake()->numberBetween(5, 1000),
            'provider' => fake()->randomElement(['anthropic', 'openai', 'google']),
            'model' => fake()->randomElement(['claude-sonnet-4-6', 'gpt-4o', 'gemini-2.0-flash']),
        ];
    }

    public function adminEditor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => 'admin_editor',
            'operation' => fake()->randomElement(['grammar_check', 'translate', 'expand', 'summarise', 'tldr', 'write_excerpt', 'generate']),
        ]);
    }

    public function publicChat(): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => 'public_chat',
            'operation' => 'chat',
        ]);
    }

    public function embedding(): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => 'admin_embedding',
            'operation' => 'embed',
        ]);
    }
}
