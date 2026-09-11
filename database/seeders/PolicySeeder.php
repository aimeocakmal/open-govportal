<?php

namespace Database\Seeders;

use App\Models\Policy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    /**
     * The policy card rounds file_size to one decimal in MB, so sizes below
     * 50 KB (the one-page sample PDFs) would display as "0.0 MB". Leave them
     * null until real documents replace the samples.
     */
    public const MIN_DISPLAYED_FILE_SIZE = 51200;

    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            $filePath = public_path('files/policies/'.$row['slug'].'.pdf');
            $hasFile = $row['status'] === 'published' && is_file($filePath);
            $fileSize = $hasFile ? filesize($filePath) : null;

            Policy::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'file_url' => $hasFile ? '/files/policies/'.$row['slug'].'.pdf' : null,
                    'file_size' => $fileSize !== null && $fileSize >= self::MIN_DISPLAYED_FILE_SIZE ? $fileSize : null,
                    'created_by' => $adminId,
                ]),
            );
        }
    }

    /**
     * Two published rows per category so every filter chip has results.
     * Category must be one of: keselamatan, data, digital, ict, perkhidmatan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            ['slug' => 'dasar-keselamatan-kandungan-dan-akaun', 'category' => 'keselamatan', 'status' => 'published', 'published_at' => '2026-08-18 09:00:00', 'download_count' => 412,
                'title_ms' => 'Dasar Keselamatan Kandungan dan Akaun',
                'title_en' => 'Content and Account Security Policy',
                'description_ms' => 'Keperluan kata laluan, pengesahan dua faktor, peranan pengguna dan log aktiviti untuk semua akaun pentadbiran portal.',
                'description_en' => 'Password requirements, two-factor authentication, user roles and activity logging for all portal admin accounts.'],
            ['slug' => 'garis-panduan-pengendalian-insiden', 'category' => 'keselamatan', 'status' => 'published', 'published_at' => '2026-02-17 09:00:00', 'download_count' => 268,
                'title_ms' => 'Garis Panduan Pengendalian Insiden',
                'title_en' => 'Incident Handling Guidelines',
                'description_ms' => 'Langkah melapor, mengasingkan dan mendokumentasikan insiden keselamatan, termasuk tempoh masa pemberitahuan kepada pengguna.',
                'description_en' => 'Steps to report, contain and document security incidents, including notification timelines for users.'],
            ['slug' => 'dasar-pengurusan-data-terbuka', 'category' => 'data', 'status' => 'published', 'published_at' => '2026-07-21 09:00:00', 'download_count' => 355,
                'title_ms' => 'Dasar Pengurusan Data Terbuka',
                'title_en' => 'Open Data Management Policy',
                'description_ms' => 'Prinsip penerbitan set data awam: format terbuka, lesen, kekerapan kemas kini dan tanggungjawab pemilik data.',
                'description_en' => 'Principles for publishing public datasets: open formats, licensing, update frequency and data owner responsibilities.'],
            ['slug' => 'garis-panduan-pengekalan-rekod', 'category' => 'data', 'status' => 'published', 'published_at' => '2026-01-20 09:00:00', 'download_count' => 190,
                'title_ms' => 'Garis Panduan Pengekalan Rekod',
                'title_en' => 'Records Retention Guidelines',
                'description_ms' => 'Tempoh simpanan untuk maklum balas, log aktiviti, perbualan pembantu AI dan sejarah versi kandungan.',
                'description_en' => 'Retention periods for feedback, activity logs, AI assistant conversations and content version history.'],
            ['slug' => 'dasar-kebolehcapaian-digital', 'category' => 'digital', 'status' => 'published', 'published_at' => '2026-06-23 09:00:00', 'download_count' => 521,
                'title_ms' => 'Dasar Kebolehcapaian Digital',
                'title_en' => 'Digital Accessibility Policy',
                'description_ms' => 'Komitmen kepada WCAG 2.1 tahap AA, jadual audit dan cara pelawat melaporkan halangan kebolehcapaian.',
                'description_en' => 'Commitment to WCAG 2.1 level AA, the audit schedule and how visitors can report accessibility barriers.'],
            ['slug' => 'garis-panduan-kandungan-dwibahasa', 'category' => 'digital', 'status' => 'published', 'published_at' => '2025-12-16 09:00:00', 'download_count' => 478,
                'title_ms' => 'Garis Panduan Kandungan Dwibahasa',
                'title_en' => 'Bilingual Content Guidelines',
                'description_ms' => 'Gaya penulisan, glosari istilah dan aliran semakan untuk kandungan dalam Bahasa Malaysia dan Bahasa Inggeris.',
                'description_en' => 'Writing style, terminology glossary and review workflow for content in Bahasa Malaysia and English.'],
            ['slug' => 'piawaian-pembangunan-tema', 'category' => 'ict', 'status' => 'published', 'published_at' => '2026-05-26 09:00:00', 'download_count' => 233,
                'title_ms' => 'Piawaian Pembangunan Tema',
                'title_en' => 'Theme Development Standards',
                'description_ms' => 'Struktur folder tema, token reka bentuk yang wajib disokong dan senarai semak sebelum tema diaktifkan.',
                'description_en' => 'Theme folder structure, the design tokens every theme must support and the checklist before a theme goes live.'],
            ['slug' => 'dasar-penggunaan-ai-dalam-kandungan', 'category' => 'ict', 'status' => 'published', 'published_at' => '2026-04-28 09:00:00', 'download_count' => 389,
                'title_ms' => 'Dasar Penggunaan AI dalam Kandungan',
                'title_en' => 'AI Use in Content Policy',
                'description_ms' => 'Bila tindakan AI boleh digunakan, keperluan semakan manusia sebelum penerbitan dan cara penggunaan direkodkan.',
                'description_en' => 'When AI actions may be used, the human review required before publishing and how usage is logged.'],
            ['slug' => 'piagam-perkhidmatan-portal', 'category' => 'perkhidmatan', 'status' => 'published', 'published_at' => '2026-03-31 09:00:00', 'download_count' => 302,
                'title_ms' => 'Piagam Perkhidmatan Portal',
                'title_en' => 'Portal Service Charter',
                'description_ms' => 'Sasaran masa muat halaman, ketersediaan 99.9 peratus dan tempoh balasan maklum balas dalam tiga hari bekerja.',
                'description_en' => 'Page load targets, 99.9 percent availability and a three-working-day response time for feedback.'],
            ['slug' => 'garis-panduan-maklum-balas-awam', 'category' => 'perkhidmatan', 'status' => 'published', 'published_at' => '2025-11-18 09:00:00', 'download_count' => 157,
                'title_ms' => 'Garis Panduan Maklum Balas Awam',
                'title_en' => 'Public Feedback Guidelines',
                'description_ms' => 'Cara maklum balas diterima, dikelaskan, dibalas dan diarkibkan, serta maklumat peribadi yang tidak perlu dihantar.',
                'description_en' => 'How feedback is received, classified, answered and archived, and which personal details should not be sent.'],
            ['slug' => 'dasar-tema-gelap', 'category' => 'digital', 'status' => 'draft', 'published_at' => null, 'download_count' => 0,
                'title_ms' => 'Dasar Tema Gelap (Draf)',
                'title_en' => 'Dark Theme Policy (Draft)',
                'description_ms' => 'Draf keperluan kontras dan warna untuk tema gelap yang dirancang pada suku keempat 2026.',
                'description_en' => 'Draft contrast and colour requirements for the dark theme planned for the fourth quarter of 2026.'],
        ];
    }
}
