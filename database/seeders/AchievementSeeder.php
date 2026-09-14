<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            Achievement::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'icon' => '/images/icons/achievements/'.$row['icon'].'.svg',
                    'published_at' => $row['status'] === 'published' ? $row['date'].' 09:00:00' : null,
                    'created_by' => $adminId,
                ]),
            );
        }
    }

    /**
     * Milestones spread across 2023–2026 so the year filter has four options.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            ['slug' => 'audit-kebolehcapaian-wcag-2-1-aa-lulus', 'date' => '2026-08-11', 'icon' => 'shield', 'is_featured' => true, 'status' => 'published',
                'title_ms' => 'Audit kebolehcapaian WCAG 2.1 AA lulus',
                'title_en' => 'WCAG 2.1 AA accessibility audit passed',
                'description_ms' => '<p>Kesemua sepuluh halaman awam lulus audit bebas tanpa isu kritikal. Tiga isu kecil dibetulkan dalam versi 0.7.0.</p>',
                'description_en' => '<p>All ten public pages passed an independent audit with no critical issues. Three minor issues were fixed in version 0.7.0.</p>'],
            ['slug' => 'editor-kandungan-berbantu-ai', 'date' => '2026-07-14', 'icon' => 'rocket', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Editor kandungan berbantu AI dilancarkan',
                'title_en' => 'AI-assisted content editor launched',
                'description_ms' => '<p>Tindakan Baiki Tatabahasa, Ringkaskan, Kembangkan dan Terjemah kini tersedia di dalam editor teks kaya.</p>',
                'description_en' => '<p>Fix Grammar, Summarise, Expand and Translate actions are now available inside the rich text editor.</p>'],
            ['slug' => '600-ujian-automatik', 'date' => '2026-07-01', 'icon' => 'chart', 'is_featured' => false, 'status' => 'published',
                'title_ms' => '600 ujian automatik',
                'title_en' => '600 automated tests',
                'description_ms' => '<p>Suite ujian melepasi 600 ujian dan 1,100 penegasan, merangkumi setiap laluan awam dalam kedua-dua bahasa.</p>',
                'description_en' => '<p>The test suite passed 600 tests and 1,100 assertions, covering every public route in both languages.</p>'],
            ['slug' => 'sistem-tema-boleh-tukar', 'date' => '2026-05-19', 'icon' => 'star', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Sistem tema boleh tukar',
                'title_en' => 'Switchable theme system',
                'description_ms' => '<p>Paparan awam dipisahkan daripada logik aplikasi. Pentadbir boleh menukar tema tanpa menyentuh kod.</p>',
                'description_en' => '<p>The public presentation is separated from application logic. Administrators can switch themes without touching code.</p>'],
            ['slug' => 'kesemua-10-halaman-awam-siap', 'date' => '2026-03-24', 'icon' => 'trophy', 'is_featured' => true, 'status' => 'published',
                'title_ms' => 'Kesemua 10 halaman awam siap',
                'title_en' => 'All 10 public pages live',
                'description_ms' => '<p>Versi 0.6.0 melengkapkan setiap halaman awam dalam Bahasa Malaysia dan Bahasa Inggeris, termasuk menu kebolehcapaian.</p>',
                'description_en' => '<p>Version 0.6.0 completes every public page in Bahasa Malaysia and English, including the accessibility menu.</p>'],
            ['slug' => 'kod-sumber-dibuka-kepada-umum', 'date' => '2026-01-14', 'icon' => 'globe', 'is_featured' => true, 'status' => 'published',
                'title_ms' => 'Kod sumber dibuka kepada umum',
                'title_en' => 'Source code opened to the public',
                'description_ms' => '<p>Repositori awam dibuka bersama dokumentasi, pelan hala tuju dan panduan penyumbang.</p>',
                'description_en' => '<p>The public repository opened with documentation, a roadmap and a contributor guide.</p>'],
            ['slug' => 'panel-pentadbiran-lengkap', 'date' => '2025-11-18', 'icon' => 'document', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Panel pentadbiran lengkap',
                'title_en' => 'Admin panel complete',
                'description_ms' => '<p>Semua jenis kandungan, tetapan laman, pengguna dan peranan boleh diurus melalui satu panel pentadbiran dwibahasa.</p>',
                'description_en' => '<p>Every content type, site setting, user and role can be managed from one bilingual admin panel.</p>'],
            ['slug' => 'menu-mega-empat-peringkat', 'date' => '2025-09-09', 'icon' => 'star', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Menu mega empat peringkat',
                'title_en' => 'Four-level mega menu',
                'description_ms' => '<p>Navigasi pengepala dan kaki laman kini diurus dari satu tempat dengan keterlihatan mengikut peranan.</p>',
                'description_en' => '<p>Header and footer navigation are now managed from one place with role-based visibility.</p>'],
            ['slug' => 'carian-teks-penuh-dwibahasa', 'date' => '2025-06-24', 'icon' => 'chart', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Carian teks penuh dwibahasa',
                'title_en' => 'Bilingual full-text search',
                'description_ms' => '<p>Carian berjalan di dalam PostgreSQL dengan indeks berasingan untuk setiap bahasa, tanpa perkhidmatan luar.</p>',
                'description_en' => '<p>Search runs inside PostgreSQL with a separate index for each language and no external service.</p>'],
            ['slug' => 'sokongan-lima-penyedia-storan-awan', 'date' => '2025-04-15', 'icon' => 'globe', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Sokongan lima penyedia storan awan',
                'title_en' => 'Support for five cloud storage providers',
                'description_ms' => '<p>Media boleh disimpan pada cakera tempatan atau empat penyedia awan, ditukar melalui tetapan tanpa mula semula.</p>',
                'description_en' => '<p>Media can be stored on local disk or four cloud providers, switched from settings without a restart.</p>'],
            ['slug' => 'dwibahasa-sejak-hari-pertama', 'date' => '2025-01-21', 'icon' => 'users', 'is_featured' => true, 'status' => 'published',
                'title_ms' => 'Dwibahasa sejak hari pertama',
                'title_en' => 'Bilingual from day one',
                'description_ms' => '<p>Setiap medan kandungan mempunyai versi Bahasa Malaysia dan Bahasa Inggeris, dan setiap laluan awam diuji dalam kedua-dua bahasa.</p>',
                'description_en' => '<p>Every content field has a Bahasa Malaysia and an English version, and every public route is tested in both languages.</p>'],
            ['slug' => 'prototaip-pertama-dipersembahkan', 'date' => '2024-10-08', 'icon' => 'rocket', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Prototaip pertama dipersembahkan',
                'title_en' => 'First prototype demonstrated',
                'description_ms' => '<p>Laman utama dan senarai siaran yang boleh dijalankan dipersembahkan kepada tiga agensi perintis.</p>',
                'description_en' => '<p>A working homepage and broadcast listing were demonstrated to three pilot agencies.</p>'],
            ['slug' => 'sistem-reka-bentuk-myds-diguna-pakai', 'date' => '2024-06-11', 'icon' => 'star', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Sistem reka bentuk MyDS diguna pakai',
                'title_en' => 'MyDS design system adopted',
                'description_ms' => '<p>Token warna, tipografi dan jarak MyDS dijadikan asas tema lalai portal.</p>',
                'description_en' => '<p>MyDS colour, typography and spacing tokens became the basis of the default portal theme.</p>'],
            ['slug' => 'pasukan-teras-ditubuhkan', 'date' => '2024-02-20', 'icon' => 'users', 'is_featured' => false, 'status' => 'published',
                'title_ms' => 'Pasukan teras ditubuhkan',
                'title_en' => 'Core team formed',
                'description_ms' => '<p>Lima orang dari bidang kandungan, kejuruteraan dan reka bentuk membentuk pasukan teras pertama.</p>',
                'description_en' => '<p>Five people from content, engineering and design formed the first core team.</p>'],
            ['slug' => 'idea-opengovportal-dilahirkan', 'date' => '2023-11-14', 'icon' => 'trophy', 'is_featured' => true, 'status' => 'published',
                'title_ms' => 'Idea OpenGovPortal dilahirkan',
                'title_en' => 'The idea for OpenGovPortal is born',
                'description_ms' => '<p>Cadangan satu halaman: portal kerajaan sumber terbuka yang dwibahasa dan mudah diakses, boleh dipasang oleh pasukan kecil.</p>',
                'description_en' => '<p>A one-page proposal: an open-source, bilingual and accessible government portal that a small team can install.</p>'],
            ['slug' => 'pelancaran-versi-1-0', 'date' => '2026-12-01', 'icon' => 'rocket', 'is_featured' => false, 'status' => 'draft',
                'title_ms' => 'Pelancaran versi 1.0',
                'title_en' => 'Version 1.0 launch',
                'description_ms' => '<p>Draf. Keluaran stabil pertama dirancang pada Disember 2026.</p>',
                'description_en' => '<p>Draft. The first stable release is planned for December 2026.</p>'],
        ];
    }
}
