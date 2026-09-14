# Stack — TALL

This project uses the full **TALL stack**: Tailwind CSS, Alpine.js, Laravel, Livewire.

| Layer              | Technology                                                                                             | Notes                                                                                                                                |
|--------------------|--------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| Framework          | Laravel 12                                                                                             |                                                                                                                                      |
| Performance        | Laravel Octane + **FrankenPHP**                                                                        | Not Swoole — see architecture.md for rationale                                                                                       |
| Admin CMS          | Filament v5.x                                                                                          | Filament already bundles Livewire 4                                                                                                  |
| Styling            | Tailwind CSS v4.x                                                                                      | MyDS design tokens; CSS-first config via `@theme`                                                                                    |
| Reactivity         | Livewire 4                                                                                             | Server-side components for all interactive pages                                                                                     |
| Micro-interactions | Alpine.js v3.x                                                                                         | UI-only: carousel, mobile menu, dropdowns, modals                                                                                    |
| Templates          | Blade                                                                                                  | All views; Livewire components extend Blade                                                                                          |
| Database           | PostgreSQL                                                                                             |                                                                                                                                      |
| Cache              | Redis (tagged cache)                                                                                   |                                                                                                                                      |
| File storage       | **Admin-configurable**: local filesystem, AWS S3, Cloudflare R2, GCP Cloud Storage, Azure Blob Storage | `ManageMediaSettings` Filament page; credentials in `settings` table (encrypted); active disk applied at runtime via `Config::set()` |
| Email              | AWS SES (Laravel SES mail driver)                                                                      |                                                                                                                                      |
| Auth/RBAC          | Spatie Laravel Permission                                                                              |                                                                                                                                      |
| Search             | PostgreSQL FTS via `searchable_content` table                                                          |                                                                                                                                      |
| Charts             | Chart.js                                                                                               | Not Recharts, not ApexCharts                                                                                                         |
| Carousel           | Alpine.js + Embla.js (vanilla)                                                                         | Alpine only for UI, no server state                                                                                                  |
| Agentic tooling    | Laravel Boost v2.x                                                                                     | AI coding agent integration for agentic workflows                                                                                    |
| AI framework       | Prism PHP (`echolabsdev/prism`)                                                                        | Unified interface for all AI provider calls (LLM + embeddings)                                                                       |
| AI provider        | **Admin-configurable** via `ManageAiSettings`                                                          | Anthropic, OpenAI, Google Gemini, Groq, Mistral, Ollama, or any OpenAI-compatible endpoint (Qwen, Moonshot, DeepSeek, …)             |
| Vector storage     | pgvector (PostgreSQL extension)                                                                        | `content_embeddings` table; no separate vector DB; dimension configured via `AI_EMBEDDING_DIMENSION`                                 |

## Octane Server: FrankenPHP

FrankenPHP replaces both Swoole **and** Nginx. Caddy is built in.

```
Cloudflare CDN → FrankenPHP/Caddy → Laravel workers → Redis + PostgreSQL
```

Do **not** use `Octane::table()` — it is Swoole-only and not available in FrankenPHP. Use Redis for all shared state.

## Frontend Layer Rule

Use the **most server-side approach possible**:

1. **Pure Blade** — static content pages (no user input, no filtering)
2. **Livewire component** — any page with search, filtering, pagination, or form submission
3. **Alpine.js** — UI micro-interactions only (toggle, show/hide, carousel init, dropdown open)
4. **Never** — Inertia.js, React, Vue, or any SPA approach
