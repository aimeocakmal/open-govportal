# OpenGovPortal Project

A Laravel 12 recreation of https://www.digital.gov.my/ — the official website of Kementerian Digital Malaysia.

The source to replicate is https://github.com/govtechmy/kd-portal (Next.js 15 + Payload CMS + MongoDB).

**Scope constraint:** Build only what exists in kd-portal, **plus the approved AI extension** (public AI chatbot + admin
AI content editor). All other features require explicit approval before adding.

## Must-Read Docs Before Coding

Always read these in order before starting any task:

1. [docs/agentic-coding.md](docs/agentic-coding.md) — execution rules, naming conventions, anti-patterns, validation
   commands
2. [docs/pages-features.md](docs/pages-features.md) — all 10 pages, their routes, data sources, status labels, and
   resolved decisions
3. [docs/database-schema.md](docs/database-schema.md) — all PostgreSQL tables and their exact column definitions
4. [docs/conversion-timeline.md](docs/conversion-timeline.md) — 12-week plan and slice template
