# Sample Content Pack

Fictional, bilingual demo content that the seeders produce. The organisation behind the portal is **OpenGovPortal
itself**: broadcasts are release notes and how-to posts, achievements are project milestones, staff are a fictional
portal team, and the profile page shows a fictional head of the portal. Nothing in this pack refers to a real
organisation, person, address or external service.

Pair this document with [sample-images.md](sample-images.md), which specifies every image slot and the committed asset
pack.

---

## How To Apply

```bash
php artisan migrate:fresh --seed --no-interaction   # full reset, canonical way to load the pack
php artisan cache:clear                              # settings, navigation and page caches are 1–24 h

php artisan db:seed --no-interaction                 # safe to repeat: rows are keyed by slug, url, email or key
php artisan cache:clear
```

Every seeder uses `updateOrCreate()` on a natural key, so re-running updates rows in place. Two seeders rebuild their
tables from scratch on each run because their rows have no stable key: `FooterSettingSeeder` (branding block and social
icons) and the footer part of `MenuSeeder` (the three link columns). Admin edits to those two areas are replaced on
every seed.

Full body text (broadcast articles, static pages, vision and mission) lives in the seeder files named in each section
below. Edit the seeder, re-seed, and clear the cache. This document is the inventory and the rules; the seeders are the
copy.

---

## Fictional Identity

| Item                   | Value                                                                                          |
|------------------------|------------------------------------------------------------------------------------------------|
| Organisation           | OpenGovPortal                                                                                  |
| Tagline (ms)           | Portal kerajaan sumber terbuka untuk perkhidmatan awam.                                        |
| Tagline (en)           | An open-source portal for public services.                                                     |
| Description (ms)       | Portal kerajaan sumber terbuka yang dwibahasa, mudah diakses dan pantas.                       |
| Description (en)       | An open-source government portal that is bilingual, accessible and fast.                       |
| Public email domain    | `opengovportal.example` (reserved TLD, never resolves)                                         |
| Feedback sender domain | `example.com`                                                                                  |
| Postal address         | Aras 5, Menara Portal, Jalan Contoh 1, 50000 Bandar Contoh                                     |
| Phone pattern          | `+603-0000 1xxx`                                                                               |
| Repository             | `https://github.com/aimeocakmal/open-govportal` (the only real external link)                  |
| Head of organisation   | Nurul Hidayah binti Azman, Ketua Pegawai Digital / Chief Digital Officer, appointed 2025-01-06 |

### Writing rules

- Write the Bahasa Malaysia version first and natively; the English version carries the same meaning, not the same
  words.
- Plain verbs, sentence case, active voice. A call to action says what happens next: "Baca pengumuman" / "Read the
  announcement".
- Keep sentences under 25 words. Excerpts are at most two sentences.
- Feature names match the navigation: Siaran / Broadcasts, Pencapaian / Achievements, Statistik / Statistics,
  Direktori / Directory, Dasar / Policy, Hubungi Kami / Contact Us.
- People get mixed Malaysian names with no honorifics tied to public office. Refer to them without gendered pronouns in
  documentation.
- Slugs are Bahasa Malaysia, lowercase, hyphenated, and shared by both locales (single `slug` column).
- Dates are absolute. The pack is dated around September 2026; bump the years when the pack starts to look stale.

---

## Demo Logins

Seeded by `database/seeders/UserSeeder.php`. Password for all four is `password`.

| Email                             | Role             | Locale | Department            |
|-----------------------------------|------------------|--------|-----------------------|
| `admin@opengovportal.example`     | `super_admin`    | ms     | —                     |
| `editor@opengovportal.example`    | `content_editor` | ms     | Kandungan & Editorial |
| `publisher@opengovportal.example` | `publisher`      | ms     | —                     |
| `viewer@opengovportal.example`    | `viewer`         | en     | —                     |

Content rows carry `created_by` = the super admin so admin lists show an author.

---

## Settings (`SettingsSeeder`)

`database/seeders/SettingsSeeder.php` writes through `Setting::set()`, which also clears the per-key cache.

| Key                                                                                      | Value                                                                                                             |
|------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------|
| `site_name_ms` / `site_name_en`                                                          | OpenGovPortal                                                                                                     |
| `site_description_ms` / `_en`                                                            | Description above                                                                                                 |
| `homepage_show_hero_banner`, `_quick_links`, `_broadcasts`, `_achievements`, `_feedback` | `1`                                                                                                               |
| `homepage_broadcasts_count` / `homepage_achievements_count`                              | `6` / `7`                                                                                                         |
| `homepage_section_order`                                                                 | `["hero_banner","quick_links","broadcasts","achievements"]`                                                       |
| `vision_ms` / `vision_en`                                                                | One `<p>`: every agency can publish clear, bilingual, accessible information without closed systems               |
| `mission_ms` / `mission_en`                                                              | Four-item `<ul>`: installable by a small team; both languages first-class; WCAG 2.1 AA; every decision documented |
| `about_ms` / `about_en`                                                                  | Two `<p>`: what the portal is built with and who maintains it                                                     |
| `statistik_charts`                                                                       | JSON array of four charts (below), type `json`                                                                    |
| `ai_*`                                                                                   | Provider defaults unchanged; all `ai_chatbot_*` copy keys empty so the lang defaults apply                        |

> **Not seeded on purpose:** `site_logo`, `site_logo_dark`, `site_favicon` and `ai_chatbot_avatar` are Filament upload
> fields. A seeded path would be dropped on the next admin save. Upload the SVGs from the asset pack through Site Info and
> AI Settings instead (see [sample-images.md](sample-images.md)).

### Statistics charts

| # | Title (ms / en)                                                           | Type            | Labels                   | Data                                         |
|---|---------------------------------------------------------------------------|-----------------|--------------------------|----------------------------------------------|
| 1 | Pelawat bulanan / Monthly visitors                                        | line            | Jan–Ogos 2026            | 18 400 → 38 200 rising                       |
| 2 | Siaran diterbitkan mengikut suku tahun / Broadcasts published per quarter | bar, 3 datasets | S1–S3                    | press 4/5/3, announcements 6/7/5, news 5/6/4 |
| 3 | Bahasa pilihan pelawat / Visitor language preference                      | doughnut        | Bahasa Malaysia, English | 62 / 38                                      |
| 4 | Muat turun dasar mengikut kategori / Policy downloads by category         | bar             | 5 categories             | 1240, 980, 1530, 760, 1110                   |

Colours come from the primary scale only: `#1E3A8A`, `#2563EB`, `#96B7FF`. Chart labels are single-language; where both
languages are needed they are joined with a slash ("Pelawat / Visitors").

---

## Language-File Strings

Public pages read the site name and page descriptions from `lang/{ms,en}/*.php`, not from the settings table. These keys
were rebranded; the MyDS masthead keys (`common.masthead.*`) and all navigation labels are unchanged.

| File : key                                      | ms                                                                                                              | en                                                                                                                    |
|-------------------------------------------------|-----------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------|
| `common.php : site_name`                        | OpenGovPortal                                                                                                   | OpenGovPortal                                                                                                         |
| `common.php : site_tagline`                     | Portal kerajaan sumber terbuka untuk perkhidmatan awam.                                                         | An open-source portal for public services.                                                                            |
| `home.php : hero.default_title`                 | Selamat datang ke OpenGovPortal                                                                                 | Welcome to OpenGovPortal                                                                                              |
| `home.php : hero.default_subtitle`              | Portal kerajaan sumber terbuka yang dwibahasa, mudah diakses dan pantas.                                        | An open-source government portal that is bilingual, accessible and fast.                                              |
| `home.php : achievements.description`           | Detik penting dalam perjalanan OpenGovPortal.                                                                   | Milestones in the OpenGovPortal journey.                                                                              |
| `siaran.php : description`                      | Siaran media, pengumuman dan berita terkini daripada OpenGovPortal.                                             | Press releases, announcements and latest news from OpenGovPortal.                                                     |
| `pencapaian.php : description`                  | Detik penting dalam perjalanan OpenGovPortal.                                                                   | Milestones in the OpenGovPortal journey.                                                                              |
| `statistik.php : description`                   | Statistik dan petunjuk prestasi utama (KPI) OpenGovPortal.                                                      | Statistics and key performance indicators (KPIs) of OpenGovPortal.                                                    |
| `direktori.php : description`                   | Direktori kakitangan OpenGovPortal.                                                                             | Staff directory of OpenGovPortal.                                                                                     |
| `dasar.php : description`                       | Senarai dasar dan garis panduan OpenGovPortal.                                                                  | List of policies and guidelines of OpenGovPortal.                                                                     |
| `hubungi.php : description`                     | Hubungi OpenGovPortal untuk sebarang pertanyaan.                                                                | Contact OpenGovPortal for any enquiries.                                                                              |
| `carian.php : description`                      | Cari maklumat di portal OpenGovPortal.                                                                          | Search for information on the OpenGovPortal website.                                                                  |
| `profil.php : description`                      | Profil organisasi OpenGovPortal.                                                                                | Profile of the OpenGovPortal organisation.                                                                            |
| `profil.php : minister`                         | Ketua organisasi                                                                                                | Head of organisation                                                                                                  |
| `profil.php : about`                            | Mengenai OpenGovPortal                                                                                          | About OpenGovPortal                                                                                                   |
| `profil.php : no_minister`                      | Tiada profil ketua organisasi buat masa ini.                                                                    | No leadership profile available yet.                                                                                  |
| `ai.php : default_name`                         | Pembantu OpenGovPortal                                                                                          | OpenGovPortal Assistant                                                                                               |
| `ai.php : default_persona`                      | Anda ialah pembantu AI rasmi OpenGovPortal. Jawab dengan sopan, jelas dan ringkas berdasarkan kandungan portal. | You are the official AI assistant for OpenGovPortal. Respond politely, clearly and concisely based on portal content. |
| `ai.php : default_welcome`                      | Selamat datang! Saya boleh membantu anda mencari maklumat di OpenGovPortal.                                     | Welcome! I can help you find information on OpenGovPortal.                                                            |
| `filament.php : resource.quick_links.icon_help` | Laluan imej ikon, contohnya /images/icons/quick-links/siaran.svg                                                | Icon image path, e.g. /images/icons/quick-links/siaran.svg                                                            |

Tests that assert these strings: `tests/Feature/HomepageTest.php`, `StatistikPageTest.php`, `DirektoriPageTest.php`,
`ProfilKementerianPageTest.php`. Update them together with the lang files.

---

## Hero Banners (`HeroBannerSeeder`)

Keyed by `sort_order`. `image` is required by the schema, so every row points at a committed placeholder. Internal CTA
URLs carry the `/ms` prefix because the column is single-locale and the view prints it as-is.

| # | Title (ms / en)                                                               | CTA (ms / en)                           | CTA URL                                          | Image                      | Active |
|---|-------------------------------------------------------------------------------|-----------------------------------------|--------------------------------------------------|----------------------------|--------|
| 1 | Selamat datang ke OpenGovPortal / Welcome to OpenGovPortal                    | Lihat profil kami / See our profile     | `/ms/profil-kementerian`                         | `/images/hero/hero-01.svg` | yes    |
| 2 | Dasar dan garis panduan di satu tempat / Policies and guidelines in one place | Layari dasar / Browse policies          | `/ms/dasar`                                      | `/images/hero/hero-02.svg` | yes    |
| 3 | Pembantu AI kini dalam fasa beta / AI assistant now in beta                   | Baca pengumuman / Read the announcement | `/ms/siaran/pembantu-ai-kini-dalam-fasa-beta`    | `/images/hero/hero-03.svg` | yes    |
| 4 | Sesi latihan untuk editor kandungan / Training sessions for content editors   | Daftar sekarang / Register now          | `/ms/siaran/sesi-latihan-untuk-editor-kandungan` | `/images/hero/hero-04.svg` | no     |

Subtitles are one sentence each and both `image_alt_*` columns describe the artwork. Banner 3 links to a published
broadcast slug; keep the two in sync.

---

## Quick Links (`QuickLinkSeeder`)

Six links, one per section, keyed by `url`. Icons are image paths because the component renders `<img src>`.

| Label (ms / en)           | URL                | Icon                                   |
|---------------------------|--------------------|----------------------------------------|
| Siaran / Broadcasts       | `/ms/siaran`       | `/images/icons/quick-links/siaran.svg` |
| Pencapaian / Achievements | `/ms/pencapaian`   | `…/pencapaian.svg`                     |
| Statistik / Statistics    | `/ms/statistik`    | `…/statistik.svg`                      |
| Dasar / Policies          | `/ms/dasar`        | `…/dasar.svg`                          |
| Direktori / Directory     | `/ms/direktori`    | `…/direktori.svg`                      |
| Hubungi Kami / Contact Us | `/ms/hubungi-kami` | `…/hubungi-kami.svg`                   |

---

## Broadcasts (`BroadcastSeeder`)

19 rows keyed by `slug`: 18 published (6 per type) and 1 draft. The homepage shows the latest 6; the listing paginates
at 15, so page 2 exists; each detail page finds 3 related items of the same type. Featured images cycle through
`broadcast-01.svg` to `broadcast-06.svg`. Bodies are two to four `<p>` blocks, some with a `<ul>` or `<ol>`.

| Published  | Type          | Slug                                                     | Title (en)                                     |
|------------|---------------|----------------------------------------------------------|------------------------------------------------|
| 2026-09-08 | press_release | `opengovportal-0-7-0-kini-tersedia`                      | OpenGovPortal 0.7.0 is now available           |
| 2026-09-01 | announcement  | `penyelenggaraan-berjadual-20-september-2026`            | Scheduled maintenance on 20 September 2026     |
| 2026-08-25 | announcement  | `pembantu-ai-kini-dalam-fasa-beta`                       | AI assistant now in beta                       |
| 2026-08-11 | press_release | `audit-kebolehcapaian-wcag-2-1-aa-selesai`               | WCAG 2.1 AA accessibility audit completed      |
| 2026-08-04 | news          | `cara-menerbitkan-siaran-pertama-anda`                   | How to publish your first broadcast            |
| 2026-07-28 | announcement  | `sesi-latihan-untuk-editor-kandungan`                    | Training sessions for content editors          |
| 2026-07-14 | press_release | `editor-kandungan-berbantu-ai-dilancarkan`               | AI-assisted content editor launched            |
| 2026-06-30 | news          | `di-sebalik-tabir-carian-dwibahasa`                      | Behind the scenes: bilingual search            |
| 2026-06-16 | announcement  | `dasar-privasi-portal-dikemas-kini`                      | Portal Privacy Policy updated                  |
| 2026-05-19 | press_release | `sistem-tema-baharu-untuk-portal`                        | New theme system for the portal                |
| 2026-05-05 | news          | `perjumpaan-komuniti-sumber-terbuka-pertama`             | First open-source community meetup             |
| 2026-04-21 | announcement  | `had-kadar-baharu-untuk-borang-maklum-balas`             | New rate limit for the feedback form           |
| 2026-04-07 | news          | `dokumentasi-portal-dikemas-kini-sepenuhnya`             | Portal documentation fully refreshed           |
| 2026-03-24 | press_release | `opengovportal-0-6-0-semua-halaman-awam-siap`            | OpenGovPortal 0.6.0: all public pages complete |
| 2026-03-10 | news          | `statistik-portal-kini-dengan-carta-interaktif`          | Portal statistics now with interactive charts  |
| 2026-02-24 | announcement  | `direktori-kakitangan-kini-boleh-dicari-secara-langsung` | Staff directory now searchable in real time    |
| 2026-02-10 | news          | `lima-petua-menulis-kandungan-dwibahasa`                 | Five tips for writing bilingual content        |
| 2026-01-14 | press_release | `kod-sumber-opengovportal-kini-terbuka`                  | OpenGovPortal source code is now open          |
| draft      | press_release | `pelan-hala-tuju-opengovportal-2027`                     | OpenGovPortal 2027 roadmap                     |

Valid `type` values: `press_release`, `announcement`, `news`.

---

## Achievements (`AchievementSeeder`)

16 rows keyed by `slug`: 15 published across 2023–2026 (so the year filter offers four years) and 1 draft. Five are
featured. Icons come from the eight shared SVGs under `/images/icons/achievements/`. Descriptions are one `<p>` each;
cards strip the tags.

| Date               | Slug                                     | Title (en)                               | Icon     | Featured |
|--------------------|------------------------------------------|------------------------------------------|----------|----------|
| 2026-08-11         | `audit-kebolehcapaian-wcag-2-1-aa-lulus` | WCAG 2.1 AA accessibility audit passed   | shield   | yes      |
| 2026-07-14         | `editor-kandungan-berbantu-ai`           | AI-assisted content editor launched      | rocket   |          |
| 2026-07-01         | `600-ujian-automatik`                    | 600 automated tests                      | chart    |          |
| 2026-05-19         | `sistem-tema-boleh-tukar`                | Switchable theme system                  | star     |          |
| 2026-03-24         | `kesemua-10-halaman-awam-siap`           | All 10 public pages live                 | trophy   | yes      |
| 2026-01-14         | `kod-sumber-dibuka-kepada-umum`          | Source code opened to the public         | globe    | yes      |
| 2025-11-18         | `panel-pentadbiran-lengkap`              | Admin panel complete                     | document |          |
| 2025-09-09         | `menu-mega-empat-peringkat`              | Four-level mega menu                     | star     |          |
| 2025-06-24         | `carian-teks-penuh-dwibahasa`            | Bilingual full-text search               | chart    |          |
| 2025-04-15         | `sokongan-lima-penyedia-storan-awan`     | Support for five cloud storage providers | globe    |          |
| 2025-01-21         | `dwibahasa-sejak-hari-pertama`           | Bilingual from day one                   | users    | yes      |
| 2024-10-08         | `prototaip-pertama-dipersembahkan`       | First prototype demonstrated             | rocket   |          |
| 2024-06-11         | `sistem-reka-bentuk-myds-diguna-pakai`   | MyDS design system adopted               | star     |          |
| 2024-02-20         | `pasukan-teras-ditubuhkan`               | Core team formed                         | users    |          |
| 2023-11-14         | `idea-opengovportal-dilahirkan`          | The idea for OpenGovPortal is born       | trophy   | yes      |
| draft (2026-12-01) | `pelancaran-versi-1-0`                   | Version 1.0 launch                       | rocket   |          |

---

## Policies (`PolicySeeder`)

11 rows keyed by `slug`: two published per category so every filter chip has results, plus one draft. `category` must be
one of `keselamatan`, `data`, `digital`, `ict`, `perkhidmatan` (the lang keys under `dasar.categories`). Published rows
link to a sample PDF at `/files/policies/<slug>.pdf`. `file_size` is read from disk at seed time but stored only when
the file is 50 KB or larger, because the card rounds to one decimal in MB and the one-page samples would show as "0.0
MB". Real documents pick up their size automatically. Descriptions are plain text.

| Category     | Slug                                    | Title (en)                          | Published      |
|--------------|-----------------------------------------|-------------------------------------|----------------|
| keselamatan  | `dasar-keselamatan-kandungan-dan-akaun` | Content and Account Security Policy | 2026-08-18     |
| keselamatan  | `garis-panduan-pengendalian-insiden`    | Incident Handling Guidelines        | 2026-02-17     |
| data         | `dasar-pengurusan-data-terbuka`         | Open Data Management Policy         | 2026-07-21     |
| data         | `garis-panduan-pengekalan-rekod`        | Records Retention Guidelines        | 2026-01-20     |
| digital      | `dasar-kebolehcapaian-digital`          | Digital Accessibility Policy        | 2026-06-23     |
| digital      | `garis-panduan-kandungan-dwibahasa`     | Bilingual Content Guidelines        | 2025-12-16     |
| ict          | `piawaian-pembangunan-tema`             | Theme Development Standards         | 2026-05-26     |
| ict          | `dasar-penggunaan-ai-dalam-kandungan`   | AI Use in Content Policy            | 2026-04-28     |
| perkhidmatan | `piagam-perkhidmatan-portal`            | Portal Service Charter              | 2026-03-31     |
| perkhidmatan | `garis-panduan-maklum-balas-awam`       | Public Feedback Guidelines          | 2025-11-18     |
| digital      | `dasar-tema-gelap`                      | Dark Theme Policy (Draft)           | draft, no file |

---

## Staff Directory (`StaffDirectorySeeder`)

15 rows keyed by `email`: 14 active (the listing paginates at 12, so page 2 exists) and 1 inactive. Five departments
with identical `department_ms` / `department_en` pairs per group so the department filter groups correctly in both
locales. Photos are `null` (the card shows the silhouette fallback) until portraits are supplied. Phones are
`+603-0000 1001` onwards in sort order.

| Department (ms / en)                                  | Staff (position ms / en)                                                                                                                                                                                    |
|-------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Pentadbiran Portal / Portal Administration            | Amirul Hakim bin Roslan (Pengarah Portal / Portal Director) · Chong Wei Lin (Timbalan Pengarah / Deputy Director) · Saraswathy a/p Muniandy (Pegawai Tadbir / Administrative Officer)                       |
| Kandungan & Editorial / Content & Editorial           | Farah Nadia binti Ismail (Ketua Editor / Head of Content) · Daniel Lim Jun Hao (Editor Kanan / Senior Editor) · Priya Nair (Penulis Kandungan / Content Writer)                                             |
| Teknologi & Platform / Technology & Platform          | Hafiz bin Kamaruddin (Ketua Kejuruteraan / Head of Engineering) · Tan Mei Xin (Jurutera Perisian Kanan / Senior Software Engineer) · Rajesh Kumar a/l Suppiah (Jurutera DevOps / DevOps Engineer)           |
| Reka Bentuk & Kebolehcapaian / Design & Accessibility | Aina Sofea binti Zulkifli (Ketua Reka Bentuk / Head of Design) · Marcus Wong Kah Wai (Pereka Pengalaman Pengguna / UX Designer) · Siti Aisyah binti Halim (Pakar Kebolehcapaian / Accessibility Specialist) |
| Sokongan & Latihan / Support & Training               | Kavitha a/p Raman (Ketua Sokongan / Head of Support) · Ahmad Firdaus bin Yusof (Pegawai Latihan / Training Officer) · Lee Wei Sheng (Pegawai Sokongan / Support Officer, **inactive**)                      |

Emails use the first or preferred name only (`amirul@opengovportal.example`, `weilin@…`) because the staff card
truncates anything longer than about 32 characters. Some rows carry a `division_*` pair (Unit Pentadbiran, Unit Siaran,
Unit Terjemahan, Unit Platform, Unit Infrastruktur, Unit Latihan).

---

## Head Of Organisation (`MinisterProfileSeeder`)

One current profile keyed by `name`. The page still uses the `profil-kementerian` route and the "Profil Kementerian /
Ministry Profile" title because those are template chrome; the card label reads "Ketua organisasi / Head of
organisation".

| Field               | Value                                                                                                                                  |
|---------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| name                | Nurul Hidayah binti Azman                                                                                                              |
| title_ms / title_en | Ketua Pegawai Digital, OpenGovPortal / Chief Digital Officer, OpenGovPortal                                                            |
| bio                 | Two `<p>` blocks: leads the team since January 2025; background in public-service digitisation and plain-language bilingual publishing |
| appointed_at        | 2025-01-06                                                                                                                             |
| photo               | `null` until a portrait is supplied (see sample-images.md)                                                                             |

---

## Addresses (`AddressSeeder`)

Keyed by `label_ms`. `google_maps_url` is `null` so the map link is hidden.

| Label (ms / en)                 | Address                                                      | Phone / Fax                     | Email                          |
|---------------------------------|--------------------------------------------------------------|---------------------------------|--------------------------------|
| Ibu Pejabat / Headquarters      | Aras 5, Menara Portal, Jalan Contoh 1, 50000 Bandar Contoh   | +603-0000 1000 / +603-0000 1001 | hello@opengovportal.example    |
| Pusat Sokongan / Support Centre | Blok B, Kompleks Contoh, Jalan Contoh 2, 50100 Bandar Contoh | +603-0000 2000                  | sokongan@opengovportal.example |

---

## Footer (`FooterSettingSeeder` and `MenuSeeder`)

Branding block (section `branding`, rebuilt each seed):

| Order | Type       | Value (ms / en)                                                |
|-------|------------|----------------------------------------------------------------|
| 1     | logo       | `/images/logo/opengovportal-footer.svg`, alt "OpenGovPortal"   |
| 2     | heading    | Portal kerajaan sumber terbuka / Open-source government portal |
| 3     | text       | Headquarters address, three lines                              |
| 4     | subheading | Ikuti kami / Follow us                                         |

Social icons (section `social`): GitHub → repository URL; Facebook, X and YouTube → `https://opengovportal.example/…`
placeholders. Only the seven icon names in `components/icons/social.blade.php` are valid.

Footer link columns (menu `public_footer`, rebuilt each seed; internal URLs are un-prefixed because the footer adds the
locale at render):

| Column (ms / en)             | Links                                                                                  |
|------------------------------|----------------------------------------------------------------------------------------|
| Mengenai Kami / About Us     | Profil OpenGovPortal → `/profil-kementerian` · Pencapaian · Direktori · Dasar · Siaran |
| Pautan Pantas / Quick Links  | Statistik · Carian · Hubungi Kami · Dokumentasi → repository `docs/` (new tab)         |
| Sumber Terbuka / Open Source | Repositori GitHub / GitHub repository · Laporkan isu / Report an issue (new tab)       |

The header menu and the admin sidebar are not part of the pack and are unchanged.

---

## Static Pages (`StaticPageSeeder`)

Two rows keyed by `slug`. The slugs `penafian` and `dasar-privasi` are hardwired in `routes/public.php` and must not
change. Both rows now carry `excerpt_*`, `meta_title_*` and `meta_desc_*`.

| Slug            | Sections (ms / en)                                                                                                                                                                                                                                  |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `penafian`      | Intro · Kandungan / Content · Pautan luar / External links · Pembantu AI / AI assistant · Ketersediaan / Availability · Had tanggungjawab / Limitation of liability · last updated 16 June 2026                                                     |
| `dasar-privasi` | Intro · Data yang dikumpul / Data collected · Tujuan penggunaan / How data is used · Tempoh simpanan / Retention · Kuki / Cookies · Perkongsian data / Sharing · Hak anda / Your rights (privasi@opengovportal.example) · last updated 16 June 2026 |

The retention periods in the privacy policy (feedback 24 months, IP addresses 30 days, AI conversations 90 days,
activity logs 12 months) are sample values; align them with `activity_log_retention_days` and the AI auto-purge setting
if those change.

---

## Admin-Only Rows

| Seeder                  | Rows                                                                                                                                                           | Notes                                                                     |
|-------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------|
| `CelebrationSeeder`     | Hari Kesedaran Kebolehcapaian Global 2026 (2026-05-21), Perjumpaan komuniti sumber terbuka 2026 (2026-05-05), draft Sambutan pelancaran versi 1.0 (2026-12-01) | No public route; `image` is `null`                                        |
| `SearchOverrideSeeder`  | `dasar` → `/ms/dasar` (100), `hubungi` → `/ms/hubungi-kami` (80), `pembantu ai` → AI beta broadcast (60)                                                       | Promoted results on `/carian`                                             |
| `MediaSeeder`           | `hero-01.svg`, `broadcast-01.svg`, `opengovportal-footer.svg`                                                                                                  | Registry entries for the asset pack with real width, height and byte size |
| `PolicyFileSeeder`      | Two public PDFs from the pack (`garis_panduan`, `piagam`) and one private report (`laporan`, no file on disk)                                                  | File registry                                                             |
| `FeedbackSeeder`        | One `new`, one `read`, one `replied` message from `@example.com` senders                                                                                       | Replied row is attributed to the super admin                              |
| `FeedbackSettingSeeder` | `is_enabled=true`, recipient `maklumbalas@opengovportal.example`, bilingual success messages                                                                   | Written through `FeedbackSetting::set()`                                  |

---

## Known Limitations

- `hero_banners.cta_url` and `quick_links.url` are single-locale columns and the views print them verbatim, so internal
  links are stored with `/ms`. English visitors who follow them land on the Bahasa Malaysia page. Making these
  locale-aware needs a view change and is out of scope for the content pack.
- The broadcast detail page emits `og:image` as the stored root-relative path. Social scrapers need an absolute URL;
  that is also a view change.
- `MenuSeeder` and `FooterSettingSeeder` replace footer rows on every seed. Do not run `db:seed` against a database
  whose footer was customised in the admin unless you intend to reset it.
- Bodies are hand-written HTML inside PHP strings. Use `&amp;` for ampersands inside HTML fields; plain-text fields
  (excerpts, positions, departments) take a literal `&`.

---

## Follow-Ups Outside The Content Pack

Found during the visual pass. Each needs a view, CSS or lang change rather than content, so they are recorded here and
left alone.

| Finding                                                                                                                                    | Where                                                                             | Suggested fix                                                                                                                                                                                                                     |
|--------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Rich-text HTML renders unstyled: no bullets on lists, no spacing between paragraphs or above `<h2>`                                        | broadcast body, profile mission list, Penafian and Dasar Privasi                  | The views apply `prose` classes but the Tailwind typography plugin is not loaded. Add `@plugin "@tailwindcss/typography"` to `resources/themes/default/css/app.css` (with the npm package) or write base styles for content HTML. |
| Pagination strings are English on Malay pages ("Showing 1 to 15 of 18 results", "Next »")                                                  | `/ms/siaran`, `/ms/direktori`                                                     | Publish and translate the pagination views, or add `lang/ms/pagination.php`.                                                                                                                                                      |
| Console error "Canvas is already in use" on the statistics page                                                                            | `resources/themes/default/views/statistik/index.blade.php`                        | `Alpine.data` already runs `init()`; the extra `x-init="init()"` creates every chart twice. Remove one.                                                                                                                           |
| Carousel arrows overlap the hero subtitle at 375 px, and the floating assistant button covers the second quick-link tile and chart legends | homepage, statistics                                                              | Move the arrows below the text on small screens; shrink the assistant button to an icon on mobile.                                                                                                                                |
| Page title "Profil Kementerian / Ministry Profile" while the card and footer say OpenGovPortal                                             | `lang/*/profil.php : title`, `common.nav.profil_kementerian`, header `MenuSeeder` | Kept as template chrome. Rename to "Profil Organisasi / Organisation Profile" if a neutral label is preferred; update the two title tests.                                                                                        |
| `/favicon.ico` and `/robots.txt` return HTTP 404 with the correct body on Herd                                                             | local nginx only                                                                  | Herd's `location = /favicon.ico` block hands the request to its fallback server, which sends the file with a 404 status. Production Caddy serves both with 200.                                                                   |

---

## Related Docs

- [sample-images.md](sample-images.md) — image slot spec, asset pack and photo request list
- [pages-features.md](pages-features.md) — which model feeds which page section
- [database-schema.md](database-schema.md) — column definitions the rows must satisfy
- [agentic-coding.md](agentic-coding.md) — seeder and validation conventions
