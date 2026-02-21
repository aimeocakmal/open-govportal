<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label_ms' => fake()->words(2, true),
            'label_en' => fake()->words(2, true),
            'url' => fake()->url(),
            'sort_order' => fake()->numberBetween(0, 100),
            'target' => '_self',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forMenu(Menu $menu): static
    {
        return $this->state(fn (array $attributes) => [
            'menu_id' => $menu->id,
        ]);
    }

    public function withParent(MenuItem $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'menu_id' => $parent->menu_id,
            'parent_id' => $parent->id,
        ]);
    }
}
