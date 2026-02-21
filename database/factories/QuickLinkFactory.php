<?php

namespace Database\Factories;

use App\Models\QuickLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuickLink>
 */
class QuickLinkFactory extends Factory
{
    protected $model = QuickLink::class;

    public function definition(): array
    {
        return [
            'label_ms' => fake()->words(2, true),
            'label_en' => fake()->words(2, true),
            'url' => fake()->url(),
            'icon' => fake()->randomElement(['globe', 'document', 'phone', 'mail', 'chart', 'users']),
            'sort_order' => fake()->numberBetween(0, 10),
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
