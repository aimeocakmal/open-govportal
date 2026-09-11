<?php

namespace Database\Factories;

use App\Models\MinisterProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MinisterProfile>
 */
class MinisterProfileFactory extends Factory
{
    protected $model = MinisterProfile::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'title_ms' => 'Ketua Pegawai Digital',
            'title_en' => 'Chief Digital Officer',
            'bio_ms' => fake()->paragraphs(2, true),
            'bio_en' => fake()->paragraphs(2, true),
            'photo' => null,
            'is_current' => true,
            'appointed_at' => fake()->date(),
        ];
    }

    public function former(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => false,
        ]);
    }
}
