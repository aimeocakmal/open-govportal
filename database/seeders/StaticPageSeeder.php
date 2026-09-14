<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    /**
     * Slugs `penafian` and `dasar-privasi` are hardwired in routes/public.php.
     */
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            StaticPage::query()->updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'slug' => 'penafian',
                'title_ms' => 'Penafian',
                'title_en' => 'Disclaimer',
                'excerpt_ms' => 'Had tanggungjawab OpenGovPortal terhadap kandungan, pautan luar dan ketersediaan portal.',
                'excerpt_en' => 'The limits of OpenGovPortal responsibility for content, external links and portal availability.',
                'meta_title_ms' => 'Penafian | OpenGovPortal',
                'meta_title_en' => 'Disclaimer | OpenGovPortal',
                'meta_desc_ms' => 'Penafian rasmi OpenGovPortal mengenai ketepatan kandungan, pautan luar dan ketersediaan perkhidmatan.',
                'meta_desc_en' => 'The official OpenGovPortal disclaimer on content accuracy, external links and service availability.',
                'content_ms' => '<p>Maklumat di portal ini disediakan untuk tujuan makluman umum. OpenGovPortal berusaha memastikan kandungan tepat dan terkini, tetapi tidak menjamin ketepatan, kesempurnaan atau kesesuaiannya untuk sebarang tujuan tertentu.</p>
<h2>Kandungan</h2>
<p>Siaran, pencapaian, statistik dan dokumen dasar diterbitkan oleh pasukan kandungan OpenGovPortal dan boleh dikemas kini tanpa notis. Tarikh terbitan dipaparkan pada setiap halaman. Jika terdapat percanggahan antara versi Bahasa Malaysia dan Bahasa Inggeris, versi Bahasa Malaysia diguna pakai.</p>
<h2>Pautan luar</h2>
<p>Portal ini mengandungi pautan ke laman web pihak ketiga, termasuk repositori kod sumber. OpenGovPortal tidak mengawal dan tidak bertanggungjawab terhadap kandungan, dasar privasi atau amalan laman tersebut.</p>
<h2>Pembantu AI</h2>
<p>Jawapan pembantu AI dijana secara automatik daripada kandungan portal dan mungkin tidak tepat. Sentiasa rujuk halaman sumber yang dipautkan sebelum bertindak berdasarkan jawapan tersebut.</p>
<h2>Ketersediaan</h2>
<p>OpenGovPortal tidak menjamin portal ini bebas daripada gangguan atau ralat. Penyelenggaraan berjadual akan diumumkan di halaman Siaran sekurang-kurangnya tujuh hari lebih awal apabila boleh.</p>
<h2>Had tanggungjawab</h2>
<p>Setakat yang dibenarkan oleh undang-undang, OpenGovPortal tidak bertanggungjawab terhadap sebarang kerugian atau kerosakan yang timbul daripada penggunaan portal ini atau pergantungan kepada kandungannya.</p>
<p>Penafian ini dikemas kini pada 16 Jun 2026.</p>',
                'content_en' => '<p>The information on this portal is provided for general information purposes. OpenGovPortal works to keep content accurate and current, but does not guarantee its accuracy, completeness or suitability for any particular purpose.</p>
<h2>Content</h2>
<p>Broadcasts, achievements, statistics and policy documents are published by the OpenGovPortal content team and may be updated without notice. The publication date is shown on every page. Where the Bahasa Malaysia and English versions differ, the Bahasa Malaysia version prevails.</p>
<h2>External links</h2>
<p>This portal links to third-party websites, including the source code repository. OpenGovPortal does not control and is not responsible for the content, privacy policies or practices of those sites.</p>
<h2>AI assistant</h2>
<p>AI assistant answers are generated automatically from portal content and may be inaccurate. Always check the linked source page before acting on an answer.</p>
<h2>Availability</h2>
<p>OpenGovPortal does not guarantee that this portal will be free from interruptions or errors. Scheduled maintenance is announced on the Broadcasts page at least seven days in advance where possible.</p>
<h2>Limitation of liability</h2>
<p>To the extent permitted by law, OpenGovPortal is not liable for any loss or damage arising from the use of this portal or reliance on its content.</p>
<p>This disclaimer was last updated on 16 June 2026.</p>',
                'status' => 'published',
                'is_in_sitemap' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'dasar-privasi',
                'title_ms' => 'Dasar Privasi',
                'title_en' => 'Privacy Policy',
                'excerpt_ms' => 'Data yang dikumpul oleh portal ini, sebab ia dikumpul, tempoh simpanan dan hak anda.',
                'excerpt_en' => 'What data this portal collects, why it is collected, how long it is kept and your rights.',
                'meta_title_ms' => 'Dasar Privasi | OpenGovPortal',
                'meta_title_en' => 'Privacy Policy | OpenGovPortal',
                'meta_desc_ms' => 'Cara OpenGovPortal mengumpul, menggunakan dan menyimpan data pelawat, termasuk borang maklum balas, pembantu AI dan kuki.',
                'meta_desc_en' => 'How OpenGovPortal collects, uses and stores visitor data, including the feedback form, the AI assistant and cookies.',
                'content_ms' => '<p>OpenGovPortal menghormati privasi anda. Dasar ini menerangkan data yang dikumpul apabila anda menggunakan portal ini, tujuan pengumpulan dan tempoh simpanannya.</p>
<h2>Data yang dikumpul</h2>
<ul>
<li><strong>Borang maklum balas:</strong> nama, alamat emel, subjek, mesej dan halaman yang anda lawati semasa menghantar. Alamat IP disimpan hanya untuk had kadar.</li>
<li><strong>Pembantu AI:</strong> teks soalan dan jawapan dalam satu sesi, bersama pengecam sesi rawak. Perbualan tidak dikaitkan dengan nama atau emel anda.</li>
<li><strong>Analitik:</strong> jika pentadbir mengaktifkan analitik, data lawatan tanpa nama seperti halaman yang dilihat dan jenis peranti dikumpul.</li>
</ul>
<h2>Tujuan penggunaan</h2>
<p>Data digunakan untuk membalas maklum balas anda, menambah baik jawapan pembantu AI, mengukur penggunaan portal dan melindungi portal daripada penyalahgunaan.</p>
<h2>Tempoh simpanan</h2>
<ul>
<li>Maklum balas disimpan selama 24 bulan selepas dibalas atau diarkibkan.</li>
<li>Alamat IP pada borang maklum balas dipadam selepas 30 hari.</li>
<li>Perbualan pembantu AI dipadam secara automatik selepas 90 hari.</li>
<li>Log aktiviti pentadbiran disimpan selama 12 bulan.</li>
</ul>
<h2>Kuki</h2>
<p>Portal ini menggunakan tiga kuki sahaja: kuki sesi untuk keselamatan borang, kuki tema untuk mengingati tema pilihan anda dan kuki bahasa untuk mengingati bahasa antara muka. Tiada kuki pengiklanan digunakan.</p>
<h2>Perkongsian data</h2>
<p>Data tidak dijual atau dikongsi dengan pihak ketiga, kecuali kepada pembekal AI yang dipilih pentadbir bagi memproses soalan pembantu AI. Teks soalan dihantar kepada pembekal tersebut tanpa sebarang pengecam peribadi.</p>
<h2>Hak anda</h2>
<p>Anda boleh meminta salinan atau pemadaman data yang anda hantar melalui borang maklum balas dengan menghubungi <a href="mailto:privasi@opengovportal.example">privasi@opengovportal.example</a>. Permintaan dibalas dalam masa 14 hari bekerja.</p>
<p>Dasar ini dikemas kini pada 16 Jun 2026.</p>',
                'content_en' => '<p>OpenGovPortal respects your privacy. This policy explains what data is collected when you use this portal, why it is collected and how long it is kept.</p>
<h2>Data collected</h2>
<ul>
<li><strong>Feedback form:</strong> name, email address, subject, message and the page you were on when you submitted. The IP address is stored only for rate limiting.</li>
<li><strong>AI assistant:</strong> the question and answer text within one session, together with a random session identifier. Conversations are not linked to your name or email.</li>
<li><strong>Analytics:</strong> if an administrator enables analytics, anonymous visit data such as pages viewed and device type is collected.</li>
</ul>
<h2>How data is used</h2>
<p>Data is used to answer your feedback, improve AI assistant answers, measure portal usage and protect the portal from abuse.</p>
<h2>Retention</h2>
<ul>
<li>Feedback is kept for 24 months after it is answered or archived.</li>
<li>IP addresses on the feedback form are removed after 30 days.</li>
<li>AI assistant conversations are deleted automatically after 90 days.</li>
<li>Admin activity logs are kept for 12 months.</li>
</ul>
<h2>Cookies</h2>
<p>This portal uses three cookies only: a session cookie for form security, a theme cookie to remember your chosen theme and a language cookie to remember the interface language. No advertising cookies are used.</p>
<h2>Sharing</h2>
<p>Data is not sold or shared with third parties, except with the AI provider chosen by the administrator to process AI assistant questions. Question text is sent to that provider without any personal identifier.</p>
<h2>Your rights</h2>
<p>You can request a copy or deletion of data you submitted through the feedback form by contacting <a href="mailto:privasi@opengovportal.example">privasi@opengovportal.example</a>. Requests are answered within 14 working days.</p>
<p>This policy was last updated on 16 June 2026.</p>',
                'status' => 'published',
                'is_in_sitemap' => true,
                'sort_order' => 2,
            ],
        ];
    }
}
