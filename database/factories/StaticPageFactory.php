<?php

namespace Database\Factories;

use App\Models\StaticPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaticPage>
 */
class StaticPageFactory extends Factory
{
    protected $model = StaticPage::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title_ms' => $title,
            'title_en' => $title,
            'slug' => Str::slug($title),
            'content_ms' => fake()->paragraphs(3, true),
            'content_en' => fake()->paragraphs(3, true),
            'excerpt_ms' => fake()->sentence(),
            'excerpt_en' => fake()->sentence(),
            'status' => 'draft',
            'is_in_sitemap' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
