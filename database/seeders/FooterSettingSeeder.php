<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use Illuminate\Database\Seeder;

class FooterSettingSeeder extends Seeder
{
    public function run(): void
    {
        FooterSetting::create([
            'section' => 'links',
            'label_ms' => 'Laman Utama',
            'label_en' => 'Home',
            'url' => '/',
            'sort_order' => 1,
        ]);

        FooterSetting::create([
            'section' => 'links',
            'label_ms' => 'Siaran',
            'label_en' => 'Broadcasts',
            'url' => '/siaran',
            'sort_order' => 2,
        ]);

        FooterSetting::create([
            'section' => 'social',
            'label_ms' => 'Facebook',
            'label_en' => 'Facebook',
            'url' => 'https://facebook.com/KementerianDigitalMalaysia',
            'sort_order' => 1,
        ]);

        FooterSetting::create([
            'section' => 'legal',
            'label_ms' => 'Penafian',
            'label_en' => 'Disclaimer',
            'url' => '/penafian',
            'sort_order' => 1,
        ]);

        FooterSetting::create([
            'section' => 'legal',
            'label_ms' => 'Dasar Privasi',
            'label_en' => 'Privacy Policy',
            'url' => '/dasar-privasi',
            'sort_order' => 2,
        ]);
    }
}
