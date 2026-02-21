<?php

namespace Database\Seeders;

use App\Models\MinisterProfile;
use Illuminate\Database\Seeder;

class MinisterProfileSeeder extends Seeder
{
    public function run(): void
    {
        MinisterProfile::factory()->create([
            'name' => 'YB Gobind Singh Deo',
            'title_ms' => 'Menteri Digital',
            'title_en' => 'Minister of Digital',
            'bio_ms' => 'YB Gobind Singh Deo merupakan Menteri Digital Malaysia.',
            'bio_en' => 'YB Gobind Singh Deo is the Minister of Digital Malaysia.',
            'is_current' => true,
            'appointed_at' => '2023-12-12',
        ]);
    }
}
