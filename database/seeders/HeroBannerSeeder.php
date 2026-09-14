<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            HeroBanner::query()->updateOrCreate(['sort_order' => $row['sort_order']], $row);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'sort_order' => 1,
                'title_ms' => 'Selamat datang ke OpenGovPortal',
                'title_en' => 'Welcome to OpenGovPortal',
                'subtitle_ms' => 'Portal kerajaan sumber terbuka yang dwibahasa, mudah diakses dan pantas. Semua yang anda perlukan untuk menerbitkan maklumat awam dengan yakin.',
                'subtitle_en' => 'An open-source government portal that is bilingual, accessible and fast. Everything you need to publish public information with confidence.',
                'image' => '/images/hero/hero-01.svg',
                'image_alt_ms' => 'Tiga gerbang sepusat berwarna putih di atas latar kecerunan biru',
                'image_alt_en' => 'Three concentric white arches over a blue gradient background',
                'cta_label_ms' => 'Lihat profil kami',
                'cta_label_en' => 'See our profile',
                'cta_url' => '/ms/profil-kementerian',
                'is_active' => true,
            ],
            [
                'sort_order' => 2,
                'title_ms' => 'Dasar dan garis panduan di satu tempat',
                'title_en' => 'Policies and guidelines in one place',
                'subtitle_ms' => 'Cari, tapis dan muat turun dokumen rasmi mengikut kategori. Setiap dokumen disertakan ringkasan dalam dua bahasa.',
                'subtitle_en' => 'Search, filter and download official documents by category. Every document comes with a summary in both languages.',
                'image' => '/images/hero/hero-02.svg',
                'image_alt_ms' => 'Satu gerbang besar yang naik dari tepi bawah latar biru',
                'image_alt_en' => 'A single large arch rising from the bottom edge of a blue background',
                'cta_label_ms' => 'Layari dasar',
                'cta_label_en' => 'Browse policies',
                'cta_url' => '/ms/dasar',
                'is_active' => true,
            ],
            [
                'sort_order' => 3,
                'title_ms' => 'Pembantu AI kini dalam fasa beta',
                'title_en' => 'AI assistant now in beta',
                'subtitle_ms' => 'Tanya soalan dalam Bahasa Malaysia atau Bahasa Inggeris dan dapatkan jawapan berdasarkan kandungan portal ini sahaja.',
                'subtitle_en' => 'Ask a question in Bahasa Malaysia or English and get answers grounded in the content of this portal only.',
                'image' => '/images/hero/hero-03.svg',
                'image_alt_ms' => 'Grid gerbang kecil berwarna putih di atas latar biru',
                'image_alt_en' => 'A grid of small white arches over a blue background',
                'cta_label_ms' => 'Baca pengumuman',
                'cta_label_en' => 'Read the announcement',
                'cta_url' => '/ms/siaran/pembantu-ai-kini-dalam-fasa-beta',
                'is_active' => true,
            ],
            [
                'sort_order' => 4,
                'title_ms' => 'Sesi latihan untuk editor kandungan',
                'title_en' => 'Training sessions for content editors',
                'subtitle_ms' => 'Daftar untuk sesi dalam talian selama 90 minit yang merangkumi editor teks kaya, terjemahan dan penjadualan penerbitan.',
                'subtitle_en' => 'Register for a 90-minute online session covering the rich text editor, translation and scheduled publishing.',
                'image' => '/images/hero/hero-04.svg',
                'image_alt_ms' => 'Dua gerbang bertindan pada skala berbeza di atas latar biru',
                'image_alt_en' => 'Two overlapping arches at different scales over a blue background',
                'cta_label_ms' => 'Daftar sekarang',
                'cta_label_en' => 'Register now',
                'cta_url' => '/ms/siaran/sesi-latihan-untuk-editor-kandungan',
                'is_active' => false,
            ],
        ];
    }
}
