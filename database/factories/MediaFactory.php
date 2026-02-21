<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'webp']);

        return [
            'filename' => fake()->uuid().'.'.$extension,
            'original_name' => fake()->word().'.'.$extension,
            'file_url' => 'media/'.fake()->uuid().'.'.$extension,
            'mime_type' => 'image/'.($extension === 'jpg' ? 'jpeg' : $extension),
            'file_size' => fake()->numberBetween(50000, 3000000),
            'width' => fake()->randomElement([800, 1024, 1280, 1920]),
            'height' => fake()->randomElement([600, 768, 720, 1080]),
            'alt_ms' => fake()->sentence(3),
            'alt_en' => fake()->sentence(3),
            'caption_ms' => fake()->optional()->sentence(),
            'caption_en' => fake()->optional()->sentence(),
            'uploaded_by' => null,
        ];
    }
}
