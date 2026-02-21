<?php

namespace Database\Seeders;

use App\Models\QuickLink;
use Illuminate\Database\Seeder;

class QuickLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'label_ms' => 'Siaran',
                'label_en' => 'Broadcasts',
                'url' => '/siaran',
                'icon' => 'megaphone',
                'sort_order' => 1,
            ],
            [
                'label_ms' => 'Dasar',
                'label_en' => 'Policies',
                'url' => '/dasar',
                'icon' => 'document',
                'sort_order' => 2,
            ],
            [
                'label_ms' => 'Direktori',
                'label_en' => 'Directory',
                'url' => '/direktori',
                'icon' => 'users',
                'sort_order' => 3,
            ],
            [
                'label_ms' => 'Hubungi Kami',
                'label_en' => 'Contact Us',
                'url' => '/hubungi-kami',
                'icon' => 'phone',
                'sort_order' => 4,
            ],
        ];

        foreach ($links as $link) {
            QuickLink::factory()->create($link);
        }
    }
}
