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
| Public AI chatbot | **Livewire component `AiChat`** — RAG via pgvector + admin-configured LLM; rate-limited (configurable) | Server-side streaming; no client-side fetch; session-only history; hidden if not configured |
| Admin AI editor | **Filament custom actions** on RichEditor — grammar check, translate BM/EN, expand, summarise, TLDR, generate | Synchronous LLM calls (any configured provider); hidden if `ai_admin_editor_enabled = false` |
| AI provider config | **`ManageAiSettings`** Filament settings page — LLM provider, model, API key, base URL; embedding provider, model, key; feature flags | Provider-agnostic: Anthropic, OpenAI, Google, Groq, Mistral, Ollama, OpenAI-compatible (Qwen, Moonshot, etc.) |
| RAG embedding pipeline | **Observer → queued Job**: `EmbeddingObserver` dispatches `GenerateEmbeddingJob` on model save | Async; admin-configured embedding provider via Prism PHP; dimension must match pgvector column |
| Vector storage | **pgvector** in existing PostgreSQL cluster — `content_embeddings` table | No separate vector DB; cosine similarity search; re-index required if embedding dimension changes |
| Static page management | **`StaticPage` model + `StaticPageResource`** — all CMS-managed static pages stored in `static_pages` table; served via `StaticPageController@show` catch-all at `/{locale}/{slug}` | Penafian and Dasar Privasi become rows in `static_pages`; no longer in `settings` table |
| Page categories | **`PageCategory` model** with self-referential `parent_id` — unlimited nesting depth enforced at app layer; used by `StaticPage` | Managed via `PageCategoryResource` in Filament with tree view |
| Menu system | **`Menu` + `MenuItem` models** replace `navigation_items` table and `ManageHeader` settings page | 4-level mega menu; manages both public and admin page navigation; role-based visibility per item |
| Site settings management | **Four Filament settings pages**: `ManageSiteInfo` (branding, logo, favicon, social URLs, GA ID, theme), `ManageEmailSettings` (mail driver, SMTP config), `ManageMediaSettings` (storage driver + cloud credentials), `ManageAiSettings` (AI provider, model, system prompt, feature flags) — all stored in `settings` table | Admins configure site without touching `.env`; sensitive values (`mail_password`, cloud keys, AI keys) stored via `Crypt::encrypt()` with `type = 'encrypted'` |
| Media storage | **`ManageMediaSettings`** Filament page — admin selects disk driver (`local`, `s3`, `r2`, `gcs`, `azure`) and fills credentials for the active provider only; `SettingObserver` applies disk config at runtime without Octane restart | Supports: local filesystem, AWS S3, Cloudflare R2 (S3-compatible), GCP Cloud Storage, Azure Blob Storage; each provider's credentials shown/hidden via Filament conditional field visibility |
| User management | **`UserResource`** in Filament — create/edit/deactivate CMS users, assign Spatie roles, `department` field scopes `department_admin` access, last-login display, admin password reset action | Single resource manages all Filament admin accounts |
| Role management | **`RoleResource`** wrapping Spatie Permission — CRUD for roles, checkbox-based permission assignment; **6 roles seeded**: `super_admin`, `department_admin`, `content_editor`, `content_author`, `publisher`, `viewer` | Uses `spatie/laravel-permission` (installed Week 2); `viewer` = "regular user" with read-only access |

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

**Laravel route:** `GET /{locale}/penafian` → served by the generic static page catch-all route `GET /{locale}/{slug}` → `StaticPageController@show`
**Source:** `static_pages` table, row with `slug = 'penafian'`. Managed via `StaticPageResource` in Filament.

**Cache:** Tag `static-pages`, TTL 24 hours.

**Blade view:** `resources/views/static/show.blade.php` (shared static page template)

---

### 10. Dasar Privasi (`/dasar-privasi`) — **Status: Planned**

**Laravel route:** `GET /{locale}/dasar-privasi` → `StaticPageController@show` (catch-all)
**Source:** `static_pages` table, row with `slug = 'dasar-privasi'`. Managed via `StaticPageResource` in Filament.

**Cache:** Tag `static-pages`, TTL 24 hours.

**Blade view:** `resources/views/static/show.blade.php`

---

## Global Components

### Navigation / Header — **Status: Planned**

**Blade component:** `resources/views/components/layout/navbar.blade.php`
**Data source:** `menu_items` table, `menu_id` where `menus.name = 'public_header'`

**Mega Menu — 4 Levels:**

```
Level 1 (Main menu)       → top-level items (parent_id IS NULL) in public_header menu
  Level 2 (Sub menu)      → children of Level 1
    Level 3 (Inner menu)  → children of Level 2
      Level 4 (Child menu)→ children of Level 3 (leaf nodes — no further nesting)
```

**Elements:**
- Ministry logo (from `settings.site_logo`)
- 4-level mega menu (from `menu_items` table, tree loaded server-side, cached)
- Language switcher: `ms` / `en` (preserves current path, swaps locale segment)
- Mobile hamburger menu (Alpine.js toggle — collapses all levels into accordion)
- Search icon → opens search overlay
- **Role-based visibility:** menu items with `required_roles` not empty are hidden unless the current user has one of the required roles (public menu items typically have `required_roles = null` = visible to all)

**Rendering:**
- Tree built server-side in `AppServiceProvider` → shared as `$publicMenu` view variable
- Alpine.js for hover/click interactions (dropdown open, mega panel show) — no server calls
- Pure Blade iteration over the pre-built nested tree

**Cache:** Tag `navigation`, TTL 24 hours. Invalidate on any `MenuItem` or `Menu` save.

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

### Static Pages (`/{locale}/{slug}`) — **Status: Planned**

**Laravel route:** `GET /{locale}/{slug}` → `StaticPageController@show` (catch-all, must be the **last** route in the `/{locale}/` prefix group so named routes are matched first)
**Model:** `StaticPage` — managed in Filament via `StaticPageResource`

**Features:**
- Any number of CMS-managed pages (not limited to Penafian/Dasar Privasi)
- Bilingual content (`title_ms`, `title_en`, `content_ms`, `content_en`)
- Optional page category (`page_category_id` FK → `page_categories`)
- SEO fields: `meta_title_{locale}`, `meta_desc_{locale}`
- Draft/published status; excluded from sitemap if `is_in_sitemap = false`
- 404 response if slug not found or status is `draft`

**Cache:** Tag `static-pages`, TTL 24 hours. Invalidate on `StaticPage` save.

**Blade view:** `resources/views/static/show.blade.php` (single shared template for all static pages)

**Seeded rows:** `penafian`, `dasar-privasi` (migrated from `settings` table; add more as needed)

---

### Menu Management — **Status: Planned**

**Models:** `Menu` (registry) + `MenuItem` (individual items)
**Managed via:** `MenuResource` in Filament (replaces `ManageHeader` settings page)

**Named menus (pre-seeded in `menus` table):**

| `menus.name` | Purpose |
|-------------|---------|
| `public_header` | Public site mega menu (4-level) |
| `public_footer` | Footer navigation links |
| `admin_sidebar` | Extra navigation items in Filament admin sidebar |

**4-Level menu structure (public_header):**

```
Level 1 — Main menu       (parent_id IS NULL in menu_items)
  Level 2 — Sub menu      (children of Level 1)
    Level 3 — Inner menu  (children of Level 2)
      Level 4 — Child menu (children of Level 3 — leaf nodes, no further nesting allowed)
```

**Per-item configuration:**
- `label_ms` / `label_en` — bilingual label
- `url` — external link (absolute URL)
- `route_name` + `route_params` (JSON) — internal named route (use `route()` helper)
- `icon` — Heroicon or icon identifier (used in admin sidebar and mobile nav)
- `sort_order` — drag-and-drop ordering in Filament
- `target` — `_self` or `_blank`
- `is_active` — toggleable without deleting
- `required_roles` (JSON array, nullable) — which Spatie roles can **see** this item:
  - `null` → visible to everyone (public users included)
  - `["super_admin", "publisher"]` → only those Filament roles see it in admin nav
  - For public menu items: role filtering applies when logged-in users view the portal
- `mega_columns` (int, Level 1 items only) — number of columns for Level 2 children in the mega panel (1–4)

**Rendering logic:**
- Public site: `AppServiceProvider` loads `public_header` tree → shared as `$publicMenu`; Alpine.js for hover panels
- Admin sidebar: Filament `PanelProvider` reads `admin_sidebar` items and registers them as `NavigationItem` objects filtered by the current user's roles
- Role filter applied server-side — hidden items never reach the DOM

**Cache:** Tag `navigation`, TTL 24 hours. Invalidate on any `Menu` or `MenuItem` save.

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

### AI Chatbot — **Status: Planned** (Phase 6)

**Livewire component:** `App\Livewire\AiChat`
**View:** `resources/views/livewire/ai-chat.blade.php`

Floating chat widget rendered on all public pages via the base layout (`<livewire:ai-chat />`). Answers questions using RAG retrieval from embedded content.

**User-facing behaviour:**
- Floating "Chat with us" button (bottom-right) — Alpine.js toggle for open/close
- Chat window shows conversation history (session-only — not persisted)
- User message submitted via Livewire `wire:submit`
- Response streams from the **admin-configured LLM provider** via Prism PHP (Livewire streaming)
- Bilingual: responds in the same locale as the current page (`app()->getLocale()`)

**RAG pipeline (per message):**
1. Embed user message via admin-configured embedding provider (via `AiService::embed()`)
2. pgvector similarity search on `content_embeddings` — top 5 chunks, filtered by locale
3. Build system prompt: "You are the AI assistant for Kementerian Digital Malaysia. Answer using only the provided context."
4. Call admin-configured LLM via `AiService::chat()` (Prism PHP) + conversation history (last 10 turns)
5. Return response; append to session history

**Rate limiting:** 10 messages/hour per IP via Redis rate limiter (Laravel `RateLimiter` facade).

**Privacy disclaimer:** First-time chatbot open shows a modal: "Your messages may be processed by an AI provider. Do not share personal information." Acceptance stored in session.

**Cache:** No cache — responses are dynamic per user query.

**Blade layout placement:**
```blade
{{-- resources/views/components/layouts/app.blade.php --}}
<livewire:ai-chat />  {{-- renders floating chat widget on all public pages --}}
```

---

## Admin Panel (Filament) — **Status: Planned**

Replaces Payload CMS admin at `/admin`.

### Filament Resources

| Resource | Model | Payload Collection / Source | Status |
|----------|-------|-----------------------------|--------|
| `BroadcastResource` | `Broadcast` | Broadcast | **Implemented** — Week 3; tests: `BroadcastTest` (8 tests) |
| `AchievementResource` | `Achievement` | Achievement | **Implemented** — Week 3; tests: `AchievementTest` (8 tests) |
| `CelebrationResource` | `Celebration` | Celebration | **Implemented** — Week 3; tests: `CelebrationTest` (7 tests) |
| `StaffDirectoryResource` | `StaffDirectory` | Directory | Planned |
| `PolicyResource` | `Policy` | Policy | **Implemented** — Week 3; tests: `PolicyTest` (9 tests) |
| `PolicyFileResource` | `PolicyFile` | File | Planned |
| `HeroBannerResource` | `HeroBanner` | HeroBanner | **Implemented** — Week 3; tests: `HeroBannerTest` (7 tests) |
| `QuickLinkResource` | `QuickLink` | QuickLink | **Implemented** — Week 3; tests: `QuickLinkTest` (7 tests) |
| `MediaResource` | `Media` | Media | Planned |
| `FeedbackResource` | `Feedback` | Feedback (read-only) | Planned |
| `SearchOverrideResource` | `SearchOverride` | Search-Overrides | Planned |
| `UserResource` | `User` | Users | Planned |
| `StaticPageResource` | `StaticPage` | New — CMS-managed static pages | Planned |
| `PageCategoryResource` | `PageCategory` | New — hierarchical categories for static pages | Planned |
| `MenuResource` | `Menu` + `MenuItem` | New — mega menu with 4-level nesting and role visibility | Planned |
| `RoleResource` | `Role` (Spatie Permission) | New — manage roles and assign permissions for all CMS users | Planned |

### Filament Settings Pages (Globals)

| Class | Replaces Payload Global | Key Fields | Status |
|-------|------------------------|------------|--------|
| `ManageSiteInfo` | SiteInfo | Site name (ms/en), description, logo (S3), dark-mode logo (S3), favicon (S3), social URLs (FB/Twitter/IG/YouTube), GA tracking ID, default theme | Planned |
| `ManageEmailSettings` | — (CMS extension) | Mail driver (ses/smtp/mailgun/log), SMTP host/port/username/password (encrypted)/encryption, from address, from name (ms/en) | Planned |
| ~~`ManageHeader`~~ | ~~Header (navigation items)~~ | — | **Replaced** by `MenuResource` |
| `ManageFooter` | Footer | Footer link sections (label ms/en + URL, grouped by `section`), social media links | Planned |
| `ManageHomepage` | Homepage | Homepage layout flags and section ordering | Planned |
| `ManageMinisterProfile` | MinisterProfile | Minister photo (S3), name, title (ms/en), bio (ms/en), appointment date | Planned |
| `ManageAddresses` | Addresses | Ministry office addresses with phone/fax/email/Google Maps URL | Planned |
| `ManageFeedbackSettings` | FeedbackSettings | Enable/disable feedback widget, recipient email, success message (ms/en) | Planned |
| `ManageMediaSettings` | — (CMS extension) | Storage driver (local/s3/r2/gcs/azure); AWS S3 key/secret/region/bucket/URL; Cloudflare R2 account ID/keys/bucket/public URL; GCP project/bucket/service-account JSON; Azure account/key/container/URL | Planned |
| `ManageAiSettings` | — (Phase 6) | LLM provider/model/API key (encrypted)/base URL, system prompt (ms/en), embedding provider/model/key (encrypted)/dimension, chatbot rate limit, feature flags | Planned |

> **Note:** `ManageHeader` is **not built** — the `MenuResource` Filament resource handles all menu management (public and admin navigation) through the `menus` + `menu_items` tables, providing richer nesting and role control than a settings page would allow.

> **`ManageEmailSettings` note:** When `mail_mailer = ses`, SMTP credential fields are unused — SES uses AWS IAM credentials from `.env`. A `SettingObserver` calls `Config::set('mail.*')` after any save so changes apply to running Octane workers without restart.

> **`ManageMediaSettings` note:** Only credentials for the active `media_disk` driver are shown (Filament `hidden()` conditional). Cloudflare R2 reuses the AWS S3 Flysystem adapter with `endpoint` set to `https://{account_id}.r2.cloudflarestorage.com`. GCP requires `league/flysystem-google-cloud-storage`; Azure requires `league/flysystem-azure-blob-storage` — install only what's needed. The `SettingObserver` rebuilds `Config::set('filesystems.disks.active_media', [...])` on save.

### Admin AI Content Editor — **Status: Planned** (Phase 6)

AI-assisted editing is integrated as **Filament custom actions** on RichEditor and Textarea fields in all content resources. No new Filament resource is created — actions are injected into existing form schemas.

**Resources that receive AI actions:** `BroadcastResource`, `AchievementResource`, `PolicyResource`, `HeroBannerResource` (any resource with `title_{locale}` or `content_{locale}` fields).

**Available AI actions (per RichEditor field):**

| Action label | Operation | Input | Output |
|-------------|-----------|-------|--------|
| **Semak Tatabahasa BM** | Grammar check (Bahasa Malaysia) | Field content | Corrected text with change summary |
| **Grammar Check (EN)** | Grammar check (English) | Field content | Corrected text with change summary |
| **Terjemah → EN** | Translate BM → EN | `content_ms` field | Fills `content_en` field |
| **Terjemah → BM** | Translate EN → BM | `content_en` field | Fills `content_ms` field |
| **Kembangkan** | Expand / elaborate | Selected text | Longer, more detailed version |
| **Ringkaskan** | Summarise | Field content | Condensed version |
| **Jana TLDR** | Auto TLDR | `content_{locale}` field | 2-3 sentence summary → fills `excerpt_{locale}` |
| **Jana daripada Prompt** | Generate from text prompt | Modal: text prompt + locale | Draft content inserted into field |
| **Jana daripada Imej** | Generate from image | Modal: image URL/upload + prompt | Caption or content based on image |

**Implementation pattern:**
```php
// Inside BroadcastResource::form()
Forms\Components\RichEditor::make('content_ms')
    ->hintAction(
        AiGrammarAction::make('grammar_ms')->locale('ms')
    )
    ->hintAction(
        AiTranslateAction::make('translate_to_en')->from('ms')->to('en')
    )
    // ...
```

**Service class:** `App\Services\AiService` — single entry point for all Prism PHP / Claude calls.
**Action classes:** `app/Filament/Actions/Ai/` — one class per operation, injected as Filament actions.

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
| `AiChat` | `livewire/ai-chat.blade.php` | All public pages (floating widget) |

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
| `Menu` | `navigation` |
| `MenuItem` | `navigation` |
| `FooterSetting` | `footer` |
| `Setting` (site info) | `navigation`, `footer`, `profil-kementerian`, `static-pages` |
| `MinisterProfile` | `profil-kementerian` |
| `Address` | `hubungi-kami` |
| `Setting` (`site_default_theme` key) | `navigation` |
| `StaticPage` | `static-pages`, `static-page:{slug}` |
| `PageCategory` | `static-pages` |
| Any embeddable model saved | No page cache tag — triggers `GenerateEmbeddingJob` instead (pgvector update) |

Implement invalidation via a `CacheObserver` registered on each model in `AppServiceProvider`.

**Note on AI chatbot:** The `AiChat` Livewire component bypasses all Redis page caching — responses are always dynamically generated per user query.

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
