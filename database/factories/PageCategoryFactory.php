<?php

namespace Database\Factories;

use App\Models\PageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageCategory>
 */
class PageCategoryFactory extends Factory
{
    protected $model = PageCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name_ms' => $name,
            'name_en' => $name,
            'slug' => Str::slug($name),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(PageCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
