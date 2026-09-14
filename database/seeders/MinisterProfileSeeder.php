<?php

namespace Database\Seeders;

use App\Models\MinisterProfile;
use Illuminate\Database\Seeder;

class MinisterProfileSeeder extends Seeder
{
    public function run(): void
    {
        MinisterProfile::query()->updateOrCreate(
            ['name' => 'Nurul Hidayah binti Azman'],
            [
                'title_ms' => 'Ketua Pegawai Digital, OpenGovPortal',
                'title_en' => 'Chief Digital Officer, OpenGovPortal',
                'bio_ms' => '<p>Nurul Hidayah mengetuai pasukan OpenGovPortal sejak Januari 2025 dan bertanggungjawab ke atas hala tuju produk, piawaian kebolehcapaian dan hubungan dengan komuniti penyumbang.</p><p>Sebelum ini, beliau menerajui beberapa projek pendigitalan perkhidmatan awam dan memberi tumpuan kepada penerbitan kandungan dwibahasa yang jelas dan mudah difahami.</p>',
                'bio_en' => '<p>Nurul Hidayah has led the OpenGovPortal team since January 2025 and is responsible for product direction, accessibility standards and the relationship with the contributor community.</p><p>Before this, they led several public-service digitisation projects with a focus on clear, plain-language bilingual publishing.</p>',
                'photo' => null,
                'is_current' => true,
                'appointed_at' => '2025-01-06',
            ],
        );
    }
}
