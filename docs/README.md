# Documentation Guide

This folder contains planning and technical reference documents for OpenGovPortal.

## Read Order (for humans and coding agents)

1. `docs/conversion-timeline.md` for project scope, phases, and delivery milestones.
2. `docs/pages-features.md` for functional parity targets and route inventory.
3. `docs/database-schema.md` for data model and migration order.
4. `docs/architecture.md` for runtime topology and scaling model.
5. `docs/caching.md` for cache strategy and invalidation patterns.
6. `docs/design.md` for UI system and accessibility baseline.
7. `docs/agentic-coding.md` for execution workflow and quality gates.
8. `docs/testing.md` for testing strategy, factories, and CI/CD pipeline.
9. `docs/api.md` for REST API endpoints, request/response format, and rate limiting.
10. `docs/deployment.md` for Docker, Kubernetes, zero-downtime deployment, and backups.
11. `docs/security.md` for OWASP coverage, security headers, RBAC, and PDPA compliance.

## Project Phases

| Phase | Weeks | Focus |
|-------|-------|-------|
| **1: Foundation** | 1–2 | Project bootstrap, tooling installation (Filament, Boost, Blueprint), design system, base infrastructure |
| **2: Content Models & CMS** | 3–5 | All Filament resources, media, site config, search indexing, admin polish |
| **3: Public Pages** | 6–9 | All 10 public pages, Livewire components, bilingual routes, SEO |
| **4: Performance & Quality** | 10–11 | Redis caching, WCAG AA, PHPUnit coverage, security audit, load testing |
| **5: Migration & Launch** | 12 | Content migration from MongoDB, DNS cutover, production deploy, monitoring |

See [conversion-timeline.md](conversion-timeline.md) for detailed tasks, installation commands, and acceptance criteria per phase.

---

## Source Of Truth Rules

- Treat `docs/conversion-timeline.md` as the scope baseline for delivery.
- Treat `docs/database-schema.md` as the schema baseline when implementing migrations.
- Treat `docs/pages-features.md` as the route and feature parity baseline.
- If a document conflicts with code, code wins. Open a docs update in the same PR.

## Maintenance Rules

- Mark planned work clearly using unchecked boxes (`- [ ]`) and implemented work with checked boxes (`- [x]`).
- Add concrete dates when updating timelines or milestones (`YYYY-MM-DD`).
- Keep examples runnable and avoid placeholder commands unless explicitly labeled as pseudocode.
- Avoid broken references to docs that do not exist in this repository.
