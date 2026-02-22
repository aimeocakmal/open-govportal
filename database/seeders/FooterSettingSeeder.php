<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use Illuminate\Database\Seeder;

class FooterSettingSeeder extends Seeder
{
    public function run(): void
    {
        FooterSetting::query()->delete();

        // Footer link columns (About Us, Quick Links, Open Source) are managed
        // via the public_footer Menu — see MenuSeeder.
        // FooterSetting manages the branding block (left column) and social icons.

        // ── Branding block ─────────────────────────────────────────────
        $branding = [
            ['type' => 'logo', 'label_ms' => 'Jata Negara', 'label_en' => 'National Coat of Arms', 'url' => '/images/jata-negara.png', 'sort_order' => 1],
            ['type' => 'heading', 'label_ms' => 'Kementerian Digital', 'label_en' => 'Ministry of Digital', 'sort_order' => 2],
            ['type' => 'text', 'label_ms' => "Aras 7, Menara PjH, No. 2,\nJalan Tun Abdul Razak, Presint 2,\n62100 Putrajaya, Malaysia", 'label_en' => "Level 7, Menara PjH, No. 2,\nJalan Tun Abdul Razak, Presint 2,\n62100 Putrajaya, Malaysia", 'sort_order' => 3],
            ['type' => 'subheading', 'label_ms' => 'Ikuti Kami', 'label_en' => 'Follow Us', 'sort_order' => 4],
        ];

        foreach ($branding as $item) {
            FooterSetting::create(array_merge($item, ['section' => 'branding']));
        }

        // ── Social links with icons ────────────────────────────────────
        $social = [
            ['label_ms' => 'Facebook', 'label_en' => 'Facebook', 'url' => 'https://facebook.com/KementerianDigitalMalaysia', 'icon' => 'facebook', 'sort_order' => 1],
            ['label_ms' => 'Instagram', 'label_en' => 'Instagram', 'url' => 'https://instagram.com/kemabordigital', 'icon' => 'instagram', 'sort_order' => 2],
            ['label_ms' => 'X', 'label_en' => 'X', 'url' => 'https://x.com/KKDmalaysia', 'icon' => 'x-twitter', 'sort_order' => 3],
            ['label_ms' => 'TikTok', 'label_en' => 'TikTok', 'url' => 'https://tiktok.com/@kementeriandigital', 'icon' => 'tiktok', 'sort_order' => 4],
        ];

        foreach ($social as $item) {
            FooterSetting::create(array_merge($item, ['section' => 'social', 'type' => 'link']));
        }
    }
}
