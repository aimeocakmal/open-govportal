<?php

namespace Database\Factories;

use App\Models\HeroBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HeroBanner>
 */
class HeroBannerFactory extends Factory
{
    protected $model = HeroBanner::class;

    public function definition(): array
    {
        return [
            'title_ms' => fake()->sentence(5),
            'title_en' => fake()->sentence(5),
            'subtitle_ms' => fake()->sentence(10),
            'subtitle_en' => fake()->sentence(10),
            'image' => 'banners/placeholder-'.fake()->randomNumber(3).'.jpg',
            'image_alt_ms' => fake()->sentence(3),
            'image_alt_en' => fake()->sentence(3),
            'cta_label_ms' => 'Ketahui Lebih Lanjut',
            'cta_label_en' => 'Learn More',
            'cta_url' => fake()->url(),
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
