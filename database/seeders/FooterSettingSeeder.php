<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use Illuminate\Database\Seeder;

class FooterSettingSeeder extends Seeder
{
    public function run(): void
    {
        FooterSetting::query()->delete();

        // Footer columns (About Us, Quick Links, Open Source) are managed
        // via the public_footer Menu — see MenuSeeder.
        // FooterSetting is used only for social links in the branding column.

        $social = [
            ['label_ms' => 'Facebook', 'label_en' => 'Facebook', 'url' => 'https://facebook.com/KementerianDigitalMalaysia'],
            ['label_ms' => 'X', 'label_en' => 'X', 'url' => 'https://x.com/KKDmalaysia'],
            ['label_ms' => 'Instagram', 'label_en' => 'Instagram', 'url' => 'https://instagram.com/kemabordigital'],
            ['label_ms' => 'TikTok', 'label_en' => 'TikTok', 'url' => 'https://tiktok.com/@kementeriandigital'],
        ];

        foreach ($social as $i => $item) {
            FooterSetting::create(array_merge($item, ['section' => 'social', 'sort_order' => $i + 1]));
        }
    }
}
