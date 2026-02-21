<?php

namespace Database\Factories;

use App\Models\Celebration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Celebration>
 */
class CelebrationFactory extends Factory
{
    protected $model = Celebration::class;

    public function definition(): array
    {
        $titleMs = fake()->sentence(4);

        return [
            'title_ms' => $titleMs,
            'title_en' => fake()->sentence(4),
            'slug' => Str::slug($titleMs).'-'.fake()->unique()->randomNumber(5),
            'description_ms' => fake()->paragraphs(2, true),
            'description_en' => fake()->paragraphs(2, true),
            'event_date' => fake()->dateTimeBetween('-1 year', '+6 months'),
            'image' => null,
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
}
