# OpenGovPortal

A Laravel 12 recreation of [digital.gov.my](https://www.digital.gov.my/), the official website of Kementerian Digital
Malaysia — rebuilt on the TALL stack with a Filament admin panel, bilingual content, and an optional AI layer.

![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4)
![Filament v5](https://img.shields.io/badge/Filament-v5-FDAE4B)
![License MIT](https://img.shields.io/badge/License-MIT-blue)

## About

The site being replicated is [govtechmy/kd-portal](https://github.com/govtechmy/kd-portal) (Next.js 15 + Payload CMS +
MongoDB). This project reimplements it as a server-rendered Laravel application backed by PostgreSQL.

**Scope is deliberately fixed:** build what exists in kd-portal, plus one approved extension (a public AI chatbot and an
admin AI content editor). Anything else needs explicit approval first.
See [docs/conversion-timeline.md](docs/conversion-timeline.md) for the delivery baseline.

Current version is **0.8.0** — see [Versioning](#versioning). Phase status lives
in [docs/README.md](docs/README.md#project-phases).

## Stack

| Layer              | Technology                                                                    |
|--------------------|-------------------------------------------------------------------------------|
| Framework          | Laravel 12                                                                    |
| Runtime            | Laravel Octane + FrankenPHP (Caddy built in — replaces both Swoole and Nginx) |
| Admin CMS          | Filament v5                                                                   |
| Reactivity         | Livewire 4 (all interactive pages)                                            |
| Micro-interactions | Alpine.js 3 (UI only — never server state)                                    |
| Styling            | Tailwind CSS v4 with MyDS design tokens                                       |
| Database           | PostgreSQL + [pgvector](https://github.com/pgvector/pgvector)                 |
| Cache              | Redis (tagged cache)                                                          |
| Search             | PostgreSQL full-text search                                                   |
| AI                 | Prism PHP (`echolabsdev/prism`), provider configurable at runtime             |

Full rationale and runtime topology: [docs/architecture.md](docs/architecture.md).

## Requirements

- **PHP 8.3+** and Composer 2
- **Node.js 20+** and npm
- **PostgreSQL 16+ with the `vector` extension** — required, not optional; the `content_embeddings` table depends on it
- **Redis** — required for the cache layer. Laravel's `database` cache driver does not support tags, and this project's
  cache invalidation is entirely tag-based
- A **Filament v5 license** — see below
- Optional: [Laravel Herd](https://herd.laravel.com/) (the project assumes the `govportal.test` domain)

## Getting started

### 1. Authenticate with the Filament registry — before installing

Filament v5 is a commercial package served from a private Composer registry, which `composer.json` already declares.
`composer install` fails with a 403 without credentials:

```bash
composer config --global --auth http-basic.packages.filamentphp.com <your-email> <your-license-key>
```

A project-local `auth.json` works too — it is already gitignored.

### 2. Install dependencies

```bash
git clone git@github.com:aimeocakmal/open-govportal.git
cd open-govportal
composer install
npm install
```

### 3. Create your environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env`

Three settings in `.env.example` ship with Laravel's stock defaults and **must be changed** — the app will not run
correctly otherwise:

```dotenv
# .env.example ships with sqlite; this project targets PostgreSQL.
# The DB_* lines below are commented out in .env.example with MySQL defaults —
# uncomment them and note the port is 5432, not 3306.
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=govportal
DB_USERNAME=postgres
DB_PASSWORD=

# .env.example ships with `database`, which cannot do tagged cache.
CACHE_STORE=redis
```

Leave `APP_LOCALE=en` as it is — it is the framework fallback and is set deliberately. The *public site* default is
Bahasa Malaysia, which is handled by routing, not by this key. See [Locales and routes](#locales-and-routes).

### 5. Create the database and enable pgvector

```bash
createdb govportal
psql -d govportal -c 'CREATE EXTENSION IF NOT EXISTS vector;'
```

### 6. Migrate, seed, and build

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
```

Seeders are idempotent — `php artisan db:seed` can be re-run without creating duplicates.

> `composer setup` runs steps 2–3 and migrates, but does **not** seed. Run `php artisan db:seed` yourself, or you get an
> empty site.

## AI configuration (optional)

`AI_CHATBOT_ENABLED` and `AI_ADMIN_EDITOR_ENABLED` both default to `false`, so the application runs fully without any AI
credentials.

To enable them, set the relevant `AI_*` keys in `.env`:

| Key                                                                     | Default                             | Purpose                                                            |
|-------------------------------------------------------------------------|-------------------------------------|--------------------------------------------------------------------|
| `AI_LLM_PROVIDER` / `AI_LLM_MODEL` / `AI_LLM_API_KEY`                   | `anthropic` / `claude-sonnet-4-6`   | Chat and content-editor model                                      |
| `AI_LLM_BASE_URL`                                                       | empty                               | Override for OpenAI-compatible endpoints                           |
| `AI_EMBEDDING_PROVIDER` / `AI_EMBEDDING_MODEL` / `AI_EMBEDDING_API_KEY` | `openai` / `text-embedding-3-small` | RAG embeddings                                                     |
| `AI_EMBEDDING_DIMENSION`                                                | `1536`                              | Must match the embedding model and the `content_embeddings` column |
| `AI_CHATBOT_ENABLED` / `AI_ADMIN_EDITOR_ENABLED`                        | `false`                             | Feature switches                                                   |
| `AI_CHATBOT_RATE_LIMIT`                                                 | `10`                                | Requests per minute per visitor                                    |

These are only bootstrap defaults. Provider, model, API key and system prompt are all editable at runtime from the
**Manage AI Settings** page in the admin panel, stored encrypted in the `settings` table.
Details: [docs/ai.md](docs/ai.md).

## Demo content and logins

The seeders produce a complete, fictional bilingual site. All four accounts use the password `password`:

| Email                             | Role             |
|-----------------------------------|------------------|
| `admin@opengovportal.example`     | `super_admin`    |
| `editor@opengovportal.example`    | `content_editor` |
| `publisher@opengovportal.example` | `publisher`      |
| `viewer@opengovportal.example`    | `viewer`         |

The content pack and the image slot specification are documented in [docs/sample-content.md](docs/sample-content.md)
and [docs/sample-images.md](docs/sample-images.md).

## Running the app

```bash
composer dev
```

That runs four processes concurrently: `php artisan serve`, the queue worker, `php artisan pail` for logs, and the Vite
dev server.

For a production-like run under Octane:

```bash
php artisan octane:start --server=frankenphp
```

Health check is at `/up`.

## Locales and routes

Two locales: **`ms`** (Bahasa Malaysia, the site default) and **`en`** (English). Translations are native `lang/ms/` and
`lang/en/` PHP arrays.

`/` inspects the browser's `Accept-Language` header and redirects to `/ms` or `/en`. Every public page is
locale-prefixed:

| Route                                                 | Page             |
|-------------------------------------------------------|------------------|
| `/{locale}`                                           | Home             |
| `/{locale}/siaran`, `/{locale}/siaran/{slug}`         | Broadcasts       |
| `/{locale}/pencapaian`, `/{locale}/pencapaian/{slug}` | Achievements     |
| `/{locale}/statistik`                                 | Statistics       |
| `/{locale}/direktori`                                 | Staff directory  |
| `/{locale}/dasar`                                     | Policies         |
| `/{locale}/profil-kementerian`                        | Ministry profile |
| `/{locale}/hubungi-kami`                              | Contact          |
| `/{locale}/carian`                                    | Search           |
| `/{locale}/penafian`, `/{locale}/dasar-privasi`       | Static pages     |
| `/{locale}/{slug}`                                    | CMS static pages |

`/sitemap.xml` and the signed `/preview/{model}/{id}` route are not locale-prefixed.

**A feature is not done until both locales are tested.** Full route and feature
inventory: [docs/pages-features.md](docs/pages-features.md).

## Admin panel

Filament v5 at **`/admin`**. Six roles ship via Spatie Laravel Permission: `super_admin`, `department_admin`,
`content_editor`, `content_author`, `publisher`, `viewer`.

## Testing

```bash
composer test                                  # clears config, runs the suite
php artisan test --compact --filter=Feature
vendor/bin/pint --dirty                        # format changed PHP files
```

Strategy, factories and the CI pipeline: [docs/testing.md](docs/testing.md).

## AI coding agents

This repository is set up for [Laravel Boost](https://github.com/laravel/boost).

**Project rules live in [`.ai/guidelines/`](.ai/guidelines/) — that directory is the single source of truth and is the
only place to edit them.** `CLAUDE.md` and `AGENTS.md` are *generated artifacts*: Boost composes them from
`.ai/guidelines/` plus its own framework guidance, and both are gitignored. Editing them directly does nothing — your
changes are overwritten on the next regeneration.

After cloning, generate them once:

```bash
php artisan boost:install    # first-time setup: picks agents, writes .mcp.json and boost.json
php artisan boost:update     # thereafter: regenerate from .ai/guidelines/
```

`boost.json` (which agents are enabled) and `.mcp.json` (the Boost MCP server registration) are both gitignored and are
recreated by `boost:install`. `composer update` and `composer setup` re-run `boost:update` automatically, and no-op
harmlessly if Boost has not been set up yet.

To change a project rule: edit the relevant file in `.ai/guidelines/`, run `php artisan boost:update`, and commit only
the `.ai/` change. Enabling another agent (Codex, Cursor, Copilot) via `boost:install` generates `AGENTS.md` from the
same source.

## Documentation

All planning and technical reference material lives in [`docs/`](docs/). Start at **[docs/README.md](docs/README.md)** —
it defines the read order, the delivery phases, and which document is authoritative for scope, schema and routes.

The three source-of-truth documents:

- [docs/conversion-timeline.md](docs/conversion-timeline.md) — scope and delivery baseline
- [docs/database-schema.md](docs/database-schema.md) — schema baseline for migrations
- [docs/pages-features.md](docs/pages-features.md) — route and feature parity baseline

## Versioning

`version.json` at the repository root holds the current version, release date, changelog and full history.

It is **read and written by the admin panel** (Platform Version page, gated on the `manage_settings` permission). Do not
hand-edit it — your changes will be overwritten. There is no separate `CHANGELOG.md`.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the branch workflow, pull request checklist, and commit guidance.

## Security

**Do not open a public issue for a security vulnerability.**

Report it privately
through [GitHub Security Advisories](https://github.com/aimeocakmal/open-govportal/security/advisories/new) on this
repository. Security posture, OWASP coverage and PDPA compliance notes: [docs/security.md](docs/security.md).

## License

Released under the [MIT License](LICENSE).

Content and design are derived from [govtechmy/kd-portal](https://github.com/govtechmy/kd-portal). The Filament v5
dependency is commercially licensed separately and is not covered by this repository's MIT grant.
