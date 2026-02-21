<?php

namespace Database\Seeders;

use App\Models\PageCategory;
use Illuminate\Database\Seeder;

class PageCategorySeeder extends Seeder
{
    public function run(): void
    {
        PageCategory::firstOrCreate(
            ['slug' => 'maklumat-korporat'],
            ['name_ms' => 'Maklumat Korporat', 'name_en' => 'Corporate Information', 'sort_order' => 1, 'is_active' => true],
        );

        PageCategory::firstOrCreate(
            ['slug' => 'dasar-undang-undang'],
            ['name_ms' => 'Dasar & Undang-Undang', 'name_en' => 'Policies & Laws', 'sort_order' => 2, 'is_active' => true],
        );
    }
}
