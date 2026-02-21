<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        HeroBanner::factory()->create([
            'title_ms' => 'Selamat Datang ke Kementerian Digital',
            'title_en' => 'Welcome to the Ministry of Digital',
            'subtitle_ms' => 'Memacu transformasi digital negara untuk masa hadapan yang lebih cerah.',
            'subtitle_en' => 'Driving national digital transformation for a brighter future.',
            'image' => 'banners/hero-welcome.jpg',
            'image_alt_ms' => 'Banner Kementerian Digital',
            'image_alt_en' => 'Ministry of Digital Banner',
            'cta_label_ms' => 'Ketahui Lebih Lanjut',
            'cta_label_en' => 'Learn More',
            'cta_url' => '/profil-kementerian',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HeroBanner::factory()->create([
            'title_ms' => 'Ekonomi Digital Malaysia 2030',
            'title_en' => 'Malaysia Digital Economy 2030',
            'subtitle_ms' => 'Pelan hala tuju strategik untuk ekonomi digital negara.',
            'subtitle_en' => 'Strategic roadmap for the national digital economy.',
            'image' => 'banners/hero-economy.jpg',
            'image_alt_ms' => 'Ekonomi Digital 2030',
            'image_alt_en' => 'Digital Economy 2030',
            'cta_label_ms' => 'Baca Lagi',
            'cta_label_en' => 'Read More',
            'cta_url' => '/siaran/pelan-tindakan-ekonomi-digital-malaysia-2030',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HeroBanner::factory()->create([
            'title_ms' => 'MyDigital ID',
            'title_en' => 'MyDigital ID',
            'subtitle_ms' => 'Identiti digital kebangsaan anda.',
            'subtitle_en' => 'Your national digital identity.',
            'image' => 'banners/hero-mydigital.jpg',
            'image_alt_ms' => 'MyDigital ID',
            'image_alt_en' => 'MyDigital ID',
            'cta_label_ms' => 'Daftar Sekarang',
            'cta_label_en' => 'Register Now',
            'cta_url' => 'https://mydigitalid.gov.my',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
