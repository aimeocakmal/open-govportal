<?php

namespace Database\Factories;

use App\Models\PolicyFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PolicyFile>
 */
class PolicyFileFactory extends Factory
{
    protected $model = PolicyFile::class;

    public function definition(): array
    {
        return [
            'title_ms' => fake()->sentence(4),
            'title_en' => fake()->sentence(4),
            'description_ms' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'filename' => fake()->word().'.pdf',
            'file_url' => 'files/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(100000, 5000000),
            'category' => fake()->randomElement(['pekeliling', 'garis_panduan', 'laporan', 'borang']),
            'download_count' => 0,
            'is_public' => true,
            'created_by' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
