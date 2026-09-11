<?php

namespace Database\Factories;

use App\Models\StaffDirectory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaffDirectory>
 */
class StaffDirectoryFactory extends Factory
{
    protected $model = StaffDirectory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position_ms' => fake()->jobTitle(),
            'position_en' => fake()->jobTitle(),
            'department_ms' => fake()->randomElement(['Teknologi & Platform', 'Kandungan & Editorial', 'Sokongan & Latihan']),
            'department_en' => fake()->randomElement(['Technology & Platform', 'Content & Editorial', 'Support & Training']),
            'division_ms' => fake()->randomElement(['Unit Pembangunan', 'Unit Pentadbiran']),
            'division_en' => fake()->randomElement(['Development Unit', 'Administration Unit']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'fax' => fake()->optional()->phoneNumber(),
            'photo' => null,
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
