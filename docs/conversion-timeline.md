# OpenGovPortal: Digital Gov Malaysia — Laravel Conversion Plan

## Executive Summary

**Source:** https://github.com/govtechmy/kd-portal (Next.js 15 + Payload CMS + MongoDB)
**Target:** OpenGovPortal (Laravel 11 + Octane + PostgreSQL)
**Goal:** Full recreation of https://www.digital.gov.my/ using the Laravel stack

---

## Source System Analysis (kd-portal)

### Tech Stack

| Component | Technology | Version |
|-----------|------------|---------|
| **Framework** | Next.js | 15.1.11 |
| **CMS** | Payload CMS | latest |
| **Database** | MongoDB | via mongooseAdapter |
| **Language** | TypeScript | 5.5.2 |
| **Styling** | Tailwind CSS | 3.4.17 |
| **i18n** | next-intl | 3.26.3 |
| **Storage** | AWS S3 | via @payloadcms/storage-s3 |
| **Deployment** | Docker + Kubernetes | docker-compose + .kube/ |
| **Components** | Radix UI | various |

### Payload CMS Collections (Data Models)

These are the content types managed in kd-portal's Payload CMS. Each maps to a Laravel model:

| Payload Collection | Purpose | Laravel Model |
|-------------------|---------|---------------|
| `Achievement` | Ministry achievements with dates | `Achievement` |
| `Broadcast` | News/press releases/announcements | `Broadcast` |
| `Celebration` | Special events and celebrations | `Celebration` |
| `Directory` | Staff directory entries | `StaffDirectory` |
| `Feedback` | User-submitted feedback | `Feedback` |
| `File` | Downloadable file management | `File` |
| `HeroBanner` | Hero banner images/slides | `HeroBanner` |
| `Media` | General media assets (images, videos) | `Media` |
| `Policy` | Policy documents | `Policy` |
| `QuickLink` | Homepage quick navigation links | `QuickLink` |
| `Search-Overrides` | Custom search priority overrides | `SearchOverride` |
| `Users` | CMS admin users | `User` |

### Payload CMS Globals (Site Configuration)

Globals are singleton configs managed in Payload. These map to Laravel settings/config tables:

| Payload Global | Purpose | Laravel Equivalent |
|---------------|---------|-------------------|
| `SiteInfo` | Site name, description, metadata | `settings` table or config |
| `Header` | Navigation menu items | `navigation_items` table |
| `Footer` | Footer links and content | `footer_settings` table |
| `Homepage` | Homepage layout configuration | `homepage_settings` table |
| `MinisterProfile` | Current minister's profile | `minister_profile` table |
| `Addresses` | Ministry physical addresses | `addresses` table |
| `FeedbackSettings` | Feedback widget configuration | `feedback_settings` table |

### Site Pages & Routes

All pages support locale-prefixed URLs (`/ms/...`, `/en/...`). Supported locales: `ms-MY` (default), `en-GB`.

| Route | Page Name | Component Directory | Description |
|-------|-----------|--------------------|----|
| `/` | Homepage | `home/` | Hero banner, quick links, broadcasts, achievements |
| `/siaran` | Siaran | `siaran/` | Broadcasts/news listing + detail |
| `/pencapaian` | Pencapaian | `pencapaian/` | Achievements listing |
| `/statistik` | Statistik | `statistik/` | Statistics with charts (Recharts) |
| `/direktori` | Direktori | `direktori/` | Staff directory with search |
| `/dasar` | Dasar | `dasar/` | Policy documents listing |
| `/profil-kementerian` | Profil Kementerian | — | Ministry profile & minister info |
| `/hubungi-kami` | Hubungi Kami | — | Contact information & form |
| `/penafian` | Penafian | — | Disclaimer (static) |
| `/dasar-privasi` | Dasar Privasi | — | Privacy policy (static) |

### Search Configuration

The Payload search plugin indexes these collections with priorities:
- Achievement: priority 10
- Broadcast: priority 20
- Staff Directory: priority 30
- Policy: priority 40

### Key Frontend Dependencies (to replicate)

| Package | Purpose | Laravel Replacement |
|---------|---------|-------------------|
| `embla-carousel-react` | Hero banner carousel | Alpine.js + CSS |
| `@tanstack/react-table` | Data tables | Blade + Alpine.js |
| `recharts` | Statistics charts | Chart.js or ApexCharts |
| `@radix-ui/*` | Accessible UI primitives | Custom Blade components |
| `cmdk` | Command palette search | Alpine.js search |
| `date-fns` | Date formatting | PHP Carbon |
| `react-day-picker` | Date picker | Flatpickr |
| `next-intl` | Internationalisation | Laravel's `lang/` + `App::setLocale()` |

---

## Conversion Strategy

### Architecture Comparison

| Aspect | kd-portal (Source) | OpenGovPortal (Target) |
|--------|--------------------|------------------------|
| **Rendering** | SSR/SSG (Next.js) | SSR with Redis page caching |
| **CMS** | Payload CMS (headless) | Filament v3 admin panel |
| **Database** | MongoDB | PostgreSQL |
| **Storage** | AWS S3 | AWS S3 (via Laravel Filesystem) |
| **Search** | Payload Search Plugin | Laravel Scout + PostgreSQL FTS |
| **i18n** | next-intl (ms-MY, en-GB) | Laravel `lang/` (ms, en) |
| **Components** | React + Radix UI | Blade + Alpine.js |
| **Charts** | Recharts | Chart.js or ApexCharts |
| **Carousel** | Embla Carousel | Alpine.js + Embla or Swiper |
| **Language** | TypeScript | PHP 8.3 |

### Design System (MyDS)

The original portal uses **MyDS (Malaysian Government Design System)**. Key design tokens to replicate:

- **Primary Color:** Blue (`#2563EB`)
- **Font:** Inter (with system-ui fallback)
- **Spacing:** 4px base unit system
- **Breakpoints:** Mobile < 640px, Tablet 640px+, Desktop 768px+, Large 1024px+, XL 1280px+
- **Accessibility:** WCAG 2.1 AA compliance
- **Components:** Navigation, Cards, Buttons, Alerts, Forms, Tables

---

## Implementation Plan: 12 Weeks

### Phase 1: Foundation (Weeks 1–2)

#### Week 1: Project Setup & Design System

**Tasks:**
- [ ] Initialize Laravel 11 project
- [ ] Install and configure Laravel Octane (Swoole)
- [ ] Set up PostgreSQL database + Redis
- [ ] Configure Laravel multi-language (`ms`, `en`) with locale URL prefix
- [ ] Set up Tailwind CSS with MyDS design tokens (colors, fonts, spacing)
- [ ] Create base Blade layouts (app.blade.php, guest.blade.php)
- [ ] Build navigation component with language switcher (ms/en)
- [ ] Build footer component

**Deliverables:**
- Running Laravel skeleton with Octane
- MyDS Tailwind config
- Base layout with header + footer
- Language switcher working

**Effort:** 40 hours

#### Week 2: Core Infrastructure

**Tasks:**
- [ ] Install Filament v3 admin panel
- [ ] Install and configure Spatie Laravel Permission (RBAC)
- [ ] Configure AWS S3 for media storage (matching kd-portal's S3 setup)
- [ ] Set up full-page caching middleware (Redis)
- [ ] Configure CI/CD pipeline (GitHub Actions)
- [ ] Write base tests
- [ ] Set up Laravel Scout for search

**Deliverables:**
- Filament admin accessible at `/admin`
- RBAC roles and permissions seeded
- S3 media storage working
- Caching middleware active

**Effort:** 40 hours

---

### Phase 2: Content Models & CMS (Weeks 3–5)

#### Week 3: Core Content Models

Map all Payload CMS collections to Laravel models + Filament resources:

**Tasks:**
- [ ] `Broadcast` model + migration + Filament resource (replaces Payload `Broadcast`)
- [ ] `Achievement` model + migration + Filament resource
- [ ] `Celebration` model + migration + Filament resource
- [ ] `HeroBanner` model + migration + Filament resource
- [ ] `QuickLink` model + migration + Filament resource
- [ ] `Policy` model + migration + Filament resource

**Each model needs:**
- Multilingual fields (title_ms, title_en, content_ms, content_en)
- Draft/published status
- Published-at scheduling
- Featured image via S3

**Deliverables:**
- 6 Filament resources working
- All migrations run

**Effort:** 40 hours

#### Week 4: Directory, Files & Site Config

**Tasks:**
- [ ] `StaffDirectory` model + migration + Filament resource
- [ ] `File` model + migration + Filament resource (downloadable files)
- [ ] `Media` model + migration + Filament resource
- [ ] `Feedback` model + migration + Filament resource
- [ ] `SearchOverride` model + Filament resource
- [ ] Settings models: SiteInfo, Header nav, Footer, MinisterProfile, Addresses
- [ ] Homepage configuration in Filament (global settings)
- [ ] Rich text editor integration (Filament RichEditor or Tiptap)

**Deliverables:**
- All Payload collections mapped to Filament
- Site configuration editable from admin
- Media library working

**Effort:** 40 hours

#### Week 5: Admin Polish

**Tasks:**
- [ ] Content versioning / revision history
- [ ] Draft/publish workflow with scheduled publishing
- [ ] Search indexing via Laravel Scout (PostgreSQL FTS)
- [ ] Bulk actions in Filament
- [ ] Image optimization on upload
- [ ] Content preview from admin
- [ ] Role-based access within Filament resources

**Deliverables:**
- Full CMS parity with Payload
- Search indexing working
- Workflow: Draft → Review → Publish

**Effort:** 40 hours

---

### Phase 3: Public Pages (Weeks 6–9)

#### Week 6: Homepage

Recreate homepage sections from kd-portal's `home/` components:

**Tasks:**
- [ ] Hero banner section (carousel with HeroBanner collection data)
- [ ] Quick links grid (QuickLink collection data)
- [ ] Latest broadcasts section (last 6 Broadcast items)
- [ ] Achievements highlights (last 7 Achievement items, sorted by date)
- [ ] Ministry announcements/stats strip
- [ ] Alpine.js for carousel interactivity
- [ ] Mobile-responsive layout

**Deliverables:**
- Homepage fully functional (ms/en)
- Carousel working
- Data from database

**Effort:** 40 hours

#### Week 7: Siaran & Pencapaian Pages

**Tasks:**
- [ ] `/siaran` — Broadcasts listing (with pagination, filter by type)
- [ ] `/siaran/{slug}` — Broadcast detail page
- [ ] `/pencapaian` — Achievements listing (with year filter)
- [ ] `/pencapaian/{slug}` — Achievement detail
- [ ] Breadcrumb navigation
- [ ] Related content links
- [ ] SEO meta tags per page

**Deliverables:**
- Siaran and Pencapaian pages in ms/en
- Detail pages with full content

**Effort:** 40 hours

#### Week 8: Direktori & Statistik Pages

**Tasks:**
- [ ] `/direktori` — Staff directory with search by name/department
- [ ] Directory card components with contact info
- [ ] `/statistik` — Statistics page with charts
- [ ] Integrate Chart.js (or ApexCharts) for data visualisations (replaces Recharts)
- [ ] Statistics data management in Filament
- [ ] Responsive table/grid layout

**Deliverables:**
- Direktori page with live search
- Statistik page with charts

**Effort:** 40 hours

#### Week 9: Static & Policy Pages

**Tasks:**
- [ ] `/dasar` — Policy documents listing + download links
- [ ] `/profil-kementerian` — Ministry profile with minister info (MinisterProfile global)
- [ ] `/hubungi-kami` — Contact page with form + addresses (Addresses global)
- [ ] Contact form submission → Feedback collection + email notification (AWS SES)
- [ ] `/penafian` — Disclaimer (static Blade view)
- [ ] `/dasar-privasi` — Privacy policy (static Blade view)
- [ ] Global site search at `/carian` using Laravel Scout
- [ ] XML Sitemap generation
- [ ] 404 and error pages

**Deliverables:**
- All 10 pages complete
- Contact form sending email
- Sitemap live

**Effort:** 40 hours

---

### Phase 4: Performance & Quality (Weeks 10–11)

#### Week 10: Performance Optimisation

**Tasks:**
- [ ] Full-page cache for all public routes (Redis, TTL 1 hour)
- [ ] Cache tag invalidation on content save (Filament observer hooks)
- [ ] Database query optimisation (eager loading, indexes)
- [ ] Image lazy loading + WebP conversion on upload
- [ ] Cloudflare CDN integration
- [ ] Octane tuning (workers, max_requests)
- [ ] Route, config, view caching in production
- [ ] Lighthouse audit → target 90+ score

**Deliverables:**
- Page load < 1 second
- 90+ Lighthouse performance score
- Cache invalidation working

**Effort:** 40 hours

#### Week 11: Testing, Accessibility & Security

**Tasks:**
- [ ] PHPUnit feature tests for all routes (ms + en locale)
- [ ] Browser testing (Chrome, Firefox, Safari, mobile)
- [ ] WCAG 2.1 AA accessibility audit + fixes
- [ ] Security audit (CSRF, XSS, SQL injection, rate limiting)
- [ ] Load testing (target: 10,000 concurrent users)
- [ ] Cross-browser responsive testing

**Deliverables:**
- Test suite with >80% coverage
- WCAG compliance report
- Security audit passed

**Effort:** 40 hours

---

### Phase 5: Migration & Launch (Week 12)

**Tasks:**
- [ ] Export content from kd-portal MongoDB via Payload API
- [ ] Write migration scripts to import into PostgreSQL
- [ ] Verify content integrity (counts, images, slugs)
- [ ] Set up URL redirects (preserve old URL structure)
- [ ] Production environment configuration
- [ ] SSL certificates + DNS
- [ ] Final smoke tests
- [ ] Soft launch (staging → production)
- [ ] Post-launch monitoring setup (Sentry, Grafana)

**Deliverables:**
- All content migrated
- Live at digital.gov.my equivalent
- Monitoring active

**Effort:** 40 hours

---

## Resource Requirements

### Team Composition

| Role | Count | Hours/Week | Total Hours |
|------|-------|------------|-------------|
| Tech Lead | 1 | 20 | 240 |
| Backend Developer | 2 | 40 | 960 |
| Frontend Developer | 1 | 40 | 480 |
| DevOps Engineer | 1 | 10 | 120 |
| QA Engineer | 1 | 20 | 240 |
| **TOTAL** | **6** | | **2,040** |

### Budget Estimate

| Item | Cost (MYR) |
|------|------------|
| Development (2,040 hours @ RM150/hr) | RM 306,000 |
| Infrastructure (12 weeks) | RM 10,000 |
| Third-party services (AWS SES, S3, Sentry) | RM 5,000 |
| **TOTAL** | **RM 321,000** |

---

## Collection-to-Model Mapping Reference

```php
// Payload: Broadcast → Laravel: Broadcast
class Broadcast extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'content_ms', 'content_en',
        'excerpt_ms', 'excerpt_en',
        'featured_image',        // S3 key
        'status',                // draft | published
        'published_at',
        'type',                  // press_release | announcement | news
    ];
}

// Payload: Achievement → Laravel: Achievement
class Achievement extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'description_ms', 'description_en',
        'date',
        'icon',                  // S3 key
        'status',
        'is_featured',
    ];
}

// Payload: Directory → Laravel: StaffDirectory
class StaffDirectory extends Model {
    protected $fillable = [
        'name',
        'position_ms', 'position_en',
        'department_ms', 'department_en',
        'email',
        'phone',
        'photo',                 // S3 key
        'sort_order',
        'is_active',
    ];
}

// Payload: Policy → Laravel: Policy
class Policy extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'slug',
        'description_ms', 'description_en',
        'file_url',              // S3 key (PDF)
        'category',
        'published_at',
        'status',
    ];
}

// Payload: HeroBanner → Laravel: HeroBanner
class HeroBanner extends Model {
    protected $fillable = [
        'title_ms', 'title_en',
        'subtitle_ms', 'subtitle_en',
        'image',                 // S3 key
        'cta_label_ms', 'cta_label_en',
        'cta_url',
        'sort_order',
        'is_active',
    ];
}

// Payload: QuickLink → Laravel: QuickLink
class QuickLink extends Model {
    protected $fillable = [
        'label_ms', 'label_en',
        'url',
        'icon',
        'sort_order',
        'is_active',
    ];
}
```

---

## Milestones

| Week | Milestone | Success Criteria |
|------|-----------|-----------------|
| 2 | Foundation Complete | Laravel + Octane running, Filament accessible |
| 5 | CMS Complete | All 12 collections manageable in Filament |
| 9 | All Pages Complete | All 10 public pages functional in ms/en |
| 11 | QA Complete | 90+ Lighthouse, WCAG AA, load test passed |
| 12 | Go Live | Site deployed, content migrated |

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Content migration from MongoDB | High | Use Payload REST API to export JSON, write custom importers |
| MyDS design parity | Medium | Audit kd-portal Tailwind config, replicate classes exactly |
| Chart.js replacing Recharts | Low | Feature-equivalent; data format mapping required |
| Alpine.js replacing React | Medium | Audit all interactive components in kd-portal; some complexity expected |
| Filament vs Payload feature gaps | Medium | Map each Payload field type to Filament field; test all workflows |
| Scope creep | Medium | Strict change control; anything beyond kd-portal parity is Phase 2 |

---

## Acceptance Criteria

### Functional
- [ ] All 10 pages from kd-portal replicated
- [ ] All 12 Payload collections migrated to Filament resources
- [ ] All 7 Payload globals editable in Filament
- [ ] Multi-language (ms/en) working on all pages
- [ ] Contact form submits and sends email (AWS SES)
- [ ] Site search working across Broadcast, Achievement, Directory, Policy
- [ ] All content from kd-portal MongoDB imported to PostgreSQL
- [ ] URL redirects in place for all changed URLs

### Non-Functional
- [ ] Page load < 1 second (Lighthouse Performance 90+)
- [ ] 10,000+ concurrent users supported
- [ ] 99.9% uptime (SLA)
- [ ] WCAG 2.1 AA accessibility compliance
- [ ] MyDS design system compliance
- [ ] SEO: sitemap.xml, canonical URLs, Open Graph meta

---

*This plan converts https://github.com/govtechmy/kd-portal from Next.js 15 + Payload CMS + MongoDB to Laravel 11 + Octane + Filament + PostgreSQL, achieving full feature parity with https://www.digital.gov.my/*
