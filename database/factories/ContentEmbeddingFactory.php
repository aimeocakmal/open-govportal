<?php

namespace Database\Factories;

use App\Models\Broadcast;
use App\Models\ContentEmbedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentEmbedding>
 */
class ContentEmbeddingFactory extends Factory
{
    protected $model = ContentEmbedding::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'embeddable_type' => Broadcast::class,
            'embeddable_id' => Broadcast::factory(),
            'chunk_index' => 0,
            'locale' => $this->faker->randomElement(['ms', 'en']),
            'content' => $this->faker->paragraph(),
            'embedding' => array_fill(0, 1536, 0.0),
            'metadata' => [
                'title' => $this->faker->sentence(),
                'slug' => $this->faker->slug(),
                'type' => 'broadcast',
            ],
        ];
    }
}
