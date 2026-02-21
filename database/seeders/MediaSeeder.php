<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        Media::factory()->create([
            'filename' => 'logo-kementerian-digital.png',
            'original_name' => 'logo-kementerian-digital.png',
            'file_url' => 'media/logo-kementerian-digital.png',
            'mime_type' => 'image/png',
            'file_size' => 102400,
            'width' => 400,
            'height' => 200,
            'alt_ms' => 'Logo Kementerian Digital Malaysia',
            'alt_en' => 'Ministry of Digital Malaysia Logo',
        ]);

        Media::factory()->create([
            'filename' => 'banner-transformasi-digital.jpg',
            'original_name' => 'banner-transformasi-digital.jpg',
            'file_url' => 'media/banner-transformasi-digital.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1048576,
            'width' => 1920,
            'height' => 1080,
            'alt_ms' => 'Banner Transformasi Digital',
            'alt_en' => 'Digital Transformation Banner',
        ]);

        Media::factory()->create([
            'filename' => 'infografik-ekonomi-digital.webp',
            'original_name' => 'infografik-ekonomi-digital.webp',
            'file_url' => 'media/infografik-ekonomi-digital.webp',
            'mime_type' => 'image/webp',
            'file_size' => 524288,
            'width' => 1280,
            'height' => 720,
            'alt_ms' => 'Infografik Ekonomi Digital',
            'alt_en' => 'Digital Economy Infographic',
            'caption_ms' => 'Statistik ekonomi digital Malaysia 2024.',
            'caption_en' => 'Malaysia digital economy statistics 2024.',
        ]);
    }
}
