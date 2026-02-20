# Pages & Features Inventory

Full inventory of all pages, features, and components in the kd-portal (digital.gov.my), mapped to their Laravel equivalents.

Source: https://github.com/govtechmy/kd-portal

---

## Implementation Status Convention

Use this document as a parity inventory, not as implementation proof.

- `Planned`: documented target behavior not yet built.
- `Implemented`: behavior exists in code and has validation evidence.
- `Deferred`: intentionally postponed and tracked in the timeline.

When updating a page section, include one of these status labels explicitly.

---

## URL Structure

All public pages are locale-prefixed:

```
/{locale}/{page}

Examples:
  /ms/          → Homepage (Bahasa Malaysia)
  /en/          → Homepage (English)
  /ms/siaran    → Broadcasts (BM)
  /en/siaran    → Broadcasts (EN)
```

**Supported locales:**
- `ms` → ms-MY (Bahasa Malaysia) — Default
- `en` → en-GB (English)

---

## Pages

### 1. Homepage (`/`)

**Source component directory:** `src/components/home/`
**Payload globals used:** `Homepage`, `SiteInfo`, `Header`
**Payload collections used:** `Achievement` (latest 7), `Broadcast` (latest 6), `HeroBanner`, `QuickLink`

**Sections:**
| Section | Data Source | Notes |
|---------|------------|-------|
| Hero Banner | `HeroBanner` collection | Embla Carousel, multiple slides |
| Quick Links | `QuickLink` collection | Grid of icon + label links |
| Latest Broadcasts | `Broadcast` collection (latest 6, non-draft) | Cards with image + excerpt |
| Achievements Highlights | `Achievement` collection (latest 7, sorted by date, not `not_achievement` type) | Timeline or card layout |
| Feedback Widget | `FeedbackSettings` global | Optional rating/comment widget |

**Laravel implementation:**
- Route: `GET /` and `GET /{locale}/` → `HomeController@index`
- Cache: Full-page Redis cache, TTL 1 hour
- Alpine.js: Embla Carousel for hero banner

---

### 2. Siaran (`/siaran`)

**Source component directory:** `src/components/siaran/`
**Payload collection:** `Broadcast`

**Sub-pages:**
- `/siaran` — Listing page with pagination
- `/siaran/{slug}` — Detail page

**Features:**
- Listing: Pagination, filter by type (press release / news / announcement)
- Cards: Featured image, title, excerpt, date, type badge
- Detail: Full rich text content, featured image, related broadcasts
- SEO: Canonical URL, Open Graph meta per article
- Search: Indexed in Payload search plugin (priority: 20)

**Laravel implementation:**
- Routes: `GET /{locale}/siaran` → `BroadcastController@index`
- Routes: `GET /{locale}/siaran/{slug}` → `BroadcastController@show`
- View cache: 1 hour for listing, 2 hours for detail

---

### 3. Pencapaian (`/pencapaian`)

**Source component directory:** `src/components/pencapaian/`
**Payload collection:** `Achievement`

**Sub-pages:**
- `/pencapaian` — Listing with year filter
- `/pencapaian/{slug}` — Detail page (if applicable)

**Features:**
- Listing: Sort/filter by year
- Cards: Icon, title, description, date
- Excludes items of type `not_achievement`
- Search: Indexed (priority: 10 — highest)

**Laravel implementation:**
- Routes: `GET /{locale}/pencapaian` → `AchievementController@index`
- Year filter via query string: `/pencapaian?tahun=2024`

---

### 4. Statistik (`/statistik`)

**Source component directory:** `src/components/statistik/`
**Frontend library:** Recharts (React charting library)

**Features:**
- Data visualisations (bar charts, line charts, pie charts)
- Statistics managed from CMS (custom statistics content type or settings)
- Interactive chart legends and tooltips
- Responsive chart layouts

**Laravel implementation:**
- Route: `GET /{locale}/statistik` → `StatistikController@index`
- Replace Recharts with **Chart.js** (via CDN or npm) rendered via Alpine.js
- Data passed from controller as JSON to Alpine.js component

---

### 5. Direktori (`/direktori`)

**Source component directory:** `src/components/direktori/`
**Payload collection:** `Directory`

**Features:**
- Staff listing with search by name, department, division
- Staff cards: photo, name, position, department, email, phone
- Filter by department/division
- Search indexed (priority: 30)

**Laravel implementation:**
- Route: `GET /{locale}/direktori` → `DirectoriController@index`
- Live search via Livewire or Alpine.js + Axios
- Search: PostgreSQL FTS on name, position, department fields

---

### 6. Dasar (`/dasar`)

**Source component directory:** `src/components/dasar/`
**Payload collection:** `Policy`

**Features:**
- Policy document listing
- Filter by category
- Download links to PDF files (S3)
- Download count tracking
- Search indexed (priority: 40)

**Laravel implementation:**
- Route: `GET /{locale}/dasar` → `DasarController@index`
- Download: `GET /{locale}/dasar/{id}/muat-turun` → increment download_count, redirect to S3

---

### 7. Profil Kementerian (`/profil-kementerian`)

**Payload global:** `MinisterProfile`

**Features:**
- Ministry overview (vision, mission, objectives)
- Current minister profile (photo, name, bio)
- Ministry organisational structure
- Historical ministers list (optional)

**Laravel implementation:**
- Route: `GET /{locale}/profil-kementerian` → `ProfilKementerianController@index`
- Static-ish content from `minister_profiles` table and `settings`
- High cache TTL (24 hours)

---

### 8. Hubungi Kami (`/hubungi-kami`)

**Payload global:** `Addresses`, `FeedbackSettings`

**Features:**
- Ministry addresses (multiple office locations)
- Contact form (name, email, subject, message)
- Form submission → stored in `feedbacks` table
- Email notification on submission (AWS SES — matching kd-portal's `@aws-sdk/client-ses`)
- Google Maps embed links (optional)
- Operating hours

**Laravel implementation:**
- Routes:
  - `GET /{locale}/hubungi-kami` → `HubungiKamiController@index`
  - `POST /{locale}/hubungi-kami` → `HubungiKamiController@submit`
- Mail: `config/mail.php` using SES driver
- Validation: Name (required), Email (required, valid), Message (required, min:20)
- CSRF protection on form

---

### 9. Penafian (`/penafian`)

Static page — Disclaimer.

**Features:**
- Static text content
- Managed from CMS settings or static Blade view

**Laravel implementation:**
- Route: `GET /{locale}/penafian` → returns `penafian.blade.php`
- Content stored in `settings` table (key: `disclaimer_ms`, `disclaimer_en`)
- Or: static Blade view with `@lang()` strings

---

### 10. Dasar Privasi (`/dasar-privasi`)

Static page — Privacy Policy.

**Features:**
- Static legal text
- Managed from CMS or static Blade view

**Laravel implementation:**
- Route: `GET /{locale}/dasar-privasi` → returns `dasar-privasi.blade.php`
- Same approach as Penafian

---

## Global Components

### Navigation / Header

**Payload global:** `Header`
**kd-portal component:** `src/components/layout/`

**Elements:**
- Ministry logo
- Main navigation links (dynamic from `navigation_items` table)
- Language switcher (ms / en)
- Mobile hamburger menu
- Search icon/button

**Blade component:** `resources/views/components/layout/navbar.blade.php`

### Footer

**Payload global:** `Footer`

**Elements:**
- Ministry logo + name
- Navigation links by section (quick links, legal, social)
- Copyright notice
- Social media icons (Facebook, Twitter, Instagram, YouTube)

**Blade component:** `resources/views/components/layout/footer.blade.php`

### Search

**Payload plugin:** `@payloadcms/plugin-search` (indexes Broadcast, Achievement, Directory, Policy)

**Laravel implementation:**
- Route: `GET /{locale}/carian?q=keyword` → `SearchController@index`
- Engine: PostgreSQL FTS via `searchable_content` table
- Results ranked by: Search-Override priority, then relevance score
- Collections searched: `broadcasts`, `achievements`, `staff_directories`, `policies`

---

## Admin Panel (Filament)

Replaces Payload CMS admin at `/admin`.

### Filament Resources (one per collection)

| Resource | Model | Payload Collection |
|----------|-------|-------------------|
| `BroadcastResource` | `Broadcast` | Broadcast |
| `AchievementResource` | `Achievement` | Achievement |
| `CelebrationResource` | `Celebration` | Celebration |
| `StaffDirectoryResource` | `StaffDirectory` | Directory |
| `PolicyResource` | `Policy` | Policy |
| `FileResource` | `File` | File |
| `HeroBannerResource` | `HeroBanner` | HeroBanner |
| `QuickLinkResource` | `QuickLink` | QuickLink |
| `MediaResource` | `Media` | Media |
| `FeedbackResource` | `Feedback` | Feedback (read-only) |
| `SearchOverrideResource` | `SearchOverride` | Search-Overrides |
| `UserResource` | `User` | Users |

### Filament Pages (for Globals)

| Page | Replaces Payload Global |
|------|------------------------|
| `ManageSiteInfo` | SiteInfo |
| `ManageHeader` | Header (navigation items) |
| `ManageFooter` | Footer |
| `ManageHomepage` | Homepage |
| `ManageMinisterProfile` | MinisterProfile |
| `ManageAddresses` | Addresses |
| `ManageFeedbackSettings` | FeedbackSettings |

---

## Component Library (Blade)

Mapped from kd-portal's React + Radix UI components:

| kd-portal (React/Radix) | Laravel Blade equivalent |
|------------------------|--------------------------|
| `@radix-ui/react-accordion` | Alpine.js accordion |
| `@radix-ui/react-dialog` | Alpine.js modal |
| `@radix-ui/react-navigation-menu` | Alpine.js dropdown nav |
| `@radix-ui/react-popover` | Alpine.js popover |
| `@radix-ui/react-select` | Alpine.js custom select |
| `embla-carousel-react` | Alpine.js + Embla.js (vanilla) |
| `cmdk` (command palette) | Alpine.js search overlay |
| `recharts` | Chart.js + Alpine.js |
| `react-day-picker` | Flatpickr |
| `@tanstack/react-table` | Blade table + Alpine.js sorting |

---

## Internationalisation

**kd-portal:** `next-intl` with message files in `messages/` directory.

**Laravel equivalent:**
```
lang/
  ms/
    common.php
    home.php
    siaran.php
    pencapaian.php
    statistik.php
    direktori.php
    dasar.php
    profil-kementerian.php
    hubungi-kami.php
    penafian.php
    dasar-privasi.php
    validation.php
  en/
    common.php
    home.php
    ... (same structure)
```

**Route middleware:** `SetLocale` middleware reads `{locale}` segment, calls `App::setLocale($locale)`.

**Locale detection:**
1. URL segment (`/ms/...` or `/en/...`)
2. Browser `Accept-Language` header (fallback for `/` redirect)
3. Default: `ms`

---

## Email System

**kd-portal uses:** `@aws-sdk/client-ses` (AWS Simple Email Service)

**Laravel equivalent:** `MAIL_MAILER=ses` in `.env`, using Laravel's built-in AWS SES mail driver.

**Triggered by:**
- Contact form submission (Hubungi Kami)
- Feedback submission
- (Optional) New broadcast notification

---

## File Storage

**kd-portal:** `@payloadcms/storage-s3` (AWS S3 for all media, hero banners, files)

**Laravel equivalent:** `config/filesystems.php` with `s3` disk.

Collections using S3:
- `media` — general images/videos
- `file` — downloadable documents
- `hero_banner` — homepage carousel images

**Environment variables** (matching kd-portal):
```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=
```

---

## Performance Targets

| Metric | Target | Strategy |
|--------|--------|---------|
| First Contentful Paint | < 1s | CDN + full-page Redis cache |
| Concurrent users | 10,000+ | Octane (Swoole) |
| Cache hit rate (CDN) | > 90% | Cloudflare page rules |
| Cache hit rate (Redis) | > 85% | 1-hour TTL, tag invalidation |
| Lighthouse Performance | 90+ | Image optimisation, lazy load |
| Uptime | 99.9% | Load balancer + health checks |
