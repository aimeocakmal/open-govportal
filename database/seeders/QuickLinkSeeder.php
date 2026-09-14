<?php

namespace Database\Seeders;

use App\Models\QuickLink;
use Illuminate\Database\Seeder;

class QuickLinkSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            QuickLink::query()->updateOrCreate(['url' => $row['url']], $row);
        }
    }

    /**
     * Icons are image paths because the quick-links component renders them
     * with an <img> tag. See docs/sample-images.md.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            ['label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'url' => '/ms/siaran', 'icon' => '/images/icons/quick-links/siaran.svg', 'sort_order' => 1, 'is_active' => true],
            ['label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'url' => '/ms/pencapaian', 'icon' => '/images/icons/quick-links/pencapaian.svg', 'sort_order' => 2, 'is_active' => true],
            ['label_ms' => 'Statistik', 'label_en' => 'Statistics', 'url' => '/ms/statistik', 'icon' => '/images/icons/quick-links/statistik.svg', 'sort_order' => 3, 'is_active' => true],
            ['label_ms' => 'Dasar', 'label_en' => 'Policies', 'url' => '/ms/dasar', 'icon' => '/images/icons/quick-links/dasar.svg', 'sort_order' => 4, 'is_active' => true],
            ['label_ms' => 'Direktori', 'label_en' => 'Directory', 'url' => '/ms/direktori', 'icon' => '/images/icons/quick-links/direktori.svg', 'sort_order' => 5, 'is_active' => true],
            ['label_ms' => 'Hubungi Kami', 'label_en' => 'Contact Us', 'url' => '/ms/hubungi-kami', 'icon' => '/images/icons/quick-links/hubungi-kami.svg', 'sort_order' => 6, 'is_active' => true],
        ];
    }
}
