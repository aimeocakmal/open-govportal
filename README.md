# OpenGovPortal

> Laravel recreation of the Kementerian Digital Malaysia portal (digital.gov.my)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)

---

## Overview

**OpenGovPortal** is a Laravel 11 recreation of the official [Kementerian Digital Malaysia](https://www.digital.gov.my/) website, originally built by GovTech Malaysia as [kd-portal](https://github.com/govtechmy/kd-portal) (Next.js 15 + Payload CMS + MongoDB).

This project migrates the portal to a **Laravel 11 + Octane** stack, targeting **10,000+ concurrent users** with sub-second response times, while preserving full feature parity and MyDS design compliance.

### Source Reference

| Item | Details |
|------|---------|
| **Original Site** | https://www.digital.gov.my/ |
| **Original Repo** | https://github.com/govtechmy/kd-portal |
| **Original Stack** | Next.js 15 + Payload CMS + MongoDB |
| **Target Stack** | Laravel 11 + Octane + PostgreSQL |

### Key Features

- **High Performance** — Laravel Octane (Swoole) for 10x speed
- **Secure** — Role-based access control, audit logging
- **Multi-language** — Bahasa Malaysia (ms-MY) + English (en-GB)
- **Responsive** — Mobile-first, MyDS-compliant design
- **Full CMS** — Filament admin panel replacing Payload CMS
- **PostgreSQL** — Enterprise-grade database (replaces MongoDB)
- **Heavy Caching** — Redis + CDN for optimal performance

---

## Architecture

```
┌─────────────────────────────────────────┐
│  CDN (Cloudflare/AWS CloudFront)        │
│  - Global edge caching                  │
│  - DDoS protection                      │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│  LOAD BALANCER (Nginx/ALB)              │
│  - SSL termination                      │
│  - Rate limiting                        │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│  LARAVEL OCTANE (Swoole)                │
│  - Application in memory                │
│  - Handle 10K+ concurrent               │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│  REDIS CLUSTER                          │
│  - Full-page cache                      │
│  - Session storage                      │
│  - Query cache                          │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│  POSTGRESQL                             │
│  - Primary + Read Replicas              │
│  - Connection pooling                   │
└─────────────────────────────────────────┘
```

---

## Tech Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Framework** | Laravel 11 | Core application |
| **Performance** | Laravel Octane (Swoole) | High-concurrency handling |
| **Frontend** | Blade + Tailwind CSS | Lightweight, fast rendering |
| **Database** | PostgreSQL | Primary data store |
| **Cache** | Redis | Full-page & query caching |
| **Auth** | Spatie Laravel Permission | RBAC (Role-Based Access Control) |
| **CDN** | Cloudflare | Edge caching & security |
| **Queue** | Redis | Background job processing |

---

## Performance Targets

| Metric | Target | Implementation |
|--------|--------|----------------|
| **First Contentful Paint** | < 1 second | CDN + edge caching |
| **Time to Interactive** | < 2 seconds | Octane + Redis |
| **Concurrent Users** | 10,000+ | Swoole + horizontal scaling |
| **Database Queries** | < 10/page | Aggressive caching |
| **Uptime** | 99.9% | Load balancer + failover |

---

## Documentation

Full technical documentation available in [`docs/`](docs/):

| Document | Description |
|----------|-------------|
| [Documentation Guide](docs/README.md) | Doc index, source-of-truth rules, and maintenance standards |
| [Agentic Coding Playbook](docs/agentic-coding.md) | Task execution workflow and definition of done for coding agents |
| [Pages & Features](docs/pages-features.md) | All pages, routes, and features from kd-portal |
| [Conversion Plan](docs/conversion-timeline.md) | 12-week Laravel migration plan |
| [Database Schema](docs/database-schema.md) | PostgreSQL schema (mapped from Payload CMS collections) |
| [Architecture](docs/architecture.md) | System design & infrastructure |
| [Caching Strategy](docs/caching.md) | Multi-layer caching implementation |
| [Design System](docs/design.md) | MyDS design tokens and component specs |

---

## Quick Start

### Prerequisites

- PHP 8.3+
- PostgreSQL 14+
- Redis 7+
- Composer
- Node.js 18+
- Swoole PHP extension (for Octane)

### Installation

```bash
# Clone repository
git clone https://github.com/aimeocakmal/open-govportal.git
cd open-govportal

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=govportal
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Configure Redis in .env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start Octane (production mode)
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
```

### Development Mode

```bash
# Traditional Laravel serve (no Octane)
php artisan serve

# Or with Octane (hot reload)
php artisan octane:start --watch
```

---

## Role-Based Access Control

### Default Roles

| Role | Permissions | Description |
|------|-------------|-------------|
| **Super Admin** | All permissions | System administrator |
| **Content Editor** | Create and edit content | Content creators |
| **Publisher** | Publish approved content | Approvers |
| **Viewer** | Read-only | Public servants |

### Permission Granularity

- **Modules:** Broadcasts, Achievements, Directory, Policy, Downloads, Gallery, etc.
- **Actions:** Create, Read, Update, Delete, Publish
- **Scope:** Own content, All content

---

## Site Pages (from kd-portal)

All pages support `/ms/` and `/en/` locale prefixes:

| Route | Malay | Description |
|-------|-------|-------------|
| `/` | Laman Utama | Homepage with hero, quick links, broadcasts |
| `/siaran` | Siaran | News & broadcasts listing |
| `/pencapaian` | Pencapaian | Ministry achievements |
| `/statistik` | Statistik | Statistics & data |
| `/direktori` | Direktori | Staff directory |
| `/dasar` | Dasar | Policy documents |
| `/profil-kementerian` | Profil Kementerian | Ministry profile |
| `/hubungi-kami` | Hubungi Kami | Contact us |
| `/penafian` | Penafian | Disclaimer |
| `/dasar-privasi` | Dasar Privasi | Privacy policy |

## Multi-Language Support

Matches kd-portal locales:

- **Bahasa Malaysia** (ms-MY) — Default
- **English** (en-GB)

Switch languages via URL: `/ms/siaran` or `/en/siaran`

---

## Caching Strategy

### 3-Layer Cache Architecture

```
Layer 1: CDN (Cloudflare)
├── Static assets (CSS, JS, images)
├── Full HTML pages (TTL: 1-24 hours)
└── Edge locations: 300+ cities

Layer 2: Redis (Application Cache)
├── Full-page cache (dynamic pages)
├── Query results (database)
├── Session data
└── TTL: 1-60 minutes

Layer 3: PostgreSQL
├── Internal query cache
├── Materialized views
└── Read replicas for scaling
```

---

## Performance Optimization

### Enable Caching

```bash
# Route cache
php artisan route:cache

# View cache
php artisan view:cache

# Config cache
php artisan config:cache

# Event cache
php artisan event:cache
```

### Octane Configuration

```ini
# config/octane.php
'server' => 'swoole',
'workers' => 8,
'max_requests' => 1000,
'task_workers' => 4,
```

---

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage

# Feature tests
php artisan test --filter=Feature

# Unit tests
php artisan test --filter=Unit
```

---

## Deployment

### Production Checklist

- [ ] Environment variables configured
- [ ] Database migrated
- [ ] Redis configured
- [ ] SSL certificate installed
- [ ] CDN configured
- [ ] Monitoring enabled
- [ ] Backups scheduled
- [ ] Octane running as service

### Docker Deployment

```bash
# Build and run with Docker
docker-compose up -d

# Scale workers
docker-compose up -d --scale app=4
```

---

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Workflow

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## Security

For security vulnerabilities, please email security@opengovportal.org instead of using the issue tracker.

### Security Features

- CSRF protection
- SQL injection prevention
- XSS filtering
- Rate limiting
- Audit logging
- HTTPS enforcement
- Secure headers

---

## Roadmap

| Phase | Timeline | Features |
|-------|----------|----------|
| **Phase 1** | Q1 2026 | Core CMS, RBAC, Multi-language |
| **Phase 2** | Q2 2026 | API, Mobile app support |
| **Phase 3** | Q3 2026 | AI features, Chatbot integration |
| **Phase 4** | Q4 2026 | Advanced analytics, Reporting |

---

## License

OpenGovPortal is open-sourced software licensed under the [MIT license](LICENSE).

---

## Support

- 📧 Email: support@opengovportal.org
- 💬 Discord: [Join our community](https://discord.gg/opengovportal)
- 🐛 Issues: [GitHub Issues](https://github.com/aimeocakmal/open-govportal/issues)

---

## Acknowledgments

- Built with [Laravel](https://laravel.com)
- Inspired by the need for better government digital services
- Created for the Malaysian public sector

---

> **OpenGovPortal:** Modern, fast, secure government portals for the people.

**Made with ❤️ in Malaysia**
