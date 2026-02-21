<?php

namespace Database\Factories;

use App\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Policy>
 */
class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    public function definition(): array
    {
        $titleMs = fake()->sentence(5);

        return [
            'title_ms' => $titleMs,
            'title_en' => fake()->sentence(5),
            'slug' => Str::slug($titleMs).'-'.fake()->unique()->randomNumber(5),
            'description_ms' => fake()->paragraphs(2, true),
            'description_en' => fake()->paragraphs(2, true),
            'category' => fake()->randomElement(['keselamatan', 'data', 'digital', 'ict', 'perkhidmatan']),
            'file_url' => null,
            'file_size' => fake()->numberBetween(100000, 5000000),
            'download_count' => 0,
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
