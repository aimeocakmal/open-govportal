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

**Week 5a — Must-Have:**
- [ ] `UserResource` enhancements: `department` field for `department_admin` scoping, `last_login_at` display column, deactivate/reactivate user action, admin password reset action, bulk role assignment
- [ ] `RoleResource`: CRUD for Spatie Permission roles + checkbox-based permission assignment per role (note: `RoleSeeder` already seeds all 6 roles + 60 permissions from Week 4)
- [ ] Role-based access within Filament resources — Filament policies for all content resources, scoped by Spatie permissions
- [ ] Draft/publish workflow: publish action button on list/edit pages, scheduled publishing via `published_at` (scheduler checks for publishable records)
- [ ] Bulk actions in Filament: publish, unpublish, change status (extends existing `DeleteBulkAction`)
- [ ] `StaticPage` + `PageCategory` models, migrations, Filament resources (from `database-schema.md`)
- [ ] `Menu` + `MenuItem` models, migrations, Filament resource (`MenuResource` with nested items)
- [ ] `ManageHomepage` settings page — homepage layout flags using `settings` table keys (deferred from Week 4; built now that Homepage data needs are clearer)
- [ ] `MyProfile` Filament page — current user can manage their own profile:
  - Edit name, email, avatar (file upload)
  - Change password (current + new + confirm)
  - Change preferred language (ms/en), stored as user preference
  - Delete own account (with confirmation modal + password re-entry; blocked for `super_admin` role to prevent accidental lockout)

**Week 5b — Nice-to-Have (can overlap with Week 6):**
- [ ] Search indexing via PostgreSQL FTS — `searchable_content` migration + custom Scout driver; note: uses `TSVECTOR GENERATED ALWAYS AS (...)` which is PostgreSQL-only, tests need special handling
- [ ] Image optimization on upload — WebP conversion, resize on S3 upload
- [ ] Content preview from admin — generate preview URL showing draft content
- [ ] Content versioning / revision history — **no schema defined in `database-schema.md`**; evaluate `spatie/laravel-activitylog` or add a `content_revisions` table before implementing

**Deliverables:**
- Full CMS parity with Payload (all 12 collections + all globals)
- Draft → Publish workflow with permissions enforcement
- User + role management in Filament
- My Profile page with password reset, language change, and account deletion
- Search indexing working (5b)

**Effort:** 40 hours

---

### Phase 3: Public Pages (Weeks 6–9)

#### Week 6: Homepage

Recreate homepage sections from kd-portal's `home/` components:

**Tasks:**
- [ ] Hero banner section (carousel with HeroBanner collection data)
- [ ] Quick links grid (QuickLink collection data)
- [ ] Latest broadcasts section (last 6 Broadcast items)
- [ ] Achievements highlights (last 7 Achievement items, sorted by date)
- [ ] Ministry announcements/stats strip
- [ ] Alpine.js for carousel interactivity
- [ ] Mobile-responsive layout

**Deliverables:**
- Homepage fully functional (ms/en)
- Carousel working
- Data from database

**Effort:** 40 hours

#### Week 7: Siaran & Pencapaian Pages

**Tasks:**
- [ ] `/siaran` — `SiaranList` Livewire component (type filter + pagination)
- [ ] `/siaran/{slug}` — Broadcast detail page (pure Blade, no Livewire)
- [ ] `/pencapaian` — `PencapaianList` Livewire component (year filter)
- [ ] `/pencapaian/{slug}` — Achievement detail (pure Blade)
- [ ] Breadcrumb navigation component
- [ ] Related content section (pure Blade, server-side query)
- [ ] SEO meta tags per page (`<x-seo>` Blade component)

**Deliverables:**
- Siaran and Pencapaian pages in ms/en
- Livewire filter + pagination working under Octane
- Detail pages with full content

**Effort:** 40 hours

#### Week 8: Direktori & Statistik Pages

**Tasks:**
- [ ] `/direktori` — `DirectoriSearch` Livewire component (name + department filter, `wire:model.live.debounce.400ms`)
- [ ] Staff card Blade component with photo, name, position, email, phone
- [ ] `/statistik` — Statistics page with Chart.js (pure Blade + Alpine.js `x-init`)
- [ ] Statistics data management in Filament (JSON field or dedicated settings)
- [ ] Responsive grid layout for staff cards

**Deliverables:**
- Direktori page with live Livewire search (tested under Octane)
- Statistik page with Chart.js rendering

**Effort:** 40 hours

#### Week 9: Static & Policy Pages

**Tasks:**
- [ ] `/dasar` — Policy documents listing + download links
- [ ] `/profil-kementerian` — Ministry profile with minister info (MinisterProfile global)
- [ ] `/hubungi-kami` — `ContactForm` Livewire component + addresses section (pure Blade)
- [ ] Contact form submission via Livewire → Feedback collection + dispatch `SendFeedbackEmail` job (AWS SES)
- [ ] `/penafian` — Disclaimer (static Blade view)
- [ ] `/dasar-privasi` — Privacy policy (static Blade view)
- [ ] Global site search at `/carian` — `SearchResults` Livewire component with `wire:model.live.debounce.500ms`
- [ ] XML Sitemap generation
- [ ] 404 and error pages

**Deliverables:**
- All 10 pages complete
- Contact form sending email
- Sitemap live

**Effort:** 40 hours

---

### Phase 4: Performance & Quality (Weeks 10–11)

#### Week 10: Performance Optimisation

**Tasks:**
- [ ] Full-page cache for all public routes (Redis, TTL 1 hour)
- [ ] Cache tag invalidation on content save (Filament observer hooks)
- [ ] Database query optimisation (eager loading, indexes)
- [ ] Image lazy loading + WebP conversion on upload
- [ ] Cloudflare CDN integration
- [ ] Octane tuning (workers, max_requests)
- [ ] Route, config, view caching in production
- [ ] Lighthouse audit → target 90+ score
- [ ] Add dark mode theme (`resources/css/themes/dark.css`) — register in `config/themes.php`; verify WCAG AA contrast ratios for dark palette

**Deliverables:**
- Page load < 1 second
- 90+ Lighthouse performance score
- Cache invalidation working

**Effort:** 40 hours

#### Week 11: Testing, Accessibility & Security

**Tasks:**
- [ ] PHPUnit feature tests for all routes (ms + en locale)
- [ ] Browser testing (Chrome, Firefox, Safari, mobile)
- [ ] WCAG 2.1 AA accessibility audit + fixes
- [ ] Security audit (CSRF, XSS, SQL injection, rate limiting)
- [ ] Load testing (target: 10,000 concurrent users)
- [ ] Cross-browser responsive testing

**Deliverables:**
- Test suite with >80% coverage
- WCAG compliance report
- Security audit passed

**Effort:** 40 hours

---

### Phase 5: Migration & Launch (Week 12)

**Tasks:**
- [ ] Export content from kd-portal MongoDB via Payload API
- [ ] Write migration scripts to import into PostgreSQL
- [ ] Verify content integrity (counts, images, slugs)
- [ ] Set up URL redirects (preserve old URL structure)
- [ ] Production environment configuration
- [ ] SSL certificates + DNS
- [ ] Final smoke tests
- [ ] Soft launch (staging → production)
- [ ] Post-launch monitoring setup (Sentry, Grafana)

**Deliverables:**
- All content migrated
- Live at digital.gov.my equivalent
- Monitoring active

**Effort:** 40 hours

---

### Phase 6: AI Features (Weeks 13–16)

> **Approved extension beyond kd-portal parity.** See [docs/ai.md](ai.md) and [docs/architecture.md → AI Services Layer](architecture.md) for full specification.

#### Week 13: RAG Foundation

Set up the embedding pipeline so all content is vector-indexed before building the chatbot.

**Tasks:**
- [ ] Install `pgvector` PostgreSQL extension; enable in migration
- [ ] Create `content_embeddings` migration + `ContentEmbedding` model (morphic, chunk_index, locale, embedding `vector(1536)`, metadata JSON)
- [ ] Install Prism PHP (`echolabsdev/prism`); configure Anthropic + OpenAI providers in `config/prism.php`
- [ ] Create `AiService` (`app/Services/AiService.php`) — single entry point for all Claude + OpenAI calls
- [ ] Create `RagService` (`app/Services/RagService.php`) — embed query → pgvector similarity search → context assembly
- [ ] Create `EmbeddingObserver` (`app/Observers/EmbeddingObserver.php`) — fires `GenerateEmbeddingJob` on model `saved`/`deleted`
- [ ] Create `GenerateEmbeddingJob` (`app/Jobs/GenerateEmbeddingJob.php`) — queued; chunks content, calls OpenAI `text-embedding-3-small`, upserts `content_embeddings`
- [ ] Register `EmbeddingObserver` on all embeddable models in `AppServiceProvider`: `Broadcast`, `Achievement`, `Policy`, `StaffDirectory`
- [ ] Add `AI_CHATBOT_ENABLED`, `ANTHROPIC_API_KEY`, `OPENAI_API_KEY` to `.env.example`
- [ ] Write `GenerateEmbeddingJobTest` + `RagServiceTest`

**Installation Commands:**

```bash
# pgvector extension (run in PostgreSQL)
psql -U postgres -d govportal -c "CREATE EXTENSION IF NOT EXISTS vector;"

# Prism PHP
composer require echolabsdev/prism

# Queue worker (for embedding jobs)
php artisan queue:work --queue=embeddings
```

**Deliverables:**
- `content_embeddings` table with pgvector column
- `php artisan db:seed --class=BroadcastSeeder` → rows appear in `content_embeddings`
- `RagServiceTest` passes (top-5 chunks returned for a test query)
- `GenerateEmbeddingJobTest` passes

**Effort:** 32 hours

---

#### Week 14: Public AI Chatbot

Build the `AiChat` Livewire component and integrate it into the public layout.

**Tasks:**
- [ ] Create `AiChat` Livewire component (`app/Livewire/AiChat.php` + `resources/views/livewire/ai-chat.blade.php`)
  - Properties: `$messages = []` (session conversation history), `$input = ''`, `$isThinking = false`
  - Method `send()`: validates input, embeds query via `RagService`, builds prompt, calls Claude via `AiService`, appends response to `$messages`
  - Rate limiting: 10 messages/hour per IP via `RateLimiter::attempt('ai-chat:' . $ip, 10, ...)`
  - Session-only history: store in PHP session, not DB; never log PII
- [ ] Create privacy disclaimer modal (Alpine.js) — shown on first open; acceptance stored in session
- [ ] Add `<livewire:ai-chat />` to `resources/views/components/layouts/app.blade.php`
- [ ] Style floating chat button + chat window with Tailwind (MyDS tokens)
- [ ] Bilingual support: system prompt adapts to `app()->getLocale()`
- [ ] Add `lang/ms/ai.php` + `lang/en/ai.php` for all AI-related UI strings
- [ ] Write `AiChatTest` (mock `AiService` + `RagService`)
- [ ] Add rate limit test: 11th message in same hour returns error response

**Deliverables:**
- Chat widget visible on `/ms` and `/en` pages
- Sends question → receives contextually accurate response (sourced from DB content)
- Rate limiting enforced (10/hour/IP)
- Privacy disclaimer shown on first open
- `AiChatTest` passes (all happy paths + rate limit)

**Effort:** 32 hours

---

#### Week 15: Admin AI Content Editor

Inject AI actions into existing Filament content resources.

**Tasks:**
- [ ] Create base Filament action classes in `app/Filament/Actions/Ai/`:
  - `AiGrammarAction` — grammar check BM or EN
  - `AiTranslateAction` — BM ↔ EN translation (from/to locale as constructor params)
  - `AiExpandAction` — expand selected text
  - `AiSummariseAction` — summarise field content
  - `AiTldrAction` — generate 2-3 sentence TLDR → fills `excerpt_{locale}` field
  - `AiGenerateAction` — generate from text prompt (modal)
  - `AiGenerateFromImageAction` — generate from image URL + prompt (modal)
- [ ] Inject relevant actions into these Filament resources: `BroadcastResource`, `AchievementResource`, `PolicyResource`
- [ ] Add `lang/ms/ai_admin.php` + `lang/en/ai_admin.php` for action labels and confirmations
- [ ] Write tests for each action class: `AiGrammarActionTest`, `AiTranslateActionTest`, etc. (mock `AiService`)
- [ ] Manual QA: each action button opens modal or replaces content inline

**Deliverables:**
- AI action buttons visible on `content_ms` / `content_en` fields in Broadcast, Achievement, Policy editors
- Each action calls Claude and returns a result within 10 seconds
- All action tests pass (mocked)

**Effort:** 32 hours

---

#### Week 16: AI QA, Performance & Monitoring

**Tasks:**
- [ ] Load test embedding job queue (target: 100 concurrent saves without job queue overflow)
- [ ] Benchmark chatbot response time (target: first token < 2 seconds, full response < 10 seconds)
- [ ] Add `ai_usage_logs` table (optional, anonymised): `operation`, `locale`, `duration_ms`, `tokens_used`, `created_at` — no user PII
- [ ] Monitor pgvector index size; add `ivfflat` index on `content_embeddings.embedding` if > 10,000 rows
- [ ] Verify PDPA compliance: no PII in embeddings (audit sample of 20 rows), no user data persisted in chat logs
- [ ] Update Cloudflare WAF rules to allow AI chat Livewire requests (bypass full-page cache)
- [ ] Add `AI_CHATBOT_ENABLED` feature flag check in `AiChat` component — disabled → hide widget gracefully
- [ ] Documentation: update `docs/ai.md` with final architecture, API key rotation guide, cost estimation

**Deliverables:**
- AI chatbot handles 50 concurrent users without degrading page performance
- pgvector index optimised
- PDPA audit passed
- `docs/ai.md` finalised

**Effort:** 32 hours

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

| Week | Milestone | Success Criteria |
|------|-----------|-----------------|
| 1 | ✅ Tooling Bootstrap | Laravel 12 + Octane + Filament + Boost + Blueprint all installed and verified — 2026-02-21 |
| 2 | ✅ Design System & Base UI | Alpine.js + MyDS tokens + theme system + nav/footer + RBAC roles + 5 passing tests — 2026-02-21 |
| 3 | ✅ Core Content Models | 6 models + Filament resources + 6 roles + 55 permissions + 53 tests passing — 2026-02-21 |
| 4 | ✅ Directory, Files & Site Config | 5 content models + 4 site config tables + 7 settings pages + 87 tests passing — 2026-02-21 |
| 5 | CMS Complete | All 12 collections manageable in Filament + My Profile page |
| 9 | All Pages Complete | All 10 public pages functional in ms/en |
| 11 | QA Complete | 90+ Lighthouse, WCAG AA, load test passed |
| 12 | Go Live | Site deployed, content migrated |

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
