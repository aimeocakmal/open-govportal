# OpenGovPortal: Digital Gov Malaysia — Laravel Conversion Plan

## Executive Summary

**Source:** https://github.com/govtechmy/kd-portal (Next.js 15 + Payload CMS + MongoDB)
**Target:** OpenGovPortal (Laravel 12 + Octane + PostgreSQL)
**Goal:** Full recreation of https://www.digital.gov.my/ using the Laravel stack

---

## Agentic Delivery Rules

To execute this plan safely with coding agents, each weekly task should be split into ticket-sized slices using this template:

- `Slice`: one endpoint, one model, one migration, or one UI component.
- `Inputs`: source references from kd-portal and target files in this repo.
- `Output`: expected code artifacts (files changed/created).
- `Validation`: exact commands and manual checks.
- `Done`: objective passes and related docs are updated.

Recommended sequence inside each week:

1. Data model and migrations
2. Admin (Filament) resource
3. Public route/controller/view
4. Caching and invalidation
5. Tests and docs update

---

## Source System Analysis (kd-portal)

### Tech Stack

| Component | Technology | Version |
|-----------|------------|---------|
| **Framework** | Next.js | 15.1.11 |
| **CMS** | Payload CMS | latest |
| **Database** | MongoDB | via mongooseAdapter |
| **Language** | TypeScript | 5.5.2 |
| **Styling** | Tailwind CSS | 3.4.17 |
| **i18n** | next-intl | 3.26.3 |
| **Storage** | AWS S3 | via @payloadcms/storage-s3 |
| **Deployment** | Docker + Kubernetes | docker-compose + .kube/ |
| **Components** | Radix UI | various |

### Payload CMS Collections (Data Models)

These are the content types managed in kd-portal's Payload CMS. Each maps to a Laravel model:

| Payload Collection | Purpose | Laravel Model |
|-------------------|---------|---------------|
| `Achievement` | Ministry achievements with dates | `Achievement` |
| `Broadcast` | News/press releases/announcements | `Broadcast` |
| `Celebration` | Special events and celebrations | `Celebration` |
| `Directory` | Staff directory entries | `StaffDirectory` |
| `Feedback` | User-submitted feedback | `Feedback` |
| `File` | Downloadable file management | `File` |
| `HeroBanner` | Hero banner images/slides | `HeroBanner` |
| `Media` | General media assets (images, videos) | `Media` |
| `Policy` | Policy documents | `Policy` |
| `QuickLink` | Homepage quick navigation links | `QuickLink` |
| `Search-Overrides` | Custom search priority overrides | `SearchOverride` |
| `Users` | CMS admin users | `User` |

### Payload CMS Globals (Site Configuration)

Globals are singleton configs managed in Payload. These map to Laravel settings/config tables:

| Payload Global | Purpose | Laravel Equivalent |
|---------------|---------|-------------------|
| `SiteInfo` | Site name, description, metadata | `settings` table or config |
| `Header` | Navigation menu items | `navigation_items` table |
| `Footer` | Footer links and content | `footer_settings` table |
| `Homepage` | Homepage layout configuration | `homepage_settings` table |
| `MinisterProfile` | Current minister's profile | `minister_profile` table |
| `Addresses` | Ministry physical addresses | `addresses` table |
| `FeedbackSettings` | Feedback widget configuration | `feedback_settings` table |

### Site Pages & Routes

All pages support locale-prefixed URLs (`/ms/...`, `/en/...`). Supported locales: `ms-MY` (default), `en-GB`.

| Route | Page Name | Component Directory | Description |
|-------|-----------|--------------------|----|
| `/` | Homepage | `home/` | Hero banner, quick links, broadcasts, achievements |
| `/siaran` | Siaran | `siaran/` | Broadcasts/news listing + detail |
| `/pencapaian` | Pencapaian | `pencapaian/` | Achievements listing |
| `/statistik` | Statistik | `statistik/` | Statistics with charts (Recharts) |
| `/direktori` | Direktori | `direktori/` | Staff directory with search |
| `/dasar` | Dasar | `dasar/` | Policy documents listing |
| `/profil-kementerian` | Profil Kementerian | — | Ministry profile & minister info |
| `/hubungi-kami` | Hubungi Kami | — | Contact information & form |
| `/penafian` | Penafian | — | Disclaimer (static) |
| `/dasar-privasi` | Dasar Privasi | — | Privacy policy (static) |

### Search Configuration

The Payload search plugin indexes these collections with priorities:
- Achievement: priority 10
- Broadcast: priority 20
- Staff Directory: priority 30
- Policy: priority 40

### Key Frontend Dependencies (to replicate)

| Package | Purpose | Laravel Replacement |
|---------|---------|-------------------|
| `embla-carousel-react` | Hero banner carousel | Alpine.js + CSS |
| `@tanstack/react-table` | Data tables | Blade + Alpine.js |
| `recharts` | Statistics charts | Chart.js or ApexCharts |
| `@radix-ui/*` | Accessible UI primitives | Custom Blade components |
| `cmdk` | Command palette search | Alpine.js search |
| `date-fns` | Date formatting | PHP Carbon |
| `react-day-picker` | Date picker | Flatpickr |
| `next-intl` | Internationalisation | Laravel's `lang/` + `App::setLocale()` |

---

## Conversion Strategy

### Architecture Comparison

| Aspect | kd-portal (Source) | OpenGovPortal (Target) |
|--------|--------------------|------------------------|
| **Rendering** | SSR/SSG (Next.js) | SSR with Redis page caching |
| **CMS** | Payload CMS (headless) | Filament v5 admin panel |
| **Database** | MongoDB | PostgreSQL |
| **Storage** | AWS S3 | AWS S3 (via Laravel Filesystem) |
| **Search** | Payload Search Plugin | Laravel Scout + PostgreSQL FTS |
| **i18n** | next-intl (ms-MY, en-GB) | Laravel `lang/` (ms, en) |
| **Components** | React + Radix UI | Blade + Alpine.js |
| **Charts** | Recharts | Chart.js or ApexCharts |
| **Carousel** | Embla Carousel | Alpine.js + Embla or Swiper |
| **Language** | TypeScript | PHP 8.3 |

### Design System (MyDS)

The original portal uses **MyDS (Malaysian Government Design System)**. Key design tokens to replicate:

- **Primary Color:** Blue (`#2563EB`)
- **Font:** Inter (with system-ui fallback)
- **Spacing:** 4px base unit system
- **Breakpoints:** Mobile < 640px, Tablet 640px+, Desktop 768px+, Large 1024px+, XL 1280px+
- **Accessibility:** WCAG 2.1 AA compliance
- **Components:** Navigation, Cards, Buttons, Alerts, Forms, Tables

---

## Implementation Plan: 12 Weeks

### Phase 1: Foundation (Weeks 1–2)

#### Week 1: Project Initialization & Tooling ✅ COMPLETED 2026-02-21

Get the full stack bootstrapped with all required packages before writing any application code.

**Tasks:**
- [x] Initialize Laravel 12 project (12.52.0)
- [x] Install Laravel Octane with FrankenPHP (octane 2.13.5, binary downloaded)
- [x] Install Filament v5 admin panel, create admin user, publish config (5.2.2)
- [x] Install Laravel Boost (agentic coding support) (2.1.8)
- [x] Install Filament Blueprint (AI-powered scaffolding) (2.1.0)
- [x] Set up PostgreSQL database (DBngin, 127.0.0.1:5432, db: govportal)
- [x] Configure Laravel multi-language (`ms`, `en`) with locale URL prefix (`APP_LOCALE=ms`)
- [x] Run initial migrations and seed super admin user (`admin@digital.gov.my`)

**Installation Commands:**

```bash
# 1. Create Laravel 12 project
composer create-project laravel/laravel govportal
cd govportal

# 2. Laravel Octane + FrankenPHP
composer require laravel/octane
php artisan octane:install --server=frankenphp

# 3. Filament v5 Admin Panel
composer require filament/filament:"^5.0"
php artisan filament:install --panels
php artisan make:filament-user
php artisan vendor:publish --tag=filament-config

# 4. Laravel Boost (agentic coding support)
composer require laravel/boost --dev
php artisan boost:install

# 5. Filament Blueprint (AI-powered model scaffolding)
# Requires a Filament license — replace placeholders with your credentials
composer config repositories.filament composer https://packages.filamentphp.com/composer
composer config --auth http-basic.packages.filamentphp.com "akmalakhpah@gmail.com" "0e847ae6-1e02-4c63-8a96-6622d569ec8b"
composer require filament/blueprint --dev
```

**Deliverables:**
- Laravel 12 boots without errors (`php artisan serve` returns 200)
- Octane FrankenPHP starts: `php artisan octane:start`
- Filament admin accessible at `/admin` with seeded super_admin user
- `php artisan boost:install` completes without errors
- Filament Blueprint available for AI-assisted scaffolding

**Effort:** 16 hours

#### Week 2: Design System & Base UI ✅ COMPLETED 2026-02-21

> **Note:** Several Week 2 tasks were completed as part of Week 1 or are already present in Laravel 12 defaults:
> - Tailwind CSS v4.x — already installed (`@tailwindcss/vite ^4.0.0` in `package.json`)
> - Vite + `@tailwindcss/vite` plugin — already configured in `vite.config.js`
> - `@import 'tailwindcss'` in `resources/css/app.css` — already present
> - FrankenPHP: `'server' => 'frankenphp'` in `config/octane.php` — done in Week 1
> - Livewire 4: installed and compatible via Filament v5 — done in Week 1
>
> Week 2 therefore focuses only on what is genuinely outstanding.

**Tasks:**
- [x] Install JS dependencies and add Alpine.js v3.x (`npm install && npm install alpinejs`); import and start Alpine in `resources/js/app.js`
- [x] Create `resources/css/themes/default.css` with all MyDS tokens under `[data-theme="default"], :root` (see `docs/design.md → Theme System`)
- [x] Update `resources/css/app.css`: import default theme file; declare `@theme inline` block so Tailwind generates utility classes from CSS variables; Inter font import moved to top
- [x] Create `config/themes.php` with `valid_themes => ['default' => 'Default']` and `fallback` key
- [x] Create `ApplyTheme` middleware: reads `govportal_theme` cookie → falls back to `settings.site_default_theme` → shares `$currentTheme` view variable; registered on `web` group in `bootstrap/app.php`
- [x] Create `SetLocale` middleware: reads `{locale}` route param, validates, calls `App::setLocale()`; aliased as `setlocale` in `bootstrap/app.php`
- [x] Create `app/Models/Setting.php` with `get()` / `set()` helpers; create `settings` migration
- [x] Create base Blade components: `resources/views/components/layouts/app.blade.php` (`<html data-theme="{{ $currentTheme }}">`) and `layouts/guest.blade.php`
- [x] Build `<x-layout.nav>` navigation component with mobile hamburger menu (Alpine.js) and language switcher (`ms`/`en`)
- [x] Build `<x-layout.footer>` component
- [x] Build `<x-layout.theme-switcher>` Alpine.js component; placed in nav (desktop + mobile)
- [x] Install Spatie Laravel Permission (v7.2.0); add `HasRoles` to `User` model; create `RoleSeeder`
- [x] Create `SettingsSeeder`; seed `site_default_theme = default` and site info defaults
- [x] Update `DatabaseSeeder` to call `RoleSeeder` and `SettingsSeeder`
- [x] Create `lang/ms/common.php` and `lang/en/common.php` with nav, footer, action labels
- [x] Create `HomeController`, locale-prefixed routes in `web.php`, placeholder home view
- [x] Share `$navItems` and `$footerData` via `AppServiceProvider::boot()` (hardcoded; swapped to DB in Week 4)
- [x] Write `tests/Feature/HomepageTest.php` — 5 assertions pass

**Deferred (moved to correct phase):**
- AWS S3 disk → Week 4 (needed when `File`/`Media` models are built)
- Full-page caching middleware → Week 10 (Performance phase)
- Laravel Scout → Week 5 (Admin Polish phase)
- CI/CD pipeline → parallel track (not week-gated)

**Installation Commands:**

```bash
# 1. Install JS deps + add Alpine.js
npm install
npm install alpinejs

# 2. Import Alpine in resources/js/app.js
# Add: import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();

# 3. Spatie Laravel Permission
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
# Then create a DatabaseSeeder or RoleSeeder for the 4 roles
```

**Deliverables:**
- `npm run build` completes without errors
- MyDS colour tokens visible in browser DevTools (`--color-primary: #2563EB` on `[data-theme="default"]`)
- `<html data-theme="default">` rendered server-side on every public page
- Theme switcher component visible; clicking it changes `data-theme` and writes cookie
- Alpine.js v3 registered globally; hamburger and dropdowns toggle correctly
- Base layout renders with header + footer across both locales (`/ms` and `/en`)
- Language switcher swaps locale correctly
- RBAC roles seeded: `super_admin`, `content_editor`, `publisher`, `viewer`
- PHPUnit smoke test passes: `php artisan test --filter=HomepageTest`

**Effort:** 20 hours

---

### Phase 2: Content Models & CMS (Weeks 3–5)

> **AI-agentic rule for Phase 2:** Filament Blueprint is a **planning tool only** — `php artisan blueprint:build` does not exist. Use `php artisan make:model -mfs` to scaffold model + migration + factory + seeder, then `php artisan make:filament-resource` for the admin resource. Verify migration against `docs/database-schema.md`, then apply the Post-Generation Checklist. See `memory/filament-v5.md` for Filament v5 property types and resource structure.

#### Week 3: Core Content Models ✅ COMPLETED 2026-02-21

Map all Payload CMS collections to Laravel models + Filament resources:

**Tasks:**
- [x] Create `Broadcast`, `Achievement`, `Celebration`, `HeroBanner`, `QuickLink`, `Policy` via `make:model -mfs` + `make:filament-resource`
- [x] Verify each migration matches `docs/database-schema.md` exactly; add GIN indexes for FTS (wrapped in `DB::getDriverName() === 'pgsql'` check for SQLite test compatibility)
- [x] Apply Post-Generation Checklist for all 6 models (bilingual form tabs, `published()`/`active()` scopes, factories with states, bilingual seeders)
- [x] Update `RoleSeeder` with all 6 roles and 55 content permissions
- [x] Write PHPUnit feature tests for all 6 models (46 tests, 68 assertions)
- [x] Run Pint formatter

**Each model follows this workflow:**
1. `php artisan make:model -mfs` → write migration matching `docs/database-schema.md` column-for-column
2. `php artisan make:filament-resource` → customise form schema (bilingual tabs) and table
3. Bilingual fields: `title_ms`/`title_en`, `content_ms`/`content_en` (or `description_ms`/`description_en`)
4. Draft/published status with `published_at` scheduling; `scopePublished()` or `scopeActive()` local scope
5. Featured image stored as S3 key string (via admin-configured `media_disk`)

**Lessons learned:**
- Filament v5 `$navigationGroup` must use type `\UnitEnum|string|null` (not `?string`)
- PostgreSQL-specific raw SQL (GIN indexes, `to_tsvector`, `DESC` indexes) must be guarded by `if (DB::getDriverName() === 'pgsql')` since PHPUnit uses SQLite `:memory:`
- Filament v5 forms use `Filament\Schemas\Schema` (not `Filament\Forms\Form`), `->components()`, `->recordActions()`, `->toolbarActions()`

**Deliverables:**
- 6 Filament resources working (Content group: Broadcast, Achievement, Celebration, Policy; Homepage group: HeroBanner, QuickLink)
- All 6 migrations run on PostgreSQL
- 53 total tests passing (80 assertions)

**Effort:** 40 hours

#### Week 4: Directory, Files & Site Config ✅ COMPLETED 2026-02-21

**Tasks — Content Models (5 remaining):**
- [x] `StaffDirectory` — model, migration (`staff_directories` table with GIN FTS index), factory, seeder, Filament resource with bilingual position/department fields
- [x] `PolicyFile` — model (mapped from Payload `File`), migration (`files` table), factory, seeder, Filament resource; note: use `protected $table = 'files'` per CLAUDE.md naming (`File → PolicyFile`)
- [x] `Media` — model, migration (`media` table), factory, seeder, Filament resource with file upload
- [x] `Feedback` — model, migration (`feedbacks` table), factory, seeder, Filament resource (read-only admin view); `ip_address` as `string(45)` for cross-DB compatibility
- [x] `SearchOverride` — model, migration (`search_overrides` table), factory, seeder, Filament resource

**Tasks — Site Config Tables (4 new migrations):**
- [x] `footer_settings` migration + `ManageFooter` Filament settings page (Repeater-based)
- [x] `minister_profiles` migration + `ManageMinisterProfile` Filament settings page (single record)
- [x] `addresses` migration + `ManageAddresses` Filament settings page (Repeater-based)
- [x] `feedback_settings` migration + `ManageFeedbackSettings` Filament settings page (KV model)

**Tasks — Settings Pages (using existing `settings` table):**
- [x] `ManageSiteInfo` — site name (ms/en), description, logo, dark-mode logo, favicon, social media URLs, GA tracking ID, default theme (15 settings keys)
- [x] `ManageEmailSettings` — mail driver (ses/smtp/mailgun/log), SMTP host/port/credentials (encrypted via `Crypt::encrypt()`), from address/name (ms/en); conditional SMTP section visibility
- [x] `ManageMediaSettings` — storage driver selector (local/s3/r2/gcs/azure) with conditional credential fields per provider; 6 encrypted keys for cloud credentials

**Tasks — Review Fixes:**
- [x] Added `department`, `avatar`, `is_active`, `last_login_at` columns to `users` table (was missing per schema doc)
- [x] Fixed `settings.updated_at` from `TIMESTAMP` to `TIMESTAMPTZ` per schema doc
- [x] Fixed `Feedback::scopeUnread()` to use `where()` instead of `whereIn()` with single value
- [x] Fixed `FeedbackSetting::set()` to explicitly update `updated_at` column

**Deferred to Week 6:**
- `ManageHomepage` — no schema defined in `database-schema.md`; defer until Homepage page is built (Week 6) when actual layout flags are determined. Will use `settings` table keys (e.g. `homepage_show_carousel`, `homepage_section_order`).

**Notes:**
- Rich text editor: Filament v5 already includes `RichEditor` — no additional integration needed (already used in `BroadcastForm` for `content_ms`/`content_en`)
- Cloud Flysystem packages: install only when `ManageMediaSettings` is tested with a live provider
- All 7 settings pages follow the Filament v5 custom page pattern: `$data` array, `Form::make()` with `livewireSubmitHandler('save')`, `Action::make('save')->submit('save')` in footer
- Encrypted settings (passwords, API keys, cloud credentials) use `Crypt::encrypt()`/`Crypt::decrypt()` with `DecryptException` fallback

**Installation Commands (cloud storage — install only drivers you need):**

```bash
# AWS S3 and Cloudflare R2 (R2 reuses the S3 adapter with a custom endpoint)
composer require league/flysystem-aws-s3-v3 "^3.0"

# GCP Cloud Storage
composer require league/flysystem-google-cloud-storage "^3.0"

# Azure Blob Storage
composer require league/flysystem-azure-blob-storage "^3.0"
```

**Deliverables:**
- All 11 Payload collections mapped to Filament resources (6 from Week 3 + 5 new)
- 4 site config tables migrated + 4 settings pages working
- 3 settings pages (SiteInfo, Email, Media) reading/writing `settings` table
- 87 tests passing (132 assertions)
- 22 migrations total

**Effort:** 40 hours

#### Week 5: Admin Polish

Split into must-have (5a) and nice-to-have (5b) to avoid overloading.

**Week 5a — Must-Have: ✅ COMPLETED 2026-02-22**
- [x] `UserResource` enhancements: `department` field for `department_admin` scoping, `last_login_at` display column, deactivate/reactivate user action, bulk role assignment, avatar upload
- [x] `RoleResource`: CRUD for Spatie Permission roles + checkbox-based permission assignment per role (note: `RoleSeeder` already seeds all 6 roles + 60 permissions from Week 4)
- [x] Role-based access within Filament resources — 16 Filament policies for all content resources, scoped by Spatie permissions
- [x] Draft/publish workflow: publish action button on list/edit pages, `published_at` DateTimePicker for scheduling (note: auto-publish scheduler command deferred to Week 10)
- [x] Bulk actions in Filament: publish, unpublish, activate/deactivate, delete (with confirmation)
- [x] `StaticPage` + `PageCategory` models, migrations, Filament resources (from `database-schema.md`)
- [x] `Menu` + `MenuItem` models, migrations, Filament resource (`MenuResource` with nested items, tree display, role-based visibility, `is_system` flag)
- [x] `ManageHomepage` settings page — section visibility toggles, content limits, section order configuration using `settings` table keys
- [x] Wire `ManageMediaSettings` to Filament file uploads — `MediaDiskService` (reads `media_disk` setting, decrypts cloud credentials, calls `Config::set()` at runtime); hooked via `AdminPanelProvider::bootUsing()`; `configureUsing()` defaults for `RichEditor` (directory) and `FileUpload` (visibility); `local` → `public` disk mapping; `r2`/`gcs`/`azure` placeholder disks in `config/filesystems.php`; `league/flysystem-aws-s3-v3` installed
- [x] `MyProfile` (EditProfile) Filament page — current user can manage their own profile:
  - Edit name, email, avatar (file upload with size/format constraints)
  - Change password (current + new + confirm)
  - Change preferred language (ms/en), stored as user preference, locale applied on save
  - Delete own account (with confirmation; blocked for `super_admin` role)

**Week 5b — Nice-to-Have (can overlap with Week 6): ✅ COMPLETED 2026-02-22**
- [x] Search indexing via PostgreSQL FTS — `searchable_content` migration with generated TSVECTOR columns + GIN indexes (PostgreSQL-only, SQLite LIKE fallback for tests); `SearchableContent` model, `HasSearchableContent` trait on 4 models, `SearchContentObserver`, `SearchService` with override priority
- [x] Image optimization on upload — `ImageOptimizationService` (WebP conversion + resize > 2048px) + `OptimizeImageJob` (queued); uses `intervention/image` v3 with GD driver
- [x] Content preview from admin — `PreviewController` with signed URLs (`/preview/{model}/{id}`), `HasPreviewUrl` trait on Edit pages, `AllowPreview` middleware, preview Blade view with banner
- [x] Content versioning / revision history — `spatie/laravel-activitylog` for audit logging (6 models), `content_revisions` table + `ContentRevision` model + `HasContentRevisions` trait (5 publishable models) + `ContentRevisionObserver` for auto-snapshot on update

**Deliverables:**
- Full CMS parity with Payload (all 12 collections + all globals)
- Draft → Publish workflow with permissions enforcement
- User + role management in Filament
- My Profile page with password reset, language change, and account deletion
- Search indexing working (5b)

**Effort:** 40 hours

---

### Phase 3: Public Pages (Weeks 6–9)

#### Week 6: Homepage ✅ COMPLETED 2026-02-22

Recreate homepage sections from kd-portal's `home/` components:

**Tasks:**
- [x] Hero banner section (carousel with HeroBanner collection data, Embla.js)
- [x] Quick links grid (QuickLink collection data, responsive 1→2→3 columns)
- [x] Latest broadcasts section (last 6 Broadcast items, configurable via settings)
- [x] Achievements highlights (last 7 Achievement items, timeline layout)
- [x] Section visibility + ordering settings (ManageHomepage)
- [x] Alpine.js + Embla.js carousel interactivity (autoplay, dots, prev/next)
- [x] Mobile-responsive layout
- [x] Tests: HomepageTest (33 tests)

**Deliverables:**
- Homepage fully functional (ms/en)
- Carousel working
- Data from database
- Section visibility/ordering configurable from admin

**Effort:** 40 hours

#### Week 7: Siaran & Pencapaian Pages ✅ COMPLETED 2026-02-22

**Tasks:**
- [x] `/siaran` — `SiaranList` Livewire component (type filter + pagination)
- [x] `/siaran/{slug}` — Broadcast detail page (pure Blade, no Livewire)
- [x] `/pencapaian` — `PencapaianList` Livewire component (year filter)
- [x] `/pencapaian/{slug}` — Achievement detail (pure Blade)
- [x] Breadcrumb navigation component
- [x] Related content section (pure Blade, server-side query)
- [x] SEO meta tags per page (`@push('seo')` in detail views + `@stack('seo')` in layout)

**Deliverables:**
- Siaran and Pencapaian pages in ms/en
- Livewire filter + pagination working under Octane
- Detail pages with full content

**Effort:** 40 hours

#### Week 8: Direktori & Statistik Pages ✅ COMPLETED 2026-02-22

**Tasks:**
- [x] `/direktori` — `DirektoriSearch` Livewire component (name + department filter, `wire:model.live.debounce.400ms`)
- [x] Staff card Blade component with photo, name, position, email, phone
- [x] `/statistik` — Statistics page with Chart.js (pure Blade + Alpine.js `x-init`)
- [x] Statistics data management in Filament (`ManageStatistik` page, JSON in `settings` table)
- [x] Responsive grid layout for staff cards

**Deliverables:**
- Direktori page with live Livewire search (tested under Octane)
- Statistik page with Chart.js rendering

**Effort:** 40 hours

#### Week 9: Static & Policy Pages ✅ COMPLETE

**Tasks:**
- [x] Lang files for all Week 9 pages (dasar, profil, hubungi, carian, errors — ms + en)
- [x] `DasarController` — index (policy listing with Alpine.js category filter) + download (increment `download_count`, redirect)
- [x] `/dasar` views — `dasar/index.blade.php` + `components/dasar/policy-card.blade.php`
- [x] `ProfilKementerianController` — index (minister profile + vision/mission from settings)
- [x] `/profil-kementerian` views — `profil-kementerian/index.blade.php` + `components/profil/minister-card.blade.php`
- [x] `HubungiKamiController` — index (addresses section + ContactForm Livewire shell)
- [x] `AddressFactory` + HasFactory trait on Address model
- [x] `ContactForm` Livewire component — name/email/subject/message fields, `wire:model.blur` validation, rate limit (5/IP/hour)
- [x] Contact form submission → store to `feedbacks` table
- [x] `/hubungi-kami` views — `hubungi-kami/index.blade.php` + `livewire/contact-form.blade.php` + `components/hubungi/address-card.blade.php`
- [x] `StaticPageController@show` — penafian + dasar-privasi routes with `defaults('slug', ...)`, renders `static/show.blade.php`
- [x] Static page seeder already existed with penafian + dasar-privasi
- [x] `/penafian` + `/dasar-privasi` — `static/show.blade.php` shared template with SEO meta
- [x] `SearchController` — index (renders page shell for SearchResults Livewire)
- [x] `SearchResults` Livewire component — `wire:model.live.debounce.500ms`, case-insensitive LIKE search on `searchable_content`, SearchOverride priority
- [x] `/carian` views — `carian/index.blade.php` + `livewire/search-results.blade.php`
- [x] XML Sitemap — `SitemapController` + `/sitemap.xml` route (root level, not locale-prefixed)
- [x] Error pages — `errors/404.blade.php`, `errors/500.blade.php`, `errors/403.blade.php`
- [x] All Week 9 routes uncommented + active in `routes/public.php`
- [x] Tests: DasarPageTest (14), ProfilKementerianPageTest (11), HubungiKamiPageTest (14), StaticPageTest (14), CarianPageTest (11), SitemapTest (7)
- [x] Full test suite passes: 456 tests, 840 assertions + build + Pint

**Deliverables:**
- All 10 pages complete
- Contact form with rate limiting
- Sitemap live at /sitemap.xml
- Error pages styled (403, 404, 500)

**Effort:** 40 hours

---

### Phase 4: AI Features, Code Quality & Performance (Weeks 10–14) — Local Development

> All tasks in this phase run on the developer machine. No server or cloud infrastructure required.
>
> **Strategy:** Build AI features first (Weeks 10–12), then run a comprehensive quality and performance pass (Weeks 13–14) that covers the **entire application including AI**. This ensures caching, security, accessibility, and test coverage all account for the AI chatbot, embedding pipeline, and admin editor.
>
> AI features are an **approved extension beyond kd-portal parity**. See [docs/ai.md](ai.md) and [docs/architecture.md → AI Services Layer](architecture.md) for full specification.

#### Week 10: RAG Foundation ✅ COMPLETED 2026-02-23

Set up the embedding pipeline so all content is vector-indexed before building the chatbot.

**Tasks:**
- [x] Install `pgvector` PostgreSQL extension on local PostgreSQL; enable in migration
- [x] Create `content_embeddings` migration + `ContentEmbedding` model (morphic, chunk_index, locale, embedding `vector(1536)`, metadata JSON)
- [x] Install Prism PHP (`echolabsdev/prism`); configure providers in `config/prism.php`
- [x] Create `AiService` (`app/Services/AiService.php`) — single entry point for all LLM + embedding calls; reads active provider from `settings` table
- [x] Create `RagService` (`app/Services/RagService.php`) — embed query → pgvector cosine similarity search → context assembly
- [x] Create `EmbeddingObserver` (`app/Observers/EmbeddingObserver.php`) — fires `GenerateEmbeddingJob` on model `saved`/`deleted`
- [x] Create `GenerateEmbeddingJob` (`app/Jobs/GenerateEmbeddingJob.php`) — queued; chunks content, calls admin-configured embedding provider, upserts `content_embeddings`
- [x] Register `EmbeddingObserver` on embeddable models in `AppServiceProvider`: `Broadcast`, `Achievement`, `Policy`, `StaffDirectory`
- [x] Create `ManageAiSettings` Filament settings page with **4 sections**:
  - **Provider Configuration** — LLM provider/model (Select dropdown + custom)/key/base URL
  - **Embedding Configuration** — embedding provider/model (Select dropdown + custom)/key/dimension
  - **Feature Flags** — chatbot enabled, admin editor enabled, chatbot rate limit (msg/hour/IP)
  - **Chatbot Settings** — bot identity and behavior, all admin-configurable:
    - Bot name (ms/en) — displayed in chat header and greeting
    - Bot avatar — file upload; displayed next to bot messages
    - Bot persona (ms/en) — personality instruction appended to system prompt
    - Language reply preference — `same_as_page` (default) | `always_ms` | `always_en` | `user_choice` | `ms_en_only` (BM/EN only)
    - Bot restrictions (ms/en) — topics the bot must refuse
    - Display location — `all_pages` (default) | `homepage_only` | `specific_pages`
    - Display pages — comma-separated route names for `specific_pages` mode
    - Welcome message (ms/en) — first message shown when chat opens
    - Input placeholder (ms/en) — placeholder text in the message input field
    - Disclaimer text (ms/en) — privacy disclaimer modal content
- [x] Create `AiProviderValidator` service — hardcoded model lists per provider + API key validation via HTTP (cached 60s); model dropdown with "Other (custom)" option
- [x] API key validation on save — invalid keys rejected (previous valid keys retained), other settings still saved; warning notification for invalid keys
- [x] Add AI env vars to `.env.example` (`AI_LLM_PROVIDER`, `AI_LLM_MODEL`, `AI_LLM_API_KEY`, `AI_EMBEDDING_PROVIDER`, etc.)
- [x] Write `GenerateEmbeddingJobTest` + `RagServiceTest` + `AiServiceTest` + `ManageAiSettingsTest` + `AiProviderValidatorTest`

**Installation Commands:**

```bash
# pgvector extension (run in local PostgreSQL)
psql -U postgres -d govportal -c "CREATE EXTENSION IF NOT EXISTS vector;"

# Prism PHP
composer require echolabsdev/prism

# Queue worker (for embedding jobs)
php artisan queue:work --queue=embeddings
```

**Deliverables:**
- `content_embeddings` table with pgvector column (SQLite fallback for tests)
- Embedding pipeline working locally (seed → observe → queue → embed → store)
- `ManageAiSettings` Filament page functional (4 sections: provider config, embedding config, feature flags, chatbot settings)
- Model dropdowns with known models per provider + "Other (custom)" option
- API key validation on save — invalid keys rejected, other settings still saved
- Chatbot settings saved and retrievable from `settings` table (name, avatar, persona, language pref incl. `ms_en_only`, restrictions, display location, welcome message, placeholder, disclaimer)
- 5 test files: `GenerateEmbeddingJobTest`, `RagServiceTest`, `AiServiceTest`, `ManageAiSettingsTest`, `AiProviderValidatorTest`
- 558 total tests passing (1037 assertions)

**Effort:** 32 hours

---

#### Week 11: Public AI Chatbot ✅ COMPLETED 2026-02-23

Build the `AiChat` Livewire component and integrate it into the public layout.

**Tasks:**
- [x] Create `AiChat` Livewire component (`app/Livewire/AiChat.php` + `resources/views/livewire/ai-chat.blade.php`)
  - Properties: `$messages = []` (session conversation history), `$input = ''`, `$isThinking = false`
  - Method `send()`: validates input, embeds query via `RagService`, builds prompt, calls LLM via `AiService`, appends response to `$messages`
  - Rate limiting: configurable per IP via `RateLimiter::attempt()` (default 10/hour, setting `ai_chatbot_rate_limit`)
  - Session-only history: store in PHP session, not DB; never log PII
- [x] Apply chatbot settings from `ManageAiSettings` (configured in Week 10):
  - Bot name + avatar displayed in chat header and next to bot messages
  - Bot persona + restrictions injected into LLM system prompt
  - Language preference controls response locale (`same_as_page` | `always_ms` | `always_en` | `user_choice` with in-chat toggle)
  - Welcome message shown as first bot message when chat opens
  - Input placeholder text from settings
  - Display location logic — component renders only on allowed pages (`all_pages` | `homepage_only` | `specific_pages` with route name check via `Route::currentRouteName()`)
- [x] Create privacy disclaimer modal (Alpine.js) — content from `ai_chatbot_disclaimer_ms`/`_en` setting (falls back to `lang/ai.php` default); shown on first open; acceptance stored in session
- [x] Add `<livewire:ai-chat />` to `resources/views/components/layouts/app.blade.php`
- [x] Style floating chat button + chat window with Tailwind (MyDS tokens)
- [x] Add `lang/ms/ai.php` + `lang/en/ai.php` for all AI-related UI strings (used as fallbacks when settings not configured)
- [x] Fix `AiService::chat()` — `withPrompt()` + `withMessages()` are mutually exclusive in Prism PHP; when history exists, append current prompt as last `UserMessage` and use `withMessages()` only
- [x] Move accessibility menu floating trigger to nav header (freed bottom-right for AI chat widget)
- [x] Write `AiChatTest` (mock `AiService` + `RagService`) — 25 tests, 46 assertions covering:
  - Bot name/avatar rendering
  - Display location filtering (hidden on excluded pages)
  - Language preference modes
  - Welcome message display
  - Rate limit enforcement
  - Disclaimer acceptance
  - Session persistence
  - Error handling

**Deliverables:**
- Chat widget visible on allowed pages only (per display location setting); hidden if not configured
- Bot identity (name, avatar, persona) rendered from admin settings
- Language preference applied to responses
- Welcome message shown on first open
- Sends question → receives contextually accurate RAG response
- Rate limiting enforced (configurable)
- Privacy disclaimer with admin-customisable text
- `AiChatTest` passes — 25 tests, 46 assertions
- 583 total tests passing (1083 assertions)

**Effort:** 32 hours

---

#### Week 12: Admin AI Content Editor + AI QA ✅ COMPLETED 2026-02-23

Inject AI actions into existing Filament resources, then validate AI-specific quality.

**Tasks — Admin AI Editor:**
- [x] Implement 6 `AiService` stub methods (`grammarCheck`, `translate`, `expand`, `summarise`, `tldr`, `generateFromPrompt`) + private `generate()` helper + `logUsage()` method
- [x] Create `ai_usage_logs` migration + `AiUsageLog` model (anonymised: no user PII)
- [x] Create `lang/ms/ai_admin.php` + `lang/en/ai_admin.php` (20 translation keys each)
- [x] Create 6 Filament action classes in `app/Filament/Actions/Ai/`:
  - `AiGrammarAction` — grammar check BM or EN
  - `AiTranslateAction` — BM ↔ EN translation (source/target field params, confirmation required)
  - `AiExpandAction` — expand selected text
  - `AiSummariseAction` — summarise field content
  - `AiTldrAction` — generate 2-3 sentence TLDR → fills `excerpt_{locale}` field
  - `AiGenerateAction` — generate from text prompt (modal with Textarea)
- [x] Create `HasAiEditorActions` trait (`app/Filament/Concerns/`) with `richEditorAiActions()`, `textareaAiActions()`, `excerptAiActions()` helper methods
- [x] Inject AI actions into 4 form schemas: `BroadcastForm`, `AchievementForm`, `PolicyForm`, `StaticPageForm` via `afterContent()`
- [x] Feature flag gating: all actions visible only when `ai_admin_editor_enabled = true` AND API key configured (`AiGrammarAction::isAiEditorEnabled()`)
- [x] Write `AiEditorActionsTest` (15 tests) — Livewire component tests with `TestAction::make()->schemaComponent()`
- [x] Write `AiUsageLogTest` (7 tests) — model creation, casts, PDPA compliance assertions
- [x] Update `AiServiceTest` — replaced 6 `BadMethodCallException` stub tests with real method tests using `Prism::fake()` + `TextResponseFake`; 27 tests total

**Tasks — AI QA & Compliance:**
- [x] PDPA compliance audit: `ai_usage_logs` has no user PII columns; `content_embeddings` contains only public content; chat history session-only
- [x] `AI_CHATBOT_ENABLED` + `AI_ADMIN_EDITOR_ENABLED` feature flags — disabled → hide widget/actions gracefully
- [x] Update `docs/ai.md` with final architecture, usage logging, trait info, PDPA checklist (all items checked)

**Deferred (see Backlog):**
- `AiGenerateFromImageAction` — Prism PHP multimodal support varies by provider
- Load test embedding job queue (requires production-like environment)
- Benchmark chatbot response time (requires live LLM provider)
- pgvector IVFFlat index (add when > 10,000 rows in production)

**Deliverables:**
- 6 AI editor actions working in 4 form schemas (BroadcastForm, AchievementForm, PolicyForm, StaticPageForm)
- Usage logging via `ai_usage_logs` table (anonymised)
- All AI tests passing (Prism::fake() mocked)
- PDPA compliance checklist complete
- `docs/ai.md` finalised
- 616 total tests passing (1155 assertions)

**Effort:** 40 hours

---

#### Week 13: Performance Optimisation & Features

Optimise the entire application — including AI features built in Weeks 10–12.

**Tasks:**
- [ ] Full-page cache for all public routes (cache driver per environment, TTL 1 hour) — use `CacheResponse` middleware with plain `Cache::remember()` (no `Cache::tags()` — database driver doesn't support it); AI chat Livewire requests bypass page cache
- [ ] Cache invalidation on content save — `CacheObserver` registered on all content models (see [pages-features.md → Cache Tag → Route / Model Mapping](pages-features.md) for invalidation rules); uses explicit `Cache::forget()` with key patterns
- [ ] Database query optimisation — eager loading audit across all controllers/Livewire components (including `AiChat`), N+1 detection, verify all GIN FTS indexes exist
- [ ] Image lazy loading — add `loading="lazy"` to all `<img>` tags on public pages (WebP conversion already implemented in Week 5b via `ImageOptimizationService`)
- [ ] Route, config, view caching — verify `php artisan route:cache`, `config:cache`, `view:cache` work correctly with all routes
- [ ] Add dark mode theme (`resources/css/themes/dark.css`) — register in `config/themes.php`; verify WCAG AA contrast ratios for dark palette; test theme switcher toggles correctly; verify AI chat widget renders correctly in dark mode
- [ ] Scheduled publishing command — `php artisan content:publish-scheduled` (Artisan command registered in Laravel scheduler, runs every minute, finds records where `status = 'draft'` AND `published_at <= now()`, sets `status = 'published'`; applies to Broadcast, Achievement, Policy, Celebration, StaticPage)
- [ ] Lighthouse audit → target 90+ score (run locally via Chrome DevTools; includes pages with AI chat widget loaded)

**Deliverables:**
- Cache invalidation working on all content models (AI chat bypasses page cache correctly)
- Dark mode theme complete with accessible contrast ratios (including chat widget)
- Scheduled publishing tested locally with all 5 publishable models
- Lighthouse performance score ≥ 90 (local)

**Effort:** 40 hours

---

#### Week 14: Testing, Accessibility & Security

Comprehensive quality pass over the **entire application** — all 10 pages, admin panel, AI chatbot, and AI editor.

**Tasks:**
- [ ] PHPUnit feature tests for all routes (ms + en locale) — target >80% coverage; includes AI component tests (`AiChatTest`, action tests, embedding tests)
- [ ] WCAG 2.1 AA accessibility audit + fixes (all 10 public pages, both themes, AI chat widget focus management + keyboard nav + screen reader)
- [ ] Security audit (CSRF token on all forms, XSS in user-generated content, SQL injection review, rate limiting on all form endpoints + AI chat, AI input sanitisation)
- [ ] Cross-browser responsive testing (Chrome, Firefox, Safari, mobile viewports; verify AI chat widget positioning and interaction on all breakpoints)

**Deliverables:**
- Test suite with >80% coverage (including AI features)
- WCAG compliance fixes applied (all critical/high resolved, including chat widget)
- Security audit passed (no critical/high findings; AI rate limiting verified)
- Cross-browser issues fixed

**Effort:** 40 hours

---

### Phase 5: Content Migration & Server Deployment (Weeks 15–16)

> All local development (Phases 1–4) is complete. This phase handles production environment setup, content migration from the live kd-portal, and launch. All tasks require server or cloud infrastructure access.

#### Week 15: Content Migration & Deployment Prep

**Tasks:**
- [ ] Export content from kd-portal MongoDB via Payload API (all 12 collections)
- [ ] Write migration scripts to import into PostgreSQL (map Payload fields → Laravel columns)
- [ ] Verify content integrity (record counts, image references, slug uniqueness, bilingual completeness)
- [ ] Set up URL redirects (preserve old kd-portal URL structure → new Laravel routes)
- [ ] Production environment configuration (`.env.production`, database credentials, Redis, queue driver, AI provider keys)
- [ ] SSL certificates + DNS configuration

**Deliverables:**
- All content migrated to PostgreSQL with verified integrity
- URL redirects tested (old → new)
- Production environment configured and validated

**Effort:** 40 hours

---

#### Week 16: Launch & Operations

**Tasks:**
- [ ] Cloudflare CDN integration (page rules, static asset caching, edge TTLs)
- [ ] Cloudflare WAF rules (allow Livewire POST requests, bypass full-page cache for AI chat)
- [ ] Octane tuning (worker count, `max_requests`, memory limits for FrankenPHP)
- [ ] Load testing on production-like environment (target: 10,000 concurrent users; AI chatbot handles 50 concurrent sessions)
- [ ] Post-launch monitoring setup (Sentry error tracking, Grafana dashboards, health checks)
- [ ] Final smoke tests on staging environment (all 10 pages, both locales, AI chatbot, admin panel)
- [ ] Soft launch (staging → production cutover)
- [ ] Post-launch monitoring active (24-hour watch period)

**Deliverables:**
- Live at production URL
- Monitoring active (Sentry + Grafana)
- Load test passed (10K concurrent users)
- 99.9% uptime target active

**Effort:** 40 hours

---

## Resource Requirements

### Team Composition

| Role | Count | Hours/Week | Total Hours |
|------|-------|------------|-------------|
| Tech Lead | 1 | 20 | 240 |
| Backend Developer | 2 | 40 | 960 |
| Frontend Developer | 1 | 40 | 480 |
| DevOps Engineer | 1 | 10 | 120 |
| QA Engineer | 1 | 20 | 240 |
| **TOTAL** | **6** | | **2,040** |

### Budget Estimate

| Item | Cost (MYR) |
|------|------------|
| Development (2,040 hours @ RM150/hr) | RM 306,000 |
| Infrastructure (12 weeks) | RM 10,000 |
| Third-party services (AWS SES, S3, Sentry) | RM 5,000 |
| **TOTAL** | **RM 321,000** |

---

## Collection-to-Model Mapping Reference

```php
// Payload: Broadcast → Laravel: Broadcast
class Broadcast extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'content_ms', 'content_en',
        'excerpt_ms', 'excerpt_en',
        'featured_image',        // S3 key
        'status',                // draft | published
        'published_at',
        'type',                  // press_release | announcement | news
    ];
}

// Payload: Achievement → Laravel: Achievement
class Achievement extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'description_ms', 'description_en',
        'date',
        'icon',                  // S3 key
        'status',
        'is_featured',
    ];
}

// Payload: Directory → Laravel: StaffDirectory
class StaffDirectory extends Model {
    protected $fillable = [
        'name',
        'position_ms', 'position_en',
        'department_ms', 'department_en',
        'email',
        'phone',
        'photo',                 // S3 key
        'sort_order',
        'is_active',
    ];
}

// Payload: Policy → Laravel: Policy
class Policy extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'description_ms', 'description_en',
        'file_url',              // S3 key (PDF)
        'category',
        'published_at',
        'status',
    ];
}

// Payload: HeroBanner → Laravel: HeroBanner
class HeroBanner extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'subtitle_ms', 'subtitle_en',
        'image',                 // S3 key
        'cta_label_ms', 'cta_label_en',
        'cta_url',
        'sort_order',
        'is_active',
    ];
}

// Payload: QuickLink → Laravel: QuickLink
class QuickLink extends Model {
    protected $fillable = [
        'label_ms', 'label_en',
        'url',
        'icon',
        'sort_order',
        'is_active',
    ];
}
```

---

## Milestones

| Week | Phase | Milestone | Success Criteria |
|------|-------|-----------|-----------------|
| 1 | 1 | ✅ Tooling Bootstrap | Laravel 12 + Octane + Filament + Boost + Blueprint all installed and verified — 2026-02-21 |
| 2 | 1 | ✅ Design System & Base UI | Alpine.js + MyDS tokens + theme system + nav/footer + RBAC roles + 5 passing tests — 2026-02-21 |
| 3 | 2 | ✅ Core Content Models | 6 models + Filament resources + 6 roles + 55 permissions + 53 tests passing — 2026-02-21 |
| 4 | 2 | ✅ Directory, Files & Site Config | 5 content models + 4 site config tables + 7 settings pages + 87 tests passing — 2026-02-21 |
| 5 | 2 | ✅ CMS Complete | All 12 collections + 16 policies + MyProfile + MediaDiskService + Menu/MenuItem + ManageHomepage + FTS + Image optimization + Content preview + Content versioning — 230 tests passing — 2026-02-22 |
| 9 | 3 | ✅ All Pages Complete | All 10 public pages functional in ms/en — 456 tests, 840 assertions |
| 10 | 4 | ✅ RAG Foundation | pgvector + embeddings + AiService + RagService + ManageAiSettings + AiProviderValidator + model dropdowns + API key validation — 558 tests, 1037 assertions — 2026-02-23 |
| 11 | 4 | ✅ AI Chatbot | AiChat Livewire component + AiService fix + 25 tests — 583 tests, 1083 assertions — 2026-02-23 |
| 12 | 4 | ✅ AI Editor + AI QA | 6 AI actions in 4 form schemas, usage logging, PDPA audit, Prism::fake() tests — 616 tests, 1155 assertions — 2026-02-23 |
| 13 | 4 | Performance & Dark Mode | Cache invalidation (incl. AI bypass), dark mode, scheduled publishing, Lighthouse ≥ 90 |
| 14 | 4 | QA Complete (LOCAL) | >80% test coverage (incl. AI), WCAG AA (incl. chat widget), security audit passed |
| 15 | 5 | Content Migrated | All kd-portal content in PostgreSQL, URL redirects, production env configured |
| 16 | 5 | Go Live | CDN + WAF + Octane tuned + monitoring + load test passed + launched |

---

## Backlog — Deferred Items for Future Development

Items deferred during Phases 1–4 that can be picked up in future sprints.

### AI Features

| Item | Deferred From | Reason | Priority | Notes |
|------|--------------|--------|----------|-------|
| `AiGenerateFromImageAction` | Week 12 | Prism PHP multimodal support varies by provider | Medium | Revisit when Prism PHP adds stable multimodal API; would allow generating content from uploaded images |
| Load test embedding job queue | Week 12 | Requires production-like environment | Low | Target: 100 concurrent saves without queue overflow |
| Benchmark chatbot response time | Week 12 | Requires live LLM provider | Low | Target: first token < 2s, full response < 10s |
| pgvector IVFFlat index | Week 12 | Only needed when > 10,000 embedding rows | Low | `CREATE INDEX ... USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)` |

### Infrastructure & Deployment

| Item | Deferred From | Reason | Priority | Notes |
|------|--------------|--------|----------|-------|
| AWS S3 disk integration testing | Week 2 | Needed when `File`/`Media` models tested with live provider | Medium | `ManageMediaSettings` already supports S3/R2/GCS/Azure; needs live testing |
| CI/CD pipeline | Week 2 | Parallel track, not week-gated | Medium | GitHub Actions or similar |
| Auto-publish scheduler command | Week 5 | Deferred to Week 13 | Medium | `php artisan content:publish-scheduled` for timed publishing |

### Performance & Quality (Week 13–14 scope)

| Item | Deferred From | Reason | Priority | Notes |
|------|--------------|--------|----------|-------|
| Full-page cache middleware | Week 2 | Moved to Week 13 (performance phase) | High | `CacheResponse` middleware with `Cache::remember()` |
| Dark mode theme | Week 13 | Upcoming | High | `resources/css/themes/dark.css` with WCAG AA contrast |
| Lighthouse audit (90+ target) | Week 13 | Upcoming | High | Includes AI chat widget loaded |
| WCAG 2.1 AA accessibility audit | Week 14 | Upcoming | High | All 10 pages + AI chat widget |
| Security audit | Week 14 | Upcoming | High | CSRF, XSS, SQL injection, rate limiting, AI input sanitisation |

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Content migration from MongoDB | High | Use Payload REST API to export JSON, write custom importers |
| MyDS design parity | Medium | Audit kd-portal Tailwind config, replicate classes exactly |
| Chart.js replacing Recharts | Low | Feature-equivalent; data format mapping required |
| Alpine.js replacing React | Medium | Audit all interactive components in kd-portal; some complexity expected |
| Filament vs Payload feature gaps | Medium | Map each Payload field type to Filament field; test all workflows |
| Scope creep | Medium | Strict change control; anything beyond kd-portal parity is Phase 2 |

---

## Acceptance Criteria

### Functional
- [ ] All 10 pages from kd-portal replicated
- [ ] All 12 Payload collections migrated to Filament resources
- [ ] All 7 Payload globals editable in Filament
- [ ] Multi-language (ms/en) working on all pages
- [ ] Contact form submits and sends email (AWS SES)
- [ ] Site search working across Broadcast, Achievement, Directory, Policy
- [ ] All content from kd-portal MongoDB imported to PostgreSQL
- [ ] URL redirects in place for all changed URLs

### Non-Functional
- [ ] Page load < 1 second (Lighthouse Performance 90+)
- [ ] 10,000+ concurrent users supported
- [ ] 99.9% uptime (SLA)
- [ ] WCAG 2.1 AA accessibility compliance
- [ ] MyDS design system compliance
- [ ] SEO: sitemap.xml, canonical URLs, Open Graph meta

---

*This plan converts https://github.com/govtechmy/kd-portal from Next.js 15 + Payload CMS + MongoDB to Laravel 12 + Octane + Filament + PostgreSQL, achieving full feature parity with https://www.digital.gov.my/*
