<?php

namespace Database\Seeders;

use App\Models\Celebration;
use Illuminate\Database\Seeder;

class CelebrationSeeder extends Seeder
{
    public function run(): void
    {
        Celebration::factory()->create([
            'title_ms' => 'Hari Malaysia Digital 2026',
            'title_en' => 'Malaysia Digital Day 2026',
            'slug' => 'hari-malaysia-digital-2026',
            'description_ms' => 'Sambutan tahunan kemajuan digital negara.',
            'description_en' => 'Annual celebration of the nation\'s digital progress.',
            'event_date' => '2026-10-01',
            'status' => 'draft',
        ]);

        Celebration::factory()->published()->create([
            'title_ms' => 'Sambutan Hari Kebangsaan 2025',
            'title_en' => 'National Day Celebration 2025',
            'slug' => 'sambutan-hari-kebangsaan-2025',
            'description_ms' => 'Kementerian Digital turut meraikan Hari Kebangsaan dengan pelbagai program digital.',
            'description_en' => 'The Ministry of Digital celebrates National Day with various digital programmes.',
            'event_date' => '2025-08-31',
        ]);

        Celebration::factory()->published()->create([
            'title_ms' => 'Minggu ICT Kerajaan 2025',
            'title_en' => 'Government ICT Week 2025',
            'slug' => 'minggu-ict-kerajaan-2025',
            'description_ms' => 'Pameran dan seminar ICT kerajaan tahunan.',
            'description_en' => 'Annual government ICT exhibition and seminar.',
            'event_date' => '2025-07-14',
        ]);
    }
}
