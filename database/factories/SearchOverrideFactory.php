<?php

namespace Database\Factories;

use App\Models\SearchOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SearchOverride>
 */
class SearchOverrideFactory extends Factory
{
    protected $model = SearchOverride::class;

    public function definition(): array
    {
        return [
            'query' => fake()->words(2, true),
            'title_ms' => fake()->sentence(4),
            'title_en' => fake()->sentence(4),
            'url' => '/'.fake()->randomElement(['ms', 'en']).'/'.fake()->slug(2),
            'description_ms' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'priority' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
