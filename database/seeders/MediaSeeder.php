<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            $path = public_path(ltrim($row['file_url'], '/'));

            Media::query()->updateOrCreate(
                ['filename' => $row['filename']],
                array_merge($row, [
                    'original_name' => $row['filename'],
                    'mime_type' => 'image/svg+xml',
                    'file_size' => is_file($path) ? filesize($path) : null,
                    'uploaded_by' => $adminId,
                ]),
            );
        }
    }

    /**
     * Registry entries for the committed asset pack. See docs/sample-images.md.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'filename' => 'hero-01.svg',
                'file_url' => '/images/hero/hero-01.svg',
                'width' => 2400,
                'height' => 1120,
                'alt_ms' => 'Tiga gerbang sepusat berwarna putih di atas latar kecerunan biru',
                'alt_en' => 'Three concentric white arches over a blue gradient background',
                'caption_ms' => 'Pemegang tempat sepanduk utama sehingga foto sebenar dimuat naik.',
                'caption_en' => 'Hero banner placeholder until a real photograph is uploaded.',
            ],
            [
                'filename' => 'broadcast-01.svg',
                'file_url' => '/images/broadcasts/broadcast-01.svg',
                'width' => 1600,
                'height' => 900,
                'alt_ms' => 'Gerbang biru di atas latar biru muda',
                'alt_en' => 'Blue arch over a light blue background',
                'caption_ms' => 'Pemegang tempat imag pilihan siaran, nisbah 16:9.',
                'caption_en' => 'Broadcast featured image placeholder, 16:9 ratio.',
            ],
            [
                'filename' => 'opengovportal-footer.svg',
                'file_url' => '/images/logo/opengovportal-footer.svg',
                'width' => 200,
                'height' => 40,
                'alt_ms' => 'Logo OpenGovPortal',
                'alt_en' => 'OpenGovPortal logo',
                'caption_ms' => 'Logo kaki laman: tanda gerbang bersama tanda kata.',
                'caption_en' => 'Footer logo: arch mark with wordmark.',
            ],
        ];
    }
}
