# OpenGovPortal — Claude Code Instructions

This file is loaded automatically by Claude Code. Read it before doing any work in this repository.

---

## What This Project Is

A Laravel 12 recreation of https://www.digital.gov.my/ — the official website of Kementerian Digital Malaysia.

The source to replicate is https://github.com/govtechmy/kd-portal (Next.js 15 + Payload CMS + MongoDB).

**Scope constraint:** Build only what exists in kd-portal. Do not add features that are not present in the source site.

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
| File storage | AWS S3 | |
| Email | AWS SES (Laravel SES mail driver) | |
| Auth/RBAC | Spatie Laravel Permission | |
| Search | PostgreSQL FTS via `searchable_content` table | |
| Charts | Chart.js | Not Recharts, not ApexCharts |
| Carousel | Alpine.js + Embla.js (vanilla) | Alpine only for UI, no server state |
| Agentic tooling | Laravel Boost v2.x | AI coding agent integration for agentic workflows |

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

| Payload | Laravel |
|---------|---------|
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

### Controllers
Match the route-to-controller table in [docs/agentic-coding.md](docs/agentic-coding.md) exactly. Do not rename.

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

---

## What Not To Do

- Do not add features not present in kd-portal
- Do not use Inertia.js, React, or Vue
- Do not use Alpine.js for anything that involves server state — use Livewire instead
- Do not use `fetch()` / `axios` directly in Alpine.js for data fetching — use Livewire wire calls
- Do not leave `// TODO` comments in committed code
- Do not reference docs that don't exist in this repo
- Do not mark a task done without running the validation commands
- Do not combine multiple models or routes in a single task — work in atomic slices

---

## How To Verify Your Work

Minimum checks before marking any task complete:

```bash
# Run tests
php artisan test --filter=Feature
php artisan test --filter=Unit

# Check both locales respond 200
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/ms/{route}
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/en/{route}

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
