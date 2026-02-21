<?php

namespace Database\Seeders;

use App\Models\SearchOverride;
use Illuminate\Database\Seeder;

class SearchOverrideSeeder extends Seeder
{
    public function run(): void
    {
        SearchOverride::factory()->create([
            'query' => 'MyDigital',
            'title_ms' => 'MyDigital ID — Identiti Digital Kebangsaan',
            'title_en' => 'MyDigital ID — National Digital Identity',
            'url' => 'https://mydigitalid.gov.my',
            'description_ms' => 'Platform identiti digital kebangsaan untuk rakyat Malaysia.',
            'description_en' => 'National digital identity platform for Malaysians.',
            'priority' => 100,
            'is_active' => true,
        ]);

        SearchOverride::factory()->create([
            'query' => 'dasar digital',
            'title_ms' => 'Dasar Digital Negara',
            'title_en' => 'National Digital Policy',
            'url' => '/ms/dasar',
            'description_ms' => 'Senarai dasar dan polisi digital negara.',
            'description_en' => 'List of national digital policies.',
            'priority' => 80,
            'is_active' => true,
        ]);

        SearchOverride::factory()->create([
            'query' => 'hubungi',
            'title_ms' => 'Hubungi Kami',
            'title_en' => 'Contact Us',
            'url' => '/ms/hubungi-kami',
            'description_ms' => 'Maklumat perhubungan Kementerian Digital.',
            'description_en' => 'Ministry of Digital contact information.',
            'priority' => 60,
            'is_active' => true,
        ]);
    }
}
