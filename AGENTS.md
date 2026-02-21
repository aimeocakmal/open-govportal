# OpenGovPortal — Agent Instructions

This file is intended for any agentic AI coding tool. Read it before doing any work in this repository.

---

## What This Project Is

A Laravel 12 recreation of https://www.digital.gov.my/ — the official website of Kementerian Digital Malaysia.

The source to replicate is https://github.com/govtechmy/kd-portal (Next.js 15 + Payload CMS + MongoDB).

**Scope constraint:** Build only what exists in kd-portal, **plus the approved AI extension** (public AI chatbot + admin AI content editor). All other features require explicit approval before adding.

---

## Must-Read Docs Before Coding

Always read these in order before starting any task:

1. [docs/agentic-coding.md](docs/agentic-coding.md) — execution rules, naming conventions, anti-patterns, validation commands
2. [docs/pages-features.md](docs/pages-features.md) — all 10 pages, their routes, data sources, status labels, and resolved decisions
3. [docs/database-schema.md](docs/database-schema.md) — all PostgreSQL tables and their exact column definitions
4. [docs/conversion-timeline.md](docs/conversion-timeline.md) — 12-week plan and slice template

---

## Stack — TALL

This project uses the full **TALL stack**: Tailwind CSS, Alpine.js, Laravel, Livewire.

| Layer | Technology | Notes |
|-------|-----------|-------|
| Framework | Laravel 12 | |
| Performance | Laravel Octane + **FrankenPHP** | Not Swoole — see architecture.md for rationale |
| Admin CMS | Filament v5.x | Filament already bundles Livewire 4 |
| Styling | Tailwind CSS v4.x | MyDS design tokens; CSS-first config via `@theme` |
| Reactivity | Livewire 4 | Server-side components for all interactive pages |
| Micro-interactions | Alpine.js v3.x | UI-only: carousel, mobile menu, dropdowns, modals |
| Templates | Blade | All views; Livewire components extend Blade |
| Database | PostgreSQL | |
| Cache | Redis (tagged cache) | |
| File storage | **Admin-configurable**: local filesystem, AWS S3, Cloudflare R2, GCP Cloud Storage, Azure Blob Storage | `ManageMediaSettings` Filament page; credentials in `settings` table (encrypted); active disk applied at runtime via `Config::set()` |
| Email | AWS SES (Laravel SES mail driver) | |
| Auth/RBAC | Spatie Laravel Permission | |
| Search | PostgreSQL FTS via `searchable_content` table | |
| Charts | Chart.js | Not Recharts, not ApexCharts |
| Carousel | Alpine.js + Embla.js (vanilla) | Alpine only for UI, no server state |
| Agentic tooling | Laravel Boost v2.x | AI coding agent integration for agentic workflows |
| AI framework | Prism PHP (`echolabsdev/prism`) | Unified interface for all AI provider calls (LLM + embeddings) |
| AI provider | **Admin-configurable** via `ManageAiSettings` | Anthropic, OpenAI, Google Gemini, Groq, Mistral, Ollama, or any OpenAI-compatible endpoint (Qwen, Moonshot, DeepSeek, …) |
| Vector storage | pgvector (PostgreSQL extension) | `content_embeddings` table; no separate vector DB; dimension configured via `PGVECTOR_DIMENSION` |

### Octane server: FrankenPHP

FrankenPHP replaces both Swoole **and** Nginx. Caddy is built in.

```
Cloudflare CDN → FrankenPHP/Caddy → Laravel workers → Redis + PostgreSQL
```

Do **not** use `Octane::table()` — it is Swoole-only and not available in FrankenPHP. Use Redis for all shared state.

### Frontend layer rule

Use the **most server-side approach possible**:

1. **Pure Blade** — static content pages (no user input, no filtering)
2. **Livewire component** — any page with search, filtering, pagination, or form submission
3. **Alpine.js** — UI micro-interactions only (toggle, show/hide, carousel init, dropdown open)
4. **Never** — Inertia.js, React, Vue, or any SPA approach

---

## Locales

- `ms` (ms-MY) — Bahasa Malaysia, **default**
- `en` (en-GB) — English

All public routes are locale-prefixed: `/{locale}/...`

Every feature must work for both locales. A task is not done until both are tested.

---

## Naming Rules (non-negotiable)

### Models
Follow the Payload collection → Laravel model mapping exactly:

| Payload / Source | Laravel |
|------------------|---------|
| Broadcast | `Broadcast` |
| Achievement | `Achievement` |
| Celebration | `Celebration` |
| Directory | `StaffDirectory` |
| Feedback | `Feedback` |
| File | `PolicyFile` |
| HeroBanner | `HeroBanner` |
| Media | `Media` |
| Policy | `Policy` |
| QuickLink | `QuickLink` |
| Search-Overrides | `SearchOverride` |
| New — CMS static pages | `StaticPage` |
| New — hierarchical page categories | `PageCategory` |
| New — menu registry | `Menu` |
| New — 4-level menu items with role visibility | `MenuItem` |

### Controllers
Match the route-to-controller table in [docs/agentic-coding.md](docs/agentic-coding.md) exactly. Do not rename.

### Route files
Add new routes to the correct file — never mix concerns:

| File | For |
|------|-----|
| `routes/public.php` | All `/{locale}/...` public pages |
| `routes/admin.php` | Custom admin endpoints beyond Filament |
| `routes/api.php` | REST API under `/api/v1/` |
| `routes/web.php` | Root redirect only — do not add routes here |

Full rules: [docs/agentic-coding.md → Route Files](docs/agentic-coding.md).

### Blade views
Match the view directory structure in [docs/agentic-coding.md](docs/agentic-coding.md) exactly.

### Cache tags
Use only the tag names defined in [docs/pages-features.md](docs/pages-features.md) under "Cache Tag → Route / Model Mapping". Do not invent new tags.

---

## Resolved Decisions (do not reopen)

| Topic | Decision |
|-------|---------|
| Frontend approach | Full TALL stack (Tailwind + Alpine.js + Laravel + Livewire) |
| Direktori live search | Livewire component `DirectoriSearch` |
| Contact form | Livewire component `ContactForm` |
| Site search | Livewire component `SearchResults` |
| Charts | Chart.js — rendered via Alpine.js `x-init` |
| Carousel | Alpine.js + Embla.js (vanilla) — no server state needed |
| Static pages (Penafian, Dasar Privasi) | Content from `settings` table via Filament |
| i18n | Native `lang/ms/` + `lang/en/` PHP arrays |
| Public AI chatbot | Livewire component `AiChat`; RAG via pgvector + admin-configured LLM; rate limit configurable via settings |
| Admin AI editor | Filament custom actions on RichEditor fields; any admin-configured LLM provider |
| AI provider selection | `ManageAiSettings` Filament page; LLM + embedding provider/model/key configurable without code changes |
| RAG pipeline | Model saved → `EmbeddingObserver` → `GenerateEmbeddingJob` (queued) → admin-configured embedding provider → pgvector |
| Site settings management | `ManageSiteInfo` (branding, logo, favicon, social), `ManageEmailSettings` (SMTP/mail driver), `ManageMediaSettings` (storage driver + cloud credentials), `ManageAiSettings` (AI provider + system prompt) — all in `settings` table; encrypted for passwords and API keys |
| User & role management | `UserResource` (create/edit/deactivate users, assign roles, `department` field for scoping) + `RoleResource` (Spatie Permission CRUD); **6 roles**: `super_admin`, `department_admin`, `content_editor`, `content_author`, `publisher`, `viewer` |
| Model + resource scaffolding | **Filament Blueprint first** — define model in `draft.yaml`, run `php artisan blueprint:build`, verify migration against `docs/database-schema.md`, apply Post-Generation Checklist; never hand-write resource boilerplate |

---

## What Not To Do

- Do not add features not present in kd-portal (exception: approved AI features — see Resolved Decisions)
- Do not use Inertia.js, React, or Vue
- Do not use Alpine.js for anything that involves server state — use Livewire instead
- Do not use `fetch()` / `axios` directly in Alpine.js for data fetching — use Livewire wire calls
- Do not leave `// TODO` comments in committed code
- Do not reference docs that don't exist in this repo
- Do not mark a task done without running the validation commands
- Do not combine multiple models or routes in a single task — work in atomic slices
- Do not write Filament resource boilerplate (migrations, models, resource pages) manually from scratch — define in `draft.yaml` and run `php artisan blueprint:build` first, then customise

---

## How To Verify Your Work

Minimum checks before marking any task complete:

```bash
# Run tests
php artisan test --filter=Feature
php artisan test --filter=Unit

# Check both locales respond 200
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/ms/{route}
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/en/{route}

# Check migration status
php artisan migrate:status

# Check cache is set after first request
php artisan tinker --execute="Cache::has('page:/ms/{route}')"
```

Full per-feature validation commands: [docs/agentic-coding.md → Per-Feature Validation Reference](docs/agentic-coding.md)

---

## Status Labels

When updating [docs/pages-features.md](docs/pages-features.md), use exactly:
- `Planned` — not yet built
- `Implemented` — built and validated (record the test name)
- `Deferred` — postponed (record why)

Never remove a status label. Change `Planned` → `Implemented` only after validation passes.

---

## Agent Workflow Rules

- Follow existing project conventions before introducing new patterns.
- Reuse existing components/classes/views when possible.
- Keep directory structure intact; do not create new base folders without approval.
- Do not add or change dependencies without approval.
- Prefer focused feature and unit tests over ad-hoc debug scripts.
- Do not create new documentation files unless explicitly requested.
- Keep responses concise and action-focused.

---

## Laravel + Ecosystem Rules

### Foundational versions

- PHP 8.3.x
- Laravel 12.x
- Filament 5.x
- Livewire 4.x
- Octane 2.x
- PHPUnit 11.x
- Laravel Pint 1.x
- Laravel Boost 2.x

### Documentation lookup

- Before implementing Laravel/framework/package behavior, consult version-appropriate official docs or internal docs referenced in this repository.
- Use broad, topic-based queries when searching docs.

### PHP rules

- Always use curly braces for control structures.
- Use constructor property promotion where appropriate.
- Avoid empty public constructors.
- Add explicit parameter and return types.
- Prefer PHPDoc blocks over inline comments unless logic is genuinely complex.
- Add array-shape PHPDoc where it improves clarity.

### Laravel rules

- Prefer `php artisan make:*` generators for framework artifacts.
- Use `--no-interaction` for automation-safe artisan commands.
- Prefer Eloquent models/relationships over raw queries.
- Prevent N+1 issues via eager loading.
- Use Form Request classes for validation (rules + custom messages).
- Use Laravel auth/authorization features (policies, gates, etc.).
- Use named routes and `route()` for URL generation.
- Use queued jobs for time-consuming work.
- Never call `env()` outside config files; use `config()`.

### Laravel 12 structure

- Middleware registration is in `bootstrap/app.php`, not `app/Http/Kernel.php`.
- Console configuration is in `bootstrap/app.php` and `routes/console.php`.
- Console commands in `app/Console/Commands/` auto-register.
- For column modifications in migrations, restate all existing attributes to avoid accidental drops.

### Formatting and tests

- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
- Run the minimum relevant tests first (filtered/file-level), then expand only if needed.
- Do not delete tests without explicit approval.
- Tests must cover happy path, failure path, and edge cases.

---

## Frontend Build Reminder

If frontend changes are not visible, run one of:

```bash
npm run build
npm run dev
composer run dev
```
