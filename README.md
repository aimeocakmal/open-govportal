# 🏛️ OpenGovPortal

> High-performance, open-source government portal solution built with Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)

---

## Overview

**OpenGovPortal** is a modern, high-performance content management system designed specifically for government agencies and public sector organizations. Built with **Laravel 11 + Octane**, it handles **10,000+ concurrent users** while maintaining sub-second response times.

### Key Features

- ⚡ **High Performance** — Laravel Octane (Swoole) for 10x speed
- 🔐 **Secure** — Role-based access control, audit logging
- 🌍 **Multi-language** — BM, English, Chinese support
- 📱 **Responsive** — Mobile-first design
- 🤖 **Agentic AI Ready** — Clean architecture for AI-assisted development
- 🗄️ **PostgreSQL** — Enterprise-grade database
- 💾 **Heavy Caching** — Redis + CDN for optimal performance

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
| [Architecture](docs/architecture.md) | System design & infrastructure |
| [Installation](docs/installation.md) | Setup guide & requirements |
| [Caching Strategy](docs/caching.md) | Multi-layer caching implementation |
| [Database Schema](docs/database-schema.md) | PostgreSQL schema design |
| [RBAC](docs/rbac.md) | Role-based access control |
| [Multi-language](docs/multi-language.md) | i18n implementation (BM/EN/CN) |
| [Performance](docs/performance.md) | Optimization techniques |
| [Security](docs/security.md) | Security best practices |
| [Deployment](docs/deployment.md) | Production deployment guide |
| [API](docs/api.md) | REST API documentation |

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
db_connection=pgsql
db_host=127.0.0.1
db_port=5432
db_database=govportal
db_username=postgres
db_password=your_password

# Configure Redis in .env
redis_host=127.0.0.1
redis_password=null
redis_port=6379

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
| **Department Admin** | Department content | Manage specific department |
| **Content Editor** | Create, edit | Content creators |
| **Content Publisher** | Publish, unpublish | Approvers |
| **Viewer** | Read-only | Public servants |

### Permission Granularity

- **Modules:** Announcements, Services, Downloads, Gallery, etc.
- **Actions:** Create, Read, Update, Delete, Publish
- **Scope:** Own content, Department content, All content

---

## Multi-Language Support

Built-in support for Malaysian languages:

- 🇲🇾 **Bahasa Malaysia** (ms)
- 🇬🇧 **English** (en)
- 🇨🇳 **中文** (zh)

Switch languages via URL: `/ms/announcements` or `/en/announcements`

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
