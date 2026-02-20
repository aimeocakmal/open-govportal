# Agentic Coding Playbook

This document defines how coding agents should execute work in this repository. Read this before writing any code.

---

## Goals

- Minimise ambiguity before implementation.
- Keep scope aligned to kd-portal parity only.
- Require validation evidence before marking work complete.
- Keep documentation synchronised with code changes.

---

## Standard Task Format

Every task must have these fields before implementation starts. If any field is missing, resolve it before coding.

```
Objective:   [one sentence outcome]
Scope:       [files, routes, models, or modules in scope]
Out of Scope:[explicit exclusions]
Inputs:      [source references — kd-portal file path, doc section, schema table]
Outputs:     [files to create or modify]
Acceptance:  [testable, binary checks — pass or fail, no "looks good"]
Validation:  [exact commands + expected output or HTTP response]
Risks:       [migration safety, cache invalidation, N+1, locale coverage]
```

---

## Worked Example (Broadcast Model + Filament Resource)

This is a fully filled-out task. Use it as a template.

```
Objective:   Create the Broadcast Eloquent model, migration, and Filament resource
             so CMS editors can create and publish bilingual news items.

Scope:
  - database/migrations/xxxx_create_broadcasts_table.php
  - app/Models/Broadcast.php
  - app/Filament/Resources/BroadcastResource.php
  - app/Filament/Resources/BroadcastResource/Pages/ListBroadcasts.php
  - app/Filament/Resources/BroadcastResource/Pages/CreateBroadcast.php
  - app/Filament/Resources/BroadcastResource/Pages/EditBroadcast.php
  - database/seeders/BroadcastSeeder.php (3 sample rows)

Out of Scope:
  - BroadcastController (public-facing route — Week 7)
  - Search indexing (Week 5)
  - Cache invalidation observer (Week 5)
  - Any UI component or Blade view

Inputs:
  - docs/database-schema.md → "broadcasts" table definition
  - docs/pages-features.md → Section "2. Siaran" for field requirements
  - docs/conversion-timeline.md → Week 3 task list

Outputs:
  - Migration creates table matching schema in database-schema.md exactly
  - Model has $fillable, $casts, and a published() local scope
  - Filament resource has list (title_ms, type, status, published_at),
    form (all fillable fields), and filters (status, type)
  - Seeder inserts 3 rows: 1 draft, 2 published (ms + en fields populated)

Acceptance:
  [ ] Migration runs without error
  [ ] php artisan migrate:status shows broadcasts as Ran
  [ ] Filament /admin/broadcasts loads without 500 error
  [ ] Creating a broadcast from Filament saves to DB
  [ ] published() scope returns only status=published rows
  [ ] Both ms and en fields present in DB row

Validation:
  php artisan migrate
  php artisan db:seed --class=BroadcastSeeder
  php artisan test --filter=BroadcastResourceTest
  # Manual: visit /admin/broadcasts — expect list with 2 published, 1 draft

Risks:
  - None for a new table — no data to lose. Migration has a down() that drops the table.
  - Filament guard: ensure BroadcastResource uses 'web' guard, not 'api'.
```

---

## Execution Loop

1. **Understand**
   - Read `docs/README.md` for document order.
   - Read the relevant page section in `docs/pages-features.md`.
   - Read the relevant schema in `docs/database-schema.md`.
   - Identify any unresolved decisions. If found, stop and surface them — do not guess.

2. **Plan**
   - Break work into the smallest testable unit: one model, one migration, one route, or one view.
   - Sequence: migrations → models → Filament resource → controller → views → cache → tests → docs.
   - Never skip steps. Never combine unrelated models in one task.

3. **Implement**
   - Follow naming conventions in this document exactly.
   - Make only the changes required by the acceptance criteria.
   - Do not refactor surrounding code unless it directly blocks the task.

4. **Validate**
   - Run the exact commands from the Validation field.
   - Record the actual output, not the expected output.
   - If a test fails, fix it before marking any item done.

5. **Document**
   - Update `docs/pages-features.md`: change the affected section from `Planned` to `Implemented` with the test name or manual check that proves it.
   - Update any affected section in `docs/database-schema.md` if schema changed.
   - Remove any TODO or placeholder comment left in code.

---

## Naming Conventions

Consistent names are required. Agents must not invent alternatives.

### Models (`app/Models/`)

| Payload Collection | Laravel Model | File |
|-------------------|---------------|------|
| Broadcast | `Broadcast` | `app/Models/Broadcast.php` |
| Achievement | `Achievement` | `app/Models/Achievement.php` |
| Celebration | `Celebration` | `app/Models/Celebration.php` |
| Directory | `StaffDirectory` | `app/Models/StaffDirectory.php` |
| Feedback | `Feedback` | `app/Models/Feedback.php` |
| File | `PolicyFile` | `app/Models/PolicyFile.php` |
| HeroBanner | `HeroBanner` | `app/Models/HeroBanner.php` |
| Media | `Media` | `app/Models/Media.php` |
| Policy | `Policy` | `app/Models/Policy.php` |
| QuickLink | `QuickLink` | `app/Models/QuickLink.php` |
| Search-Overrides | `SearchOverride` | `app/Models/SearchOverride.php` |

### Route Files (`routes/`)

The route system is split into four focused files. Always add new routes to the correct file — never add public page routes directly to `web.php`.

| File | Middleware group | URL prefix | Purpose |
|------|-----------------|------------|---------|
| `routes/web.php` | `web` | — | Root `/` redirect only; `require`s `public.php` and `admin.php` |
| `routes/public.php` | `web` + `setlocale` | `/{locale}` | All 10 public pages + `/carian` search |
| `routes/admin.php` | `web` + `auth` (when needed) | `/admin` | Custom admin actions beyond Filament |
| `routes/api.php` | `api` (stateless, `throttle:api`) | `/api/v1` | REST API endpoints; Sanctum auth in Phase 4 |

**Rules:**
- Adding a new public page route → `routes/public.php` only. Uncomment the prepared stub for that page.
- Adding a custom admin endpoint → `routes/admin.php` with `middleware(['auth', 'role:...'])`.
- Adding an API endpoint → `routes/api.php`. Use `Route::apiResource()` or explicit `Route::get/post`.
- Never put a public route in `web.php`. Never put a page route in `api.php`.

Both `routes/public.php` and `routes/admin.php` are loaded via `require` inside `web.php` and therefore inherit the `web` middleware group (session, CSRF, `ApplyTheme`). The `api.php` file is loaded separately in `bootstrap/app.php` with its own `api` group.

### Controllers (`app/Http/Controllers/`)

**Public controllers** (`app/Http/Controllers/` — registered in `routes/public.php`):

| Route | Controller | Method |
|-------|-----------|--------|
| `GET /{locale}` | `HomeController` | `index` |
| `GET /{locale}/siaran` | `BroadcastController` | `index` |
| `GET /{locale}/siaran/{slug}` | `BroadcastController` | `show` |
| `GET /{locale}/pencapaian` | `AchievementController` | `index` |
| `GET /{locale}/pencapaian/{slug}` | `AchievementController` | `show` |
| `GET /{locale}/statistik` | `StatistikController` | `index` |
| `GET /{locale}/direktori` | `DirectoriController` | `index` |
| `GET /{locale}/dasar` | `DasarController` | `index` |
| `GET /{locale}/dasar/{id}/muat-turun` | `DasarController` | `download` |
| `GET /{locale}/profil-kementerian` | `ProfilKementerianController` | `index` |
| `GET /{locale}/hubungi-kami` | `HubungiKamiController` | `index` |
| `GET /{locale}/penafian` | `StaticPageController` | `penafian` |
| `GET /{locale}/dasar-privasi` | `StaticPageController` | `dasarPrivasi` |
| `GET /{locale}/carian` | `SearchController` | `index` |

**API controllers** (`app/Http/Controllers/Api/` — registered in `routes/api.php`, Phase 4):

| Route | Controller | Method |
|-------|-----------|--------|
| `GET /api/v1/broadcasts` | `Api\BroadcastController` | `index` |
| `GET /api/v1/broadcasts/{slug}` | `Api\BroadcastController` | `show` |
| `GET /api/v1/achievements` | `Api\AchievementController` | `index` |
| `GET /api/v1/staff-directory` | `Api\StaffDirectoryController` | `index` |
| `GET /api/v1/policies` | `Api\PolicyController` | `index` |
| `GET /api/v1/search` | `Api\SearchController` | `index` |
| `POST /api/v1/feedback` | `Api\FeedbackController` | `store` |

### Filament Resources (`app/Filament/Resources/`)

Naming pattern: `{Model}Resource.php`

Examples: `BroadcastResource`, `AchievementResource`, `StaffDirectoryResource`

### Livewire Components (`app/Livewire/`)

One class per interactive component. Naming: `PascalCase`, file: `app/Livewire/{Name}.php`.

| Class | File | View |
|-------|------|------|
| `SiaranList` | `app/Livewire/SiaranList.php` | `resources/views/livewire/siaran-list.blade.php` |
| `PencapaianList` | `app/Livewire/PencapaianList.php` | `resources/views/livewire/pencapaian-list.blade.php` |
| `DirectoriSearch` | `app/Livewire/DirectoriSearch.php` | `resources/views/livewire/direktori-search.blade.php` |
| `ContactForm` | `app/Livewire/ContactForm.php` | `resources/views/livewire/contact-form.blade.php` |
| `SearchResults` | `app/Livewire/SearchResults.php` | `resources/views/livewire/search-results.blade.php` |

**Rules for Livewire components:**
- Always declare `public` properties for `wire:model` bindings
- Always include validation rules in `$rules` or using `#[Validate]` attributes
- Use `#[Computed]` for expensive derived properties (not `public` props that re-query on every update)
- Never access `Request` directly in a Livewire component — use component properties only
- Wrap database queries in `#[Computed]` with `->cache()` where appropriate for Octane compatibility

**Octane + Livewire safety:**
- Do not use `static` properties in Livewire components — workers share memory
- Do not store non-serializable objects (closures, PDO connections) as component properties
- Test Livewire components with `php artisan octane:start` running, not just `artisan serve`

### Blade Views (`resources/views/`)

```
resources/views/
  components/
    layouts/                    ← Blade layout components (used as <x-layouts.app>)
      app.blade.php             ← main public layout; sets <html data-theme="...">
      guest.blade.php           ← minimal layout (no nav/footer)
    layout/                     ← shared UI components (used as <x-layout.nav> etc.)
      nav.blade.php             ← sticky header, hamburger (Alpine.js), language switcher
      footer.blade.php
      theme-switcher.blade.php  ← Alpine.js cookie-based theme switcher
    home/
      hero-banner.blade.php
      quick-links.blade.php
      broadcast-card.blade.php
      achievement-card.blade.php
    siaran/
      broadcast-card.blade.php
    pencapaian/
      achievement-card.blade.php
    direktori/
      staff-card.blade.php
    dasar/
      policy-card.blade.php
  livewire/                     ← Livewire component views (one per Livewire class)
    siaran-list.blade.php
    pencapaian-list.blade.php
    direktori-search.blade.php
    contact-form.blade.php
    search-results.blade.php
  home/
    index.blade.php
  siaran/
    index.blade.php             ← embeds <livewire:siaran-list />
    show.blade.php
  pencapaian/
    index.blade.php             ← embeds <livewire:pencapaian-list />
    show.blade.php
  statistik/
    index.blade.php
  direktori/
    index.blade.php             ← embeds <livewire:direktori-search />
  dasar/
    index.blade.php
  profil-kementerian/
    index.blade.php
  hubungi-kami/
    index.blade.php             ← embeds <livewire:contact-form />
  static/
    penafian.blade.php
    dasar-privasi.blade.php
  carian/
    index.blade.php             ← embeds <livewire:search-results />
```

### Language Files (`lang/`)

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
    (same files)
```

### Migrations

Pattern: `{timestamp}_create_{table}_table.php` for new tables, `{timestamp}_add_{column}_to_{table}_table.php` for columns.

Never use `--force` on existing migrations. Always include a `down()` method that reverses the `up()`.

### Cache Tags

Use exactly the tag names from `docs/pages-features.md → Cache Tag → Route / Model Mapping`.

Do not invent new tag names without updating that table.

---

## Anti-Patterns

These are common agent failure modes. Do not do any of these.

**Scope drift:**
Do not build the public controller when the task only asks for the model. Do not add extra fields "while you're there." Complete exactly what the task asks.

**Fake validation:**
Do not mark acceptance criteria as passing without running the commands. Do not write "test passes" if you did not execute the test runner. Record the actual command output.

**Unresolved OR:**
If the docs say "X or Y," stop. Look in `docs/pages-features.md → Resolved Implementation Decisions`. If it is listed there, use that decision. If it is not listed, surface the ambiguity before coding.

**Premature abstraction:**
Do not create a base controller, a trait, or a service class "for reuse" unless two or more concrete implementations already exist. Start simple.

**Wrong layer for interactivity:**
Do not use `fetch()` or `axios` in Alpine.js to retrieve application data. If a component needs server data, it is a Livewire component. Alpine.js is for DOM interactions (toggle, show/hide, init a library) only.

**Static properties in Livewire + Octane:**
Do not use `static` properties inside Livewire components. Octane workers share memory across requests; static state will leak between users.

**Missing locale coverage:**
Every public route must work for both `/ms/` and `/en/`. A feature test that only tests one locale is incomplete.

**Silent cache bypass:**
Do not remove a `Cache::remember` call to "make testing easier." If cache makes testing hard, mock the cache in tests.

**Leaving TODOs in code:**
Every `// TODO` left in committed code is a broken promise. Either do it now or open a tracked task. Do not commit TODOs.

**Hallucinated file paths:**
Only reference files that exist in the repository. Do not reference `docs/installation.md`, `docs/rbac.md`, or other docs listed in the old README — they do not exist. Check with `Glob` before referencing.

---

## Per-Feature Validation Reference

Use the smallest set of commands that proves correctness.

### Migration / Model

```bash
php artisan migrate
php artisan migrate:status
# Expect: new table shows "Ran"

php artisan tinker --execute="App\Models\Broadcast::count()"
# Expect: integer (not exception)
```

### Filament Resource

```bash
php artisan filament:check-translations
# Optional; skip if not configured

# Manual: visit /admin/{resource-slug} as super_admin user
# Expect: list page loads, create form opens, record saves
```

### Public Route + Controller

```bash
php artisan test --filter=BroadcastControllerTest
# Expect: all assertions pass

curl -s -o /dev/null -w "%{http_code}" http://govportal.test/ms/siaran
# Expect: 200

curl -s -o /dev/null -w "%{http_code}" http://govportal.test/en/siaran
# Expect: 200
```

### Cache

```bash
php artisan cache:clear

curl -s http://govportal.test/ms/siaran > /dev/null  # cold request
curl -s http://govportal.test/ms/siaran > /dev/null  # should hit cache

php artisan tinker --execute="Cache::has('page:/ms/siaran')"
# Expect: true
```

### Search Indexing

```bash
php artisan scout:import "App\Models\Broadcast"
# Expect: no errors, record count printed

php artisan tinker --execute="App\Models\Broadcast::search('digital')->get()->count()"
# Expect: integer >= 0
```

### Livewire Component

```bash
php artisan make:livewire ComponentName
# Creates: app/Livewire/ComponentName.php + resources/views/livewire/component-name.blade.php

php artisan test --filter=ComponentNameTest
# Expect: all assertions pass

# Manual: visit the page containing the component, verify wire:model and wire:click work
# Verify with Octane running (not just artisan serve):
php artisan octane:start --watch
# Navigate to /{locale}/page — check for Livewire hydration errors in browser console
```

### API Endpoint

```bash
# Health check (no auth)
curl -s http://govportal.test/api/v1/health
# Expect: {"status":"ok"}

# Authenticated endpoint (Phase 4 — Sanctum token required)
curl -s -H "Authorization: Bearer {token}" http://govportal.test/api/v1/broadcasts
# Expect: paginated JSON with data[], links{}, meta{}

php artisan test --filter=BroadcastApiTest
# Expect: all assertions pass

# Rate limit smoke test
for i in {1..5}; do curl -s -o /dev/null -w "%{http_code}\n" http://govportal.test/api/v1/health; done
# Expect: all 200 (rate limit is 60/min by default for /api/v1/)
```

### Contact Form (Email + Livewire)

```bash
# Set MAIL_MAILER=log in .env for local testing
php artisan test --filter=ContactFormTest
# Expect: passes; storage/logs/laravel.log contains "To: {email}"

# Test Livewire validation inline feedback:
php artisan test --filter=ContactFormValidationTest
# Expect: submitting empty form shows validation errors without page reload
```

---

## Definition of Done

A task is done only when **all** are true:

- [ ] Acceptance criteria are satisfied — each checked with the validation command.
- [ ] Tests were run and output is recorded (pass or fail logged, not assumed).
- [ ] No `// TODO` comments left in any committed file.
- [ ] No unresolved contradictions between docs and code.
- [ ] Any new route is listed in the Naming Conventions table above.
- [ ] Any new model is covered in `docs/database-schema.md`.
- [ ] `docs/pages-features.md` status updated: `Planned` → `Implemented` for affected section.
- [ ] Cache, i18n, and security implications acknowledged in the task summary.

---

## Required Quality Gates

- **Correctness:** behaviour matches the documented route/model contract in `docs/pages-features.md`.
- **Safety:** no private data (email, phone, user ID) leaks in cache keys, logs, or public API responses.
- **Performance:** no N+1 queries. Use `->with()` eager loading. Verify with `DB::enableQueryLog()` in tests.
- **Locale coverage:** every public route tested for both `ms` and `en` locale.
- **Operability:** failures produce log entries at `ERROR` level. No silent swallowing of exceptions.
- **Recoverability:** every migration has a working `down()` method. Test with `php artisan migrate:rollback`.

---

## Documentation Update Policy

- Update `docs/pages-features.md` in the same PR as any route, controller, or view change.
- Update `docs/database-schema.md` in the same PR as any migration.
- Remove stale references instead of leaving TODO links.
- Prefer exact file paths and concrete examples over narrative-only guidance.
- If you discover a doc is wrong, fix it immediately and note the correction in your PR description.
