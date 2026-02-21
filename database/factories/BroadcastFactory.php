<?php

namespace Database\Factories;

use App\Models\Broadcast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Broadcast>
 */
class BroadcastFactory extends Factory
{
    protected $model = Broadcast::class;

    public function definition(): array
    {
        $titleMs = fake()->sentence(6);

        return [
            'title_ms' => $titleMs,
            'title_en' => fake()->sentence(6),
            'slug' => Str::slug($titleMs).'-'.fake()->unique()->randomNumber(5),
            'content_ms' => fake()->paragraphs(3, true),
            'content_en' => fake()->paragraphs(3, true),
            'excerpt_ms' => fake()->text(200),
            'excerpt_en' => fake()->text(200),
            'featured_image' => null,
            'type' => fake()->randomElement(['announcement', 'press_release', 'news']),
            'status' => 'draft',
            'published_at' => null,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function announcement(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'announcement',
        ]);
    }

    public function pressRelease(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'press_release',
        ]);
    }

    public function news(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'news',
        ]);
    }
}
