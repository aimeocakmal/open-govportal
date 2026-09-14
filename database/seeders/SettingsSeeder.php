<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $key => [$value, $type]) {
            Setting::set($key, $value, $type);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    private function rows(): array
    {
        return [
            // Site identity
            'site_name_ms' => ['OpenGovPortal', 'string'],
            'site_name_en' => ['OpenGovPortal', 'string'],
            'site_description_ms' => ['Portal kerajaan sumber terbuka yang dwibahasa, mudah diakses dan pantas.', 'string'],
            'site_description_en' => ['An open-source government portal that is bilingual, accessible and fast.', 'string'],
            'site_default_theme' => ['default', 'string'],
            'google_analytics_id' => ['', 'string'],
            'facebook_url' => ['', 'string'],
            'twitter_url' => ['', 'string'],
            'instagram_url' => ['', 'string'],
            'youtube_url' => ['', 'string'],

            // Homepage layout (ManageHomepage)
            'homepage_show_hero_banner' => ['1', 'boolean'],
            'homepage_show_quick_links' => ['1', 'boolean'],
            'homepage_show_broadcasts' => ['1', 'boolean'],
            'homepage_show_achievements' => ['1', 'boolean'],
            'homepage_show_feedback' => ['1', 'boolean'],
            'homepage_broadcasts_count' => ['6', 'integer'],
            'homepage_achievements_count' => ['7', 'integer'],
            'homepage_section_order' => ['["hero_banner","quick_links","broadcasts","achievements"]', 'json'],

            // Organisation profile (Profil page)
            'vision_ms' => ['<p>Setiap agensi awam mampu menerbitkan maklumat yang jelas, dwibahasa dan mudah diakses tanpa bergantung kepada sistem tertutup.</p>', 'string'],
            'vision_en' => ['<p>Every public agency can publish clear, bilingual and accessible information without depending on closed systems.</p>', 'string'],
            'mission_ms' => ['<ul><li>Menyediakan portal sumber terbuka yang boleh dipasang dan diselenggara oleh pasukan kecil.</li><li>Melayan Bahasa Malaysia dan Bahasa Inggeris sebagai bahasa kelas pertama dalam setiap ciri.</li><li>Mematuhi WCAG 2.1 AA pada setiap halaman awam.</li><li>Mendokumentasikan setiap keputusan supaya komuniti dapat menyumbang.</li></ul>', 'string'],
            'mission_en' => ['<ul><li>Provide an open-source portal that a small team can install and maintain.</li><li>Treat Bahasa Malaysia and English as first-class languages in every feature.</li><li>Meet WCAG 2.1 AA on every public page.</li><li>Document every decision so the community can contribute.</li></ul>', 'string'],
            'about_ms' => ['<p>OpenGovPortal ialah portal kerajaan sumber terbuka yang dibina dengan Laravel, Livewire dan Tailwind CSS. Ia menggabungkan halaman awam untuk siaran, pencapaian, statistik, direktori dan dasar dengan panel pentadbiran yang lengkap untuk pasukan kandungan.</p><p>Projek ini diselenggara oleh pasukan kecil bersama komuniti penyumbang. Kod sumber, dokumentasi dan pelan hala tuju tersedia secara terbuka supaya mana-mana agensi boleh menggunakannya semula.</p>', 'string'],
            'about_en' => ['<p>OpenGovPortal is an open-source government portal built with Laravel, Livewire and Tailwind CSS. It pairs public pages for broadcasts, achievements, statistics, the staff directory and policies with a complete admin panel for content teams.</p><p>The project is maintained by a small team together with a community of contributors. The source code, documentation and roadmap are all open so that any agency can reuse them.</p>', 'string'],

            // Statistics page (ManageStatistik)
            'statistik_charts' => [json_encode($this->charts(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'json'],

            // AI settings (ManageAiSettings) — copy keys stay empty so lang defaults apply
            'ai_llm_provider' => ['anthropic', 'string'],
            'ai_llm_model' => ['claude-sonnet-4-6', 'string'],
            'ai_llm_api_key' => ['', 'encrypted'],
            'ai_llm_base_url' => ['', 'string'],
            'ai_embedding_provider' => ['openai', 'string'],
            'ai_embedding_model' => ['text-embedding-3-small', 'string'],
            'ai_embedding_api_key' => ['', 'encrypted'],
            'ai_embedding_dimension' => ['1536', 'string'],
            'ai_chatbot_enabled' => ['0', 'string'],
            'ai_admin_editor_enabled' => ['0', 'string'],
            'ai_chatbot_rate_limit' => ['10', 'string'],
            'ai_chatbot_name_ms' => ['', 'string'],
            'ai_chatbot_name_en' => ['', 'string'],
            'ai_chatbot_avatar' => ['', 'string'],
            'ai_chatbot_persona_ms' => ['', 'string'],
            'ai_chatbot_persona_en' => ['', 'string'],
            'ai_chatbot_language_preference' => ['same_as_page', 'string'],
            'ai_chatbot_restrictions_ms' => ['', 'string'],
            'ai_chatbot_restrictions_en' => ['', 'string'],
            'ai_chatbot_display_location' => ['all_pages', 'string'],
            'ai_chatbot_display_pages' => ['', 'string'],
            'ai_chatbot_welcome_ms' => ['', 'string'],
            'ai_chatbot_welcome_en' => ['', 'string'],
            'ai_chatbot_placeholder_ms' => ['', 'string'],
            'ai_chatbot_placeholder_en' => ['', 'string'],
            'ai_chatbot_disclaimer_ms' => ['', 'string'],
            'ai_chatbot_disclaimer_en' => ['', 'string'],
        ];
    }

    /**
     * Chart definitions consumed by StatistikController / x-statistik.chart.
     *
     * @return array<int, array<string, mixed>>
     */
    private function charts(): array
    {
        return [
            [
                'title_ms' => 'Pelawat bulanan',
                'title_en' => 'Monthly visitors',
                'description_ms' => 'Pelawat unik dari Januari hingga Ogos 2026.',
                'description_en' => 'Unique visitors from January to August 2026.',
                'type' => 'line',
                'data' => [
                    'labels' => ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos'],
                    'datasets' => [
                        ['label' => 'Pelawat / Visitors', 'data' => [18400, 21200, 24800, 23100, 27600, 31900, 35400, 38200], 'color' => '#2563EB'],
                    ],
                ],
            ],
            [
                'title_ms' => 'Siaran diterbitkan mengikut suku tahun',
                'title_en' => 'Broadcasts published per quarter',
                'description_ms' => 'Bilangan siaran mengikut jenis pada tahun 2026.',
                'description_en' => 'Number of broadcasts by type in 2026.',
                'type' => 'bar',
                'data' => [
                    'labels' => ['S1 / Q1', 'S2 / Q2', 'S3 / Q3'],
                    'datasets' => [
                        ['label' => 'Siaran Media / Press', 'data' => [4, 5, 3], 'color' => '#1E3A8A'],
                        ['label' => 'Pengumuman / Announcements', 'data' => [6, 7, 5], 'color' => '#2563EB'],
                        ['label' => 'Berita / News', 'data' => [5, 6, 4], 'color' => '#96B7FF'],
                    ],
                ],
            ],
            [
                'title_ms' => 'Bahasa pilihan pelawat',
                'title_en' => 'Visitor language preference',
                'description_ms' => 'Peratusan sesi mengikut bahasa antara muka.',
                'description_en' => 'Share of sessions by interface language.',
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Bahasa Malaysia', 'English'],
                    'datasets' => [
                        ['label' => 'Sesi / Sessions', 'data' => [62, 38], 'backgroundColor' => ['#2563EB', '#96B7FF'], 'borderColor' => '#FFFFFF'],
                    ],
                ],
            ],
            [
                'title_ms' => 'Muat turun dasar mengikut kategori',
                'title_en' => 'Policy downloads by category',
                'description_ms' => 'Jumlah muat turun sejak Januari 2026.',
                'description_en' => 'Total downloads since January 2026.',
                'type' => 'bar',
                'data' => [
                    'labels' => ['Keselamatan', 'Data', 'Digital', 'ICT', 'Perkhidmatan'],
                    'datasets' => [
                        ['label' => 'Muat turun / Downloads', 'data' => [1240, 980, 1530, 760, 1110], 'color' => '#2563EB'],
                    ],
                ],
            ],
        ];
    }
}
