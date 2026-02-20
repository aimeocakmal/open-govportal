# Pages & Features Inventory

Full inventory of all pages, features, and components in the kd-portal (digital.gov.my), mapped to their Laravel equivalents.

Source: https://github.com/govtechmy/kd-portal

---

## Implementation Status Convention

Use this document as a parity inventory, not as implementation proof.

- `Planned` — documented target behavior not yet built.
- `Implemented` — behavior exists in code and has validation evidence (test name or manual check recorded).
- `Deferred` — intentionally postponed; reason and tracking reference noted.

When updating a page section, include one of these status labels explicitly in **bold** next to the section heading. Never remove a status; change it from `Planned` → `Implemented` only after running validation.

---

## Resolved Implementation Decisions

These decisions are final. Do not reopen them without a documented reason.

| Topic | Decision | Rationale |
|-------|----------|-----------|
| **Frontend approach** | **Full TALL stack** (Tailwind + Alpine.js + Laravel + **Livewire**) | Filament 5.x already bundles Livewire 4; server-side components are simpler and more maintainable than Alpine+fetch |
| Direktori live search | **Livewire component `DirectoriSearch`** | Real-time server-side filtering; clean validation; no manual fetch() calls |
| Contact form | **Livewire component `ContactForm`** | Native `$errors`, `wire:model`, `wire:submit`; no manual JS form handling |
| Site search | **Livewire component `SearchResults`** | Server-side FTS query; debounced `wire:model.live` |
| Siaran type filter | **Livewire component `SiaranList`** | Replaces URL query param approach; filter state in component |
| Pencapaian year filter | **Livewire component `PencapaianList`** | Same pattern as SiaranList |
| Statistics charts | **Chart.js** (not ApexCharts) | Lighter bundle; sufficient for bar/line/pie; MIT license |
| Hero carousel | **Alpine.js + Embla.js (vanilla)** | UI-only: no server state; Alpine for init, Embla for scroll logic |
| Alpine.js scope | **Micro-interactions only**: mobile menu toggle, dropdown open/close, modal show/hide, carousel init | Alpine must NOT make server requests or manage application data |
| Static pages (Penafian, Dasar Privasi) | **`settings` table via Filament** (not hardcoded Blade) | Allows non-dev edits; consistent with other CMS content |
| i18n storage | **`lang/ms/` and `lang/en/` PHP arrays** | Native Laravel; no extra package; aligns with `App::setLocale()` |

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

Locale detection order:
1. `{locale}` URL segment
2. Browser `Accept-Language` header (for root `/` redirect only)
3. Default: `ms`

---

## Pages

### 1. Homepage (`/`) — **Status: Planned**

**Laravel route:** `GET /{locale}` → `HomeController@index`
**Source component directory:** `src/components/home/`
**Payload globals used:** `Homepage`, `SiteInfo`, `Header`
**Payload collections used:** `Achievement` (latest 7), `Broadcast` (latest 6), `HeroBanner`, `QuickLink`

**Sections:**

| Section | Data Source | Query | Notes |
|---------|------------|-------|-------|
| Hero Banner | `hero_banners` | `where is_active=true, order sort_order asc` | Alpine.js + Embla.js carousel |
| Quick Links | `quick_links` | `where is_active=true, order sort_order asc` | Icon + label grid |
| Latest Broadcasts | `broadcasts` | `where status=published, order published_at desc, limit 6` | Cards with image + excerpt |
| Achievements Highlights | `achievements` | `where status=published AND type != not_achievement, order date desc, limit 7` | Card or timeline layout |
| Feedback Widget | `feedback_settings` | key-value lookup | Optional; shown if `is_enabled=true` |

**Cache:** Full-page Redis cache, tag `homepage`, TTL 1 hour. Invalidate on any `HeroBanner`, `QuickLink`, `Broadcast`, or `Achievement` save.

**Blade views:**
- `resources/views/home/index.blade.php`
- `resources/views/components/home/hero-banner.blade.php`
- `resources/views/components/home/quick-links.blade.php`
- `resources/views/components/home/broadcast-card.blade.php`
- `resources/views/components/home/achievement-card.blade.php`

---

### 2. Siaran (`/siaran`) — **Status: Planned**

**Laravel routes:**
- `GET /{locale}/siaran` → `BroadcastController@index`
- `GET /{locale}/siaran/{slug}` → `BroadcastController@show`

**Source component directory:** `src/components/siaran/`
**Payload collection:** `Broadcast`

**Listing page features:**
- Pagination (15 per page) — managed by Livewire
- Filter by `type`: `press_release | announcement | news` — Livewire `wire:model` on select
- Cards: featured image, title, excerpt, date, type badge

**Implementation:**
- `BroadcastController@index` renders `siaran/index.blade.php` which embeds `<livewire:siaran-list />`
- `App\Livewire\SiaranList` — handles type filter + pagination
- `BroadcastController@show` renders `siaran/show.blade.php` directly (no Livewire; static detail page)

**Detail page features:**
- Full rich text content (rendered HTML from `content_ms` or `content_en`)
- Featured image (from S3)
- Related broadcasts (same type, latest 3, excluding current)
- Breadcrumb: Home → Siaran → {title}
- SEO: `<title>`, `<meta description>`, Open Graph, canonical URL

**Cache:**
- Listing: page-level cache NOT applied (Livewire manages state); query results cached with tag `broadcasts`, TTL 10 min
- Detail: full-page Redis cache, tag `broadcast:{slug}`, TTL 2 hours
- Invalidate `broadcasts` tag on any Broadcast publish/update/delete

**Blade views:**
- `resources/views/siaran/index.blade.php` (embeds Livewire component)
- `resources/views/siaran/show.blade.php`
- `resources/views/livewire/siaran-list.blade.php`
- `resources/views/components/siaran/broadcast-card.blade.php`

**Search:** Indexed in `searchable_content`, priority 20.

---

### 3. Pencapaian (`/pencapaian`) — **Status: Planned**

**Laravel routes:**
- `GET /{locale}/pencapaian` → `AchievementController@index`
- `GET /{locale}/pencapaian/{slug}` → `AchievementController@show`

**Source component directory:** `src/components/pencapaian/`
**Payload collection:** `Achievement`

**Listing page features:**
- Year filter — Livewire `wire:model` on year select dropdown; filter applied server-side
- Cards: icon, title, description, date
- Excludes `achievements` where `type = not_achievement`

**Implementation:**
- `AchievementController@index` renders `pencapaian/index.blade.php` which embeds `<livewire:pencapaian-list />`
- `App\Livewire\PencapaianList` — holds `$year` property, filters query on update
- `AchievementController@show` renders `pencapaian/show.blade.php` directly

**Detail page features:**
- Full description content, date, icon
- Breadcrumb: Home → Pencapaian → {title}

**Cache:** Detail pages cached with tag `achievement:{slug}`, TTL 2 hours. Listing not page-cached (Livewire).

**Blade views:**
- `resources/views/pencapaian/index.blade.php`
- `resources/views/pencapaian/show.blade.php`
- `resources/views/livewire/pencapaian-list.blade.php`
- `resources/views/components/pencapaian/achievement-card.blade.php`

**Search:** Indexed in `searchable_content`, priority 10 (highest).

---

### 4. Statistik (`/statistik`) — **Status: Planned**

**Laravel route:** `GET /{locale}/statistik` → `StatistikController@index`
**Source component directory:** `src/components/statistik/`
**Frontend library:** Chart.js (replaces kd-portal's Recharts)

**Features:**
- Bar charts, line charts, pie charts for ministry KPIs and digital statistics
- Statistics managed from Filament (dedicated `Statistik` content type or JSON in `settings`)
- Interactive chart legends and tooltips
- Responsive canvas layout

**Implementation:**
- Controller passes chart datasets as JSON to the Blade view
- Alpine.js component initialises Chart.js on `x-init`
- No server-side chart rendering

**Cache:** Tag `statistik`, TTL 6 hours (data changes infrequently).

**Blade views:**
- `resources/views/statistik/index.blade.php`
- `resources/views/components/statistik/chart.blade.php`

---

### 5. Direktori (`/direktori`) — **Status: Planned**

**Laravel route:** `GET /{locale}/direktori` → `DirectoriController@index`
**Source component directory:** `src/components/direktori/`
**Payload collection:** `Directory` → Model: `StaffDirectory`

**Features:**
- Staff listing grid/list
- Live search by name, position, or department — **Livewire** `wire:model.live.debounce.400ms` on text input
- Filter by department/division — **Livewire** `wire:model` on select dropdown
- Staff cards: photo, name, position, department, email, phone

**Implementation:**
- `DirectoriController@index` renders `direktori/index.blade.php` which embeds `<livewire:direktori-search />`
- `App\Livewire\DirectoriSearch` — holds `$query` (string) and `$jabatan` (department filter); updates results on property change
- No separate API endpoint needed — Livewire handles all server communication

**Cache:** Livewire component queries are not page-cached. Raw staff list cached in query cache, tag `direktori`, TTL 4 hours. Invalidate on any StaffDirectory save.

**Blade views:**
- `resources/views/direktori/index.blade.php`
- `resources/views/livewire/direktori-search.blade.php`
- `resources/views/components/direktori/staff-card.blade.php`

**Search:** Indexed in `searchable_content`, priority 30.

---

### 6. Dasar (`/dasar`) — **Status: Planned**

**Laravel routes:**
- `GET /{locale}/dasar` → `DasarController@index`
- `GET /{locale}/dasar/{id}/muat-turun` → `DasarController@download`

**Source component directory:** `src/components/dasar/`
**Payload collection:** `Policy`

**Features:**
- Policy document listing
- Filter by `category` via `?kategori=` query param
- Each item: title, description, category badge, download button
- Download action: increments `download_count`, redirects to signed S3 URL (10-minute expiry)

**Cache:** Tag `policies`, TTL 2 hours. Download route bypasses cache.

**Blade views:**
- `resources/views/dasar/index.blade.php`
- `resources/views/components/dasar/policy-card.blade.php`

**Search:** Indexed in `searchable_content`, priority 40.

---

### 7. Profil Kementerian (`/profil-kementerian`) — **Status: Planned**

**Laravel route:** `GET /{locale}/profil-kementerian` → `ProfilKementerianController@index`
**Payload global:** `MinisterProfile`

**Features:**
- Current minister: photo, name, title, bio (from `minister_profiles` table, `where is_current=true`)
- Ministry vision, mission, objectives (from `settings` table)
- Ministry organisational structure (static image or editable from Filament)

**Cache:** Tag `profil-kementerian`, TTL 24 hours. Invalidate on MinisterProfile save.

**Blade views:**
- `resources/views/profil-kementerian/index.blade.php`
- `resources/views/components/profil/minister-card.blade.php`

---

### 8. Hubungi Kami (`/hubungi-kami`) — **Status: Planned**

**Laravel route:** `GET /{locale}/hubungi-kami` → `HubungiKamiController@index` (renders page shell + addresses)

No separate POST route needed — form submission handled by Livewire.

**Payload globals:** `Addresses`, `FeedbackSettings`

**Features:**
- Ministry addresses from `addresses` table — rendered in Blade (static, no Livewire)
- Contact form — **Livewire component `ContactForm`**
  - Fields: name (required), email (required, valid), subject (required), message (required, min:20)
  - On valid submit: store to `feedbacks` table, dispatch `SendFeedbackEmail` job, show success state
  - Inline validation feedback with `wire:model.blur` on each field
  - Rate-limited: 5 submissions per IP per hour (using Livewire's `#[RateLimit]` or middleware)

**Validation (inside Livewire component):**
```php
protected $rules = [
    'name'    => 'required|string|max:255',
    'email'   => 'required|email|max:255',
    'subject' => 'required|string|max:500',
    'message' => 'required|string|min:20|max:5000',
];
```

**Cache:** Addresses section cached with tag `hubungi-kami`, TTL 24 hours. Livewire form bypasses page cache.

**Blade views:**
- `resources/views/hubungi-kami/index.blade.php`
- `resources/views/livewire/contact-form.blade.php`

---

### 9. Penafian (`/penafian`) — **Status: Planned**

**Laravel route:** `GET /{locale}/penafian` → `StaticPageController@penafian`
**Source:** Content from `settings` table, keys `disclaimer_ms` and `disclaimer_en`.

**Implementation note:** Content editable in Filament's `ManageSiteInfo` settings page. Falls back to `lang/{locale}/penafian.php` if settings keys are empty.

**Cache:** Tag `static-pages`, TTL 24 hours.

**Blade view:** `resources/views/static/penafian.blade.php`

---

### 10. Dasar Privasi (`/dasar-privasi`) — **Status: Planned**

**Laravel route:** `GET /{locale}/dasar-privasi` → `StaticPageController@dasarPrivasi`
**Source:** Content from `settings` table, keys `privacy_policy_ms` and `privacy_policy_en`.

**Cache:** Tag `static-pages`, TTL 24 hours.

**Blade view:** `resources/views/static/dasar-privasi.blade.php`

---

## Global Components

### Navigation / Header — **Status: Planned**

**Payload global:** `Header`
**Blade component:** `resources/views/components/layout/navbar.blade.php`

**Elements:**
- Ministry logo (from `settings.site_logo`)
- Main navigation links (from `navigation_items` table, top-level items ordered by `sort_order`)
- Dropdown for items with `parent_id` children
- Language switcher: `ms` / `en` (preserves current path, swaps locale segment)
- Mobile hamburger menu (Alpine.js toggle)
- Search icon → opens search overlay

**Cache:** Navigation data cached with tag `navigation`, TTL 24 hours. Invalidate on `navigation_items` save.

---

### Footer — **Status: Planned**

**Payload global:** `Footer`
**Blade component:** `resources/views/components/layout/footer.blade.php`

**Elements:**
- Ministry logo + name (from `settings`)
- Footer links by section (from `footer_settings` table, grouped by `section` column)
- Social media icons: Facebook, Twitter/X, Instagram, YouTube (URLs from `settings`)
- Copyright notice with current year

**Cache:** Tag `footer`, TTL 24 hours. Invalidate on `footer_settings` or `settings` save.

---

### Theme Switcher — **Status: Planned**

**Blade component:** `resources/views/components/layout/theme-switcher.blade.php`

**Elements:**
- One button per available theme (list from `config/themes.php valid_themes`)
- Active theme highlighted with a ring indicator
- Clicking a button: sets `data-theme` on `<html>`, writes `govportal_theme` cookie (1 year)
- Initial state read from `data-theme` attribute (set server-side by `ApplyTheme` middleware)

**Implementation:**
- Pure Alpine.js micro-interaction — no server call needed
- `ApplyTheme` middleware reads `govportal_theme` cookie → falls back to `settings.site_default_theme` → shares `$currentTheme` with all views
- Base layout: `<html data-theme="{{ $currentTheme }}">`
- Placed in nav or footer — exact placement determined during Week 2 build

**Cache:** No cache invalidation needed — theme is per-user (cookie). Admin `site_default_theme` change invalidates `navigation` tag (covers layout re-render).

---

### Search (`/carian`) — **Status: Planned**

**Payload plugin:** `@payloadcms/plugin-search`
**Laravel route:** `GET /{locale}/carian` → `SearchController@index` (renders page shell)

**Implementation:** **Livewire component `SearchResults`**
- `wire:model.live.debounce.500ms` on search input
- Results update as user types (after 500ms debounce)
- Displays results grouped or flat, sorted by priority then relevance

**Engine:** PostgreSQL FTS via `searchable_content` table.

**Results ranked by:**
1. `SearchOverride` priority match (exact keyword in `search_overrides.query`)
2. FTS relevance score (`ts_rank`)

**Collections searched:** `broadcasts` (p20), `achievements` (p10), `staff_directories` (p30), `policies` (p40).

**Blade views:**
- `resources/views/carian/index.blade.php`
- `resources/views/livewire/search-results.blade.php`

---

## Admin Panel (Filament) — **Status: Planned**

Replaces Payload CMS admin at `/admin`.

### Filament Resources

| Resource | Model | Payload Collection | Status |
|----------|-------|--------------------|--------|
| `BroadcastResource` | `Broadcast` | Broadcast | Planned |
| `AchievementResource` | `Achievement` | Achievement | Planned |
| `CelebrationResource` | `Celebration` | Celebration | Planned |
| `StaffDirectoryResource` | `StaffDirectory` | Directory | Planned |
| `PolicyResource` | `Policy` | Policy | Planned |
| `FileResource` | `File` | File | Planned |
| `HeroBannerResource` | `HeroBanner` | HeroBanner | Planned |
| `QuickLinkResource` | `QuickLink` | QuickLink | Planned |
| `MediaResource` | `Media` | Media | Planned |
| `FeedbackResource` | `Feedback` | Feedback (read-only) | Planned |
| `SearchOverrideResource` | `SearchOverride` | Search-Overrides | Planned |
| `UserResource` | `User` | Users | Planned |

### Filament Settings Pages (Globals)

| Class | Replaces Payload Global | Status |
|-------|------------------------|--------|
| `ManageSiteInfo` | SiteInfo | Planned |
| `ManageHeader` | Header (navigation items) | Planned |
| `ManageFooter` | Footer | Planned |
| `ManageHomepage` | Homepage | Planned |
| `ManageMinisterProfile` | MinisterProfile | Planned |
| `ManageAddresses` | Addresses | Planned |
| `ManageFeedbackSettings` | FeedbackSettings | Planned |

---

## Component Library

### Livewire Components (`app/Livewire/`)

Server-side reactive components. Use for anything that involves data from the database.

| Component Class | View | Used On |
|----------------|------|---------|
| `SiaranList` | `livewire/siaran-list.blade.php` | `/siaran` |
| `PencapaianList` | `livewire/pencapaian-list.blade.php` | `/pencapaian` |
| `DirectoriSearch` | `livewire/direktori-search.blade.php` | `/direktori` |
| `ContactForm` | `livewire/contact-form.blade.php` | `/hubungi-kami` |
| `SearchResults` | `livewire/search-results.blade.php` | `/carian` |

### Alpine.js (micro-interactions only — no server calls)

| Interaction | Pattern |
|------------|---------|
| Mobile menu toggle | `x-data="{ open: false }"` + `x-show` |
| Dropdown navigation | `x-data="{ open: false }"` + `@click.away="open = false"` |
| Modal/dialog | `x-data="{ show: false }"` + `x-show` + `x-transition` |
| Accordion (FAQ) | `x-data="{ expanded: null }"` + `x-show` |
| Hero carousel | `x-init="initEmbla($el)"` — delegates to Embla.js |
| Chart.js init | `x-init="new Chart($el, config)"` — delegates to Chart.js |

### Mapped from kd-portal (React + Radix UI → TALL equivalent)

| kd-portal | TALL equivalent | Layer |
|-----------|----------------|-------|
| `@radix-ui/react-accordion` | Alpine.js `x-show` + `x-transition` | Alpine |
| `@radix-ui/react-dialog` | Alpine.js modal pattern | Alpine |
| `@radix-ui/react-navigation-menu` | Alpine.js dropdown nav | Alpine |
| `@radix-ui/react-popover` | Alpine.js popover | Alpine |
| `@radix-ui/react-select` | Alpine.js custom select | Alpine |
| `embla-carousel-react` | Alpine.js `x-init` + Embla.js (vanilla) | Alpine |
| `cmdk` (command palette search) | Livewire `SearchResults` component | Livewire |
| `recharts` | Chart.js + Alpine.js `x-init` | Alpine (Chart.js) |
| `react-day-picker` | Flatpickr | Alpine `x-init` |
| `@tanstack/react-table` | Blade table + `SiaranList` / `DirectoriSearch` | Livewire |

---

## Internationalisation

**kd-portal:** `next-intl` with message files in `messages/` directory.

**Laravel equivalent:** Native `lang/` PHP arrays + `App::setLocale()`.

```
lang/
  ms/
    common.php       ← site name, nav labels, buttons
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
    validation.php   ← form error messages in BM
  en/
    (same files)
```

**SetLocale middleware:** Reads `{locale}` from route parameter, validates against `['ms', 'en']`, calls `App::setLocale()`. Registered in `web` middleware group, applied to all `/{locale}/` prefixed routes.

---

## Email

**kd-portal:** `@aws-sdk/client-ses`
**Laravel:** `MAIL_MAILER=ses` — Laravel's built-in SES driver (uses `aws/aws-sdk-php` under the hood).

**Triggered by:**
- `POST /hubungi-kami` (contact form submission)
- `POST /hubungi-kami` (feedback submission if feedback widget enabled)

---

## File Storage

**kd-portal:** `@payloadcms/storage-s3`
**Laravel:** `config/filesystems.php`, disk `s3`.

Collections using S3 storage:
- `media` — general images/videos
- `files` — downloadable policy documents and attachments
- `hero_banners` — carousel images

**Required `.env` variables:**
```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=
```

---

## Cache Tag → Route / Model Mapping

When a model is saved, these cache tags must be flushed:

| Model saved | Tags to flush |
|-------------|--------------|
| `HeroBanner` | `homepage` |
| `QuickLink` | `homepage` |
| `Broadcast` | `homepage`, `broadcasts`, `broadcast:{slug}` |
| `Achievement` | `homepage`, `achievements` |
| `Celebration` | `celebrations` |
| `StaffDirectory` | `direktori` |
| `Policy` | `policies` |
| `NavigationItem` | `navigation` |
| `FooterSetting` | `footer` |
| `Setting` (site info) | `navigation`, `footer`, `profil-kementerian`, `static-pages` |
| `MinisterProfile` | `profil-kementerian` |
| `Address` | `hubungi-kami` |
| `Setting` (`site_default_theme` key) | `navigation` |

Implement invalidation via a `CacheObserver` registered on each model in `AppServiceProvider`.

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
