<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        Policy::factory()->create([
            'title_ms' => 'Dasar Keselamatan Siber Negara (Draf)',
            'title_en' => 'National Cyber Security Policy (Draft)',
            'slug' => 'dasar-keselamatan-siber-negara',
            'description_ms' => 'Rangka kerja keselamatan siber untuk melindungi infrastruktur digital negara.',
            'description_en' => 'Cybersecurity framework to protect the nation\'s digital infrastructure.',
            'category' => 'keselamatan',
            'status' => 'draft',
        ]);

        Policy::factory()->published()->create([
            'title_ms' => 'Dasar Pendigitalan Kerajaan',
            'title_en' => 'Government Digitalisation Policy',
            'slug' => 'dasar-pendigitalan-kerajaan',
            'description_ms' => 'Dasar berkaitan pendigitalan perkhidmatan kerajaan untuk rakyat Malaysia.',
            'description_en' => 'Policy on the digitalisation of government services for Malaysians.',
            'category' => 'digital',
            'file_size' => 2500000,
        ]);

        Policy::factory()->published()->create([
            'title_ms' => 'Garis Panduan Pengurusan Data Terbuka',
            'title_en' => 'Open Data Management Guidelines',
            'slug' => 'garis-panduan-pengurusan-data-terbuka',
            'description_ms' => 'Garis panduan untuk pengurusan dan perkongsian data terbuka kerajaan.',
            'description_en' => 'Guidelines for the management and sharing of government open data.',
            'category' => 'data',
            'file_size' => 1800000,
        ]);
    }
}
