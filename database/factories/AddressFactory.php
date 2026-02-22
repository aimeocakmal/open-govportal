<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'label_ms' => fake()->company(),
            'label_en' => fake()->company(),
            'address_ms' => fake()->address(),
            'address_en' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'fax' => fake()->optional()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'google_maps_url' => null,
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
}
