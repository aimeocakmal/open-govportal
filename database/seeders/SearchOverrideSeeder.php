<?php

namespace Database\Seeders;

use App\Models\SearchOverride;
use Illuminate\Database\Seeder;

class SearchOverrideSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            SearchOverride::query()->updateOrCreate(['query' => $row['query']], $row);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'query' => 'dasar',
                'title_ms' => 'Dasar dan garis panduan',
                'title_en' => 'Policies and guidelines',
                'url' => '/ms/dasar',
                'description_ms' => 'Senarai penuh dasar dan garis panduan portal mengikut kategori.',
                'description_en' => 'The full list of portal policies and guidelines by category.',
                'priority' => 100,
                'is_active' => true,
            ],
            [
                'query' => 'hubungi',
                'title_ms' => 'Hubungi Kami',
                'title_en' => 'Contact Us',
                'url' => '/ms/hubungi-kami',
                'description_ms' => 'Alamat, nombor telefon dan borang maklum balas OpenGovPortal.',
                'description_en' => 'OpenGovPortal addresses, phone numbers and feedback form.',
                'priority' => 80,
                'is_active' => true,
            ],
            [
                'query' => 'pembantu ai',
                'title_ms' => 'Pembantu AI (beta)',
                'title_en' => 'AI assistant (beta)',
                'url' => '/ms/siaran/pembantu-ai-kini-dalam-fasa-beta',
                'description_ms' => 'Cara pembantu AI menjawab soalan berdasarkan kandungan portal.',
                'description_en' => 'How the AI assistant answers questions from portal content.',
                'priority' => 60,
                'is_active' => true,
            ],
        ];
    }
}
