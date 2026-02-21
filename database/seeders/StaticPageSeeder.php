<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        StaticPage::firstOrCreate(
            ['slug' => 'penafian'],
            [
                'title_ms' => 'Penafian',
                'title_en' => 'Disclaimer',
                'content_ms' => '<p>Maklumat yang terkandung dalam laman web ini disediakan untuk tujuan maklumat umum sahaja.</p>',
                'content_en' => '<p>The information contained in this website is provided for general informational purposes only.</p>',
                'status' => 'published',
                'is_in_sitemap' => true,
                'sort_order' => 1,
            ],
        );

        StaticPage::firstOrCreate(
            ['slug' => 'dasar-privasi'],
            [
                'title_ms' => 'Dasar Privasi',
                'title_en' => 'Privacy Policy',
                'content_ms' => '<p>Kementerian Digital Malaysia komited untuk melindungi privasi pengguna laman web ini.</p>',
                'content_en' => '<p>Ministry of Digital Malaysia is committed to protecting the privacy of visitors to this website.</p>',
                'status' => 'published',
                'is_in_sitemap' => true,
                'sort_order' => 2,
            ],
        );
    }
}
