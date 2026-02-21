<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        Achievement::factory()->create([
            'title_ms' => '5 Juta Pengguna MyDigital ID',
            'title_en' => '5 Million MyDigital ID Users',
            'slug' => '5-juta-pengguna-mydigital-id',
            'description_ms' => 'Platform identiti digital kebangsaan telah mencapai 5 juta pengguna berdaftar.',
            'description_en' => 'The national digital identity platform has reached 5 million registered users.',
            'date' => '2025-12-01',
            'is_featured' => true,
            'status' => 'draft',
        ]);

        Achievement::factory()->published()->create([
            'title_ms' => '100% Perkhidmatan Kerajaan Dalam Talian',
            'title_en' => '100% Government Services Online',
            'slug' => '100-peratus-perkhidmatan-kerajaan-dalam-talian',
            'description_ms' => 'Semua perkhidmatan kerajaan kini boleh diakses secara dalam talian melalui portal MyGov.',
            'description_en' => 'All government services are now accessible online through the MyGov portal.',
            'date' => '2025-09-15',
            'is_featured' => true,
        ]);

        Achievement::factory()->published()->create([
            'title_ms' => 'Malaysia Top 25 Digital Nations',
            'title_en' => 'Malaysia Top 25 Digital Nations',
            'slug' => 'malaysia-top-25-digital-nations',
            'description_ms' => 'Malaysia berjaya memasuki senarai 25 negara paling digital di dunia mengikut IMD World Digital Competitiveness Ranking.',
            'description_en' => 'Malaysia has successfully entered the list of top 25 most digital nations in the world according to IMD World Digital Competitiveness Ranking.',
            'date' => '2025-06-20',
            'is_featured' => false,
        ]);
    }
}
