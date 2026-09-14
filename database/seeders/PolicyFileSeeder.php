<?php

namespace Database\Seeders;

use App\Models\PolicyFile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PolicyFileSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            $path = public_path(ltrim($row['file_url'], '/'));

            PolicyFile::query()->updateOrCreate(
                ['filename' => $row['filename']],
                array_merge($row, [
                    'mime_type' => 'application/pdf',
                    'file_size' => is_file($path) ? filesize($path) : ($row['file_size'] ?? null),
                    'created_by' => $adminId,
                ]),
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'filename' => 'garis-panduan-kandungan-dwibahasa.pdf',
                'file_url' => '/files/policies/garis-panduan-kandungan-dwibahasa.pdf',
                'title_ms' => 'Garis Panduan Kandungan Dwibahasa',
                'title_en' => 'Bilingual Content Guidelines',
                'description_ms' => 'Salinan PDF garis panduan penulisan untuk editor kandungan.',
                'description_en' => 'PDF copy of the writing guidelines for content editors.',
                'category' => 'garis_panduan',
                'is_public' => true,
                'download_count' => 478,
            ],
            [
                'filename' => 'piagam-perkhidmatan-portal.pdf',
                'file_url' => '/files/policies/piagam-perkhidmatan-portal.pdf',
                'title_ms' => 'Piagam Perkhidmatan Portal',
                'title_en' => 'Portal Service Charter',
                'description_ms' => 'Sasaran perkhidmatan yang dijanjikan kepada pelawat portal.',
                'description_en' => 'Service targets promised to portal visitors.',
                'category' => 'piagam',
                'is_public' => true,
                'download_count' => 302,
            ],
            [
                'filename' => 'laporan-dalaman-s2-2026.pdf',
                'file_url' => '/files/private/laporan-dalaman-s2-2026.pdf',
                'title_ms' => 'Laporan Dalaman Suku Kedua 2026',
                'title_en' => 'Internal Report Q2 2026',
                'description_ms' => 'Laporan prestasi dalaman untuk pasukan pentadbiran sahaja.',
                'description_en' => 'Internal performance report for the administration team only.',
                'category' => 'laporan',
                'is_public' => false,
                'download_count' => 12,
                'file_size' => 524288,
            ],
        ];
    }
}
