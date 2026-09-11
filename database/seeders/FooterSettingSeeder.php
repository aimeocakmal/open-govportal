<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use Illuminate\Database\Seeder;

class FooterSettingSeeder extends Seeder
{
    public const REPOSITORY_URL = 'https://github.com/aimeocakmal/open-govportal';

    public function run(): void
    {
        FooterSetting::query()->delete();

        // Footer link columns (About, Quick Links, Open Source) are managed
        // via the public_footer Menu — see MenuSeeder.
        // FooterSetting manages the branding block (left column) and social icons.

        $branding = [
            ['type' => 'logo', 'label_ms' => 'OpenGovPortal', 'label_en' => 'OpenGovPortal', 'url' => '/images/logo/opengovportal-footer.svg', 'sort_order' => 1],
            ['type' => 'heading', 'label_ms' => 'Portal kerajaan sumber terbuka', 'label_en' => 'Open-source government portal', 'sort_order' => 2],
            ['type' => 'text', 'label_ms' => "Aras 5, Menara Portal\nJalan Contoh 1\n50000 Bandar Contoh", 'label_en' => "Level 5, Menara Portal\nJalan Contoh 1\n50000 Bandar Contoh", 'sort_order' => 3],
            ['type' => 'subheading', 'label_ms' => 'Ikuti kami', 'label_en' => 'Follow us', 'sort_order' => 4],
        ];

        foreach ($branding as $item) {
            FooterSetting::create(array_merge($item, ['section' => 'branding']));
        }

        $social = [
            ['label_ms' => 'GitHub', 'label_en' => 'GitHub', 'url' => self::REPOSITORY_URL, 'icon' => 'github', 'sort_order' => 1],
            ['label_ms' => 'Facebook', 'label_en' => 'Facebook', 'url' => 'https://opengovportal.example/facebook', 'icon' => 'facebook', 'sort_order' => 2],
            ['label_ms' => 'X', 'label_en' => 'X', 'url' => 'https://opengovportal.example/x', 'icon' => 'x-twitter', 'sort_order' => 3],
            ['label_ms' => 'YouTube', 'label_en' => 'YouTube', 'url' => 'https://opengovportal.example/youtube', 'icon' => 'youtube', 'sort_order' => 4],
        ];

        foreach ($social as $item) {
            FooterSetting::create(array_merge($item, ['section' => 'social', 'type' => 'link']));
        }
    }
}
