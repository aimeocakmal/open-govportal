<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $header = Menu::firstOrCreate(
            ['name' => 'public_header'],
            ['label_ms' => 'Menu Utama', 'label_en' => 'Main Menu', 'is_active' => true],
        );

        Menu::firstOrCreate(
            ['name' => 'public_footer'],
            ['label_ms' => 'Menu Footer', 'label_en' => 'Footer Menu', 'is_active' => true],
        );

        Menu::firstOrCreate(
            ['name' => 'admin_sidebar'],
            ['label_ms' => 'Menu Admin', 'label_en' => 'Admin Menu', 'is_active' => true],
        );

        // Sample header items
        $items = [
            ['label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'url' => '/ms/siaran', 'sort_order' => 1],
            ['label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'url' => '/ms/pencapaian', 'sort_order' => 2],
            ['label_ms' => 'Statistik', 'label_en' => 'Statistics', 'url' => '/ms/statistik', 'sort_order' => 3],
            ['label_ms' => 'Direktori', 'label_en' => 'Directory', 'url' => '/ms/direktori', 'sort_order' => 4],
            ['label_ms' => 'Dasar', 'label_en' => 'Policy', 'url' => '/ms/dasar', 'sort_order' => 5],
            ['label_ms' => 'Profil Kementerian', 'label_en' => 'Ministry Profile', 'url' => '/ms/profil-kementerian', 'sort_order' => 6],
            ['label_ms' => 'Hubungi Kami', 'label_en' => 'Contact Us', 'url' => '/ms/hubungi-kami', 'sort_order' => 7],
        ];

        foreach ($items as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $header->id, 'label_ms' => $item['label_ms']],
                array_merge($item, ['menu_id' => $header->id, 'is_active' => true]),
            );
        }
    }
}
