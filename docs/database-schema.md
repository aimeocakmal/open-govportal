# Database Schema

## Overview

OpenGovPortal uses **PostgreSQL**, mapping all 12 Payload CMS collections from kd-portal (MongoDB) to relational tables. Content tables use separate `_ms` / `_en` columns for bilingual content (matching kd-portal's `ms-MY` / `en-GB` locales).

---

## Entity Relationship Overview

```
users ─────────────────────────────────────── (CMS admin users)
  │
  └── (via Spatie roles/permissions)

Content Tables (public-facing):
  broadcasts          ← Payload: Broadcast
  achievements        ← Payload: Achievement
  celebrations        ← Payload: Celebration
  staff_directories   ← Payload: Directory
  policies            ← Payload: Policy
  files               ← Payload: File
  hero_banners        ← Payload: HeroBanner
  quick_links         ← Payload: QuickLink
  media               ← Payload: Media
  feedbacks           ← Payload: Feedback
  search_overrides    ← Payload: Search-Overrides

Site Configuration Tables:
  settings            ← Payload Global: SiteInfo
  menus               ← Registry of named menus (replaces navigation_items)
  menu_items          ← 4-level menu items with role visibility
  footer_settings     ← Payload Global: Footer
  homepage_settings   ← Payload Global: Homepage
  minister_profiles   ← Payload Global: MinisterProfile
  addresses           ← Payload Global: Addresses
  feedback_settings   ← Payload Global: FeedbackSettings

CMS Extensions (beyond Payload parity):
  static_pages        ← Admin-managed static content pages (slug-based)
  page_categories     ← Hierarchical categories for static pages
```

---

## Content Tables

### 1. `broadcasts`

Replaces Payload `Broadcast` collection. News, press releases, announcements.

```sql
CREATE TABLE broadcasts (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500),
    slug            VARCHAR(600) NOT NULL UNIQUE,
    content_ms      TEXT,
    content_en      TEXT,
    excerpt_ms      VARCHAR(1000),
    excerpt_en      VARCHAR(1000),
    featured_image  VARCHAR(2048),          -- S3 key or URL
    type            VARCHAR(50) DEFAULT 'announcement',
                                            -- announcement | press_release | news
    status          VARCHAR(20) DEFAULT 'draft',
                                            -- draft | published
    published_at    TIMESTAMPTZ,
    created_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_broadcasts_status_published ON broadcasts(status, published_at DESC);
CREATE INDEX idx_broadcasts_slug ON broadcasts(slug);
CREATE INDEX idx_broadcasts_search ON broadcasts USING GIN(
    to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(title_en,''))
);
```

### 2. `achievements`

Replaces Payload `Achievement` collection. Ministry milestones and accomplishments.

```sql
CREATE TABLE achievements (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500),
    slug            VARCHAR(600) NOT NULL UNIQUE,
    description_ms  TEXT,
    description_en  TEXT,
    date            DATE NOT NULL,
    icon            VARCHAR(2048),          -- S3 key
    is_featured     BOOLEAN DEFAULT FALSE,
    status          VARCHAR(20) DEFAULT 'draft',
    published_at    TIMESTAMPTZ,
    created_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_achievements_date ON achievements(date DESC);
CREATE INDEX idx_achievements_status ON achievements(status, is_featured);
```

Note: kd-portal homepage fetches last 7 achievements sorted by date, excluding items marked `not_achievement`.

### 3. `celebrations`

Replaces Payload `Celebration` collection. Special events and celebrations.

```sql
CREATE TABLE celebrations (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500),
    slug            VARCHAR(600) UNIQUE,
    description_ms  TEXT,
    description_en  TEXT,
    event_date      DATE,
    image           VARCHAR(2048),          -- S3 key
    status          VARCHAR(20) DEFAULT 'draft',
    published_at    TIMESTAMPTZ,
    created_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### 4. `staff_directories`

Replaces Payload `Directory` collection. Ministry staff listings.

```sql
CREATE TABLE staff_directories (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    position_ms     VARCHAR(500),
    position_en     VARCHAR(500),
    department_ms   VARCHAR(255),
    department_en   VARCHAR(255),
    division_ms     VARCHAR(255),
    division_en     VARCHAR(255),
    email           VARCHAR(255),
    phone           VARCHAR(50),
    fax             VARCHAR(50),
    photo           VARCHAR(2048),          -- S3 key
    sort_order      INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_staff_name ON staff_directories(name);
CREATE INDEX idx_staff_department ON staff_directories(department_ms, department_en);
CREATE INDEX idx_staff_search ON staff_directories USING GIN(
    to_tsvector('simple', COALESCE(name,'') || ' ' || COALESCE(position_ms,'') || ' ' || COALESCE(department_ms,''))
);
```

### 5. `policies`

Replaces Payload `Policy` collection. Policy documents available for download.

```sql
CREATE TABLE policies (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500),
    slug            VARCHAR(600) NOT NULL UNIQUE,
    description_ms  TEXT,
    description_en  TEXT,
    category        VARCHAR(100),
    file_url        VARCHAR(2048),          -- S3 key (PDF)
    file_size       BIGINT,                 -- bytes
    download_count  INTEGER DEFAULT 0,
    status          VARCHAR(20) DEFAULT 'draft',
    published_at    TIMESTAMPTZ,
    created_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_policies_category ON policies(category);
CREATE INDEX idx_policies_status ON policies(status, published_at DESC);
CREATE INDEX idx_policies_search ON policies USING GIN(
    to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(title_en,''))
);
```

### 6. `files`

Replaces Payload `File` collection. General downloadable files.

```sql
CREATE TABLE files (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500),
    title_en        VARCHAR(500),
    description_ms  TEXT,
    description_en  TEXT,
    filename        VARCHAR(500) NOT NULL,
    file_url        VARCHAR(2048) NOT NULL,  -- S3 key
    mime_type       VARCHAR(100),
    file_size       BIGINT,
    category        VARCHAR(100),
    download_count  INTEGER DEFAULT 0,
    is_public       BOOLEAN DEFAULT TRUE,
    created_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### 7. `hero_banners`

Replaces Payload `HeroBanner` collection. Homepage carousel slides.

```sql
CREATE TABLE hero_banners (
    id              BIGSERIAL PRIMARY KEY,
    title_ms        VARCHAR(500),
    title_en        VARCHAR(500),
    subtitle_ms     TEXT,
    subtitle_en     TEXT,
    image           VARCHAR(2048) NOT NULL,  -- S3 key
    image_alt_ms    VARCHAR(500),
    image_alt_en    VARCHAR(500),
    cta_label_ms    VARCHAR(200),
    cta_label_en    VARCHAR(200),
    cta_url         VARCHAR(2048),
    sort_order      INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hero_active_order ON hero_banners(is_active, sort_order);
```

### 8. `quick_links`

Replaces Payload `QuickLink` collection. Homepage quick navigation links.

```sql
CREATE TABLE quick_links (
    id              BIGSERIAL PRIMARY KEY,
    label_ms        VARCHAR(200) NOT NULL,
    label_en        VARCHAR(200),
    url             VARCHAR(2048) NOT NULL,
    icon            VARCHAR(100),            -- icon name or S3 key
    sort_order      INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### 9. `media`

Replaces Payload `Media` collection. General media asset library.

```sql
CREATE TABLE media (
    id              BIGSERIAL PRIMARY KEY,
    filename        VARCHAR(500) NOT NULL,
    original_name   VARCHAR(500),
    file_url        VARCHAR(2048) NOT NULL,  -- S3 key
    mime_type       VARCHAR(100),
    file_size       BIGINT,
    width           INTEGER,
    height          INTEGER,
    alt_ms          VARCHAR(500),
    alt_en          VARCHAR(500),
    caption_ms      TEXT,
    caption_en      TEXT,
    uploaded_by     BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### 10. `feedbacks`

Replaces Payload `Feedback` collection. User-submitted feedback.

```sql
CREATE TABLE feedbacks (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255),
    email           VARCHAR(255),
    subject         VARCHAR(500),
    message         TEXT NOT NULL,
    page_url        VARCHAR(2048),           -- Which page was feedback from
    rating          SMALLINT,               -- Optional star rating 1-5
    status          VARCHAR(20) DEFAULT 'new',
                                            -- new | read | replied | archived
    reply           TEXT,
    replied_at      TIMESTAMPTZ,
    replied_by      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    ip_address      INET,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_feedback_status ON feedbacks(status, created_at DESC);
```

### 11. `search_overrides`

Replaces Payload `Search-Overrides` collection. Custom search result boosting.

```sql
CREATE TABLE search_overrides (
    id              BIGSERIAL PRIMARY KEY,
    query           VARCHAR(500) NOT NULL,   -- Search keyword to match
    title_ms        VARCHAR(500),
    title_en        VARCHAR(500),
    url             VARCHAR(2048),
    description_ms  TEXT,
    description_en  TEXT,
    priority        INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

---

## Site Configuration Tables

These replace Payload CMS Globals (singleton site-wide configuration).

### `settings`

Replaces Payload Global: `SiteInfo`

```sql
CREATE TABLE settings (
    key             VARCHAR(255) PRIMARY KEY,
    value           TEXT,
    type            VARCHAR(50) DEFAULT 'string',  -- string | json | boolean
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- Default values:
INSERT INTO settings VALUES
    ('site_name_ms', 'Kementerian Digital Malaysia', 'string', NOW()),
    ('site_name_en', 'Ministry of Digital Malaysia', 'string', NOW()),
    ('site_description_ms', '...', 'string', NOW()),
    ('site_description_en', '...', 'string', NOW()),
    ('google_analytics_id', '', 'string', NOW()),
    ('facebook_url', '', 'string', NOW()),
    ('twitter_url', '', 'string', NOW()),
    ('instagram_url', '', 'string', NOW()),
    ('youtube_url', '', 'string', NOW()),
    ('site_default_theme', 'default', 'string', NOW()),
                                            -- valid values: keys in config/themes.php valid_themes array

    -- AI configuration (Phase 6) — managed via ManageAiSettings Filament page
    ('ai_llm_provider',          'anthropic',                 'string',    NOW()),
    ('ai_llm_model',             'claude-sonnet-4-6',         'string',    NOW()),
    ('ai_llm_api_key',           '',                          'encrypted', NOW()),
    ('ai_llm_base_url',          '',                          'string',    NOW()),
    -- ai_llm_base_url only needed for openai-compatible providers (Qwen, Moonshot, DeepSeek, etc.)

    ('ai_embedding_provider',    'openai',                    'string',    NOW()),
    ('ai_embedding_model',       'text-embedding-3-small',    'string',    NOW()),
    ('ai_embedding_api_key',     '',                          'encrypted', NOW()),
    ('ai_embedding_dimension',   '1536',                      'integer',   NOW()),
    -- ai_embedding_dimension must match content_embeddings.embedding column dimension
    -- changing requires full re-index: php artisan govportal:reindex-embeddings

    ('ai_chatbot_enabled',       'false',                     'boolean',   NOW()),
    ('ai_admin_editor_enabled',  'false',                     'boolean',   NOW()),
    ('ai_chatbot_rate_limit',    '10',                        'integer',   NOW());
    -- ai_chatbot_rate_limit: messages per hour per IP address
```

> **Encrypted settings:** Keys with `type = 'encrypted'` are stored via `Crypt::encrypt()` and read via `Crypt::decrypt()` in `AiService`. The `Setting::get()` helper handles encryption/decryption transparently when `type = 'encrypted'`.

### `content_embeddings` (Phase 6 — AI)

Stores vector embeddings for all embeddable content models. Used by the RAG pipeline.

```sql
CREATE TABLE content_embeddings (
    id              BIGSERIAL PRIMARY KEY,
    embeddable_type VARCHAR(255) NOT NULL,   -- e.g., 'App\Models\Broadcast'
    embeddable_id   BIGINT NOT NULL,
    chunk_index     SMALLINT NOT NULL DEFAULT 0,
    locale          VARCHAR(5) NOT NULL,     -- 'ms' or 'en'
    content         TEXT NOT NULL,           -- raw chunk text (debugging / re-index)
    embedding       vector(1536) NOT NULL,   -- dimension set by PGVECTOR_DIMENSION env var
    metadata        JSONB,                   -- {title, slug, url, type, provider, model, dimension}
    created_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at      TIMESTAMP WITH TIME ZONE DEFAULT NOW(),

    UNIQUE (embeddable_type, embeddable_id, chunk_index, locale)
);

CREATE INDEX idx_ce_morphic ON content_embeddings (embeddable_type, embeddable_id);

-- Add after > 10,000 rows for performance:
-- CREATE INDEX idx_ce_vector ON content_embeddings
--     USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100);
```

**Notes:**
- `embedding vector(1536)` — dimension is fixed at table creation by `PGVECTOR_DIMENSION` env var (default `1536`)
- To use a different embedding model with different dimensions, set `PGVECTOR_DIMENSION` before migrations and re-run `php artisan govportal:reindex-embeddings`
- `metadata.provider` and `metadata.model` record which provider/model generated this embedding (for audit and re-index detection)

### `menus`

Registry of named menus. Replaces Payload Global: `Header` (previously `navigation_items`).

```sql
CREATE TABLE menus (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,  -- 'public_header' | 'public_footer' | 'admin_sidebar'
    label_ms    VARCHAR(255) NOT NULL,
    label_en    VARCHAR(255),
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

-- Pre-seeded rows:
INSERT INTO menus (name, label_ms, label_en) VALUES
    ('public_header', 'Menu Utama', 'Main Menu'),
    ('public_footer', 'Menu Footer', 'Footer Menu'),
    ('admin_sidebar', 'Menu Admin', 'Admin Menu');
```

### `menu_items`

Individual items within a named menu. Supports 4-level nesting via self-referential `parent_id`. Level depth is enforced at the application layer (Filament validation), not the database.

```sql
CREATE TABLE menu_items (
    id              BIGSERIAL PRIMARY KEY,
    menu_id         BIGINT NOT NULL REFERENCES menus(id) ON DELETE CASCADE,
    parent_id       BIGINT REFERENCES menu_items(id) ON DELETE CASCADE,
                                            -- null = Level 1; 1 hop = Level 2; 2 hops = Level 3; 3 hops = Level 4 (max)
    label_ms        VARCHAR(255) NOT NULL,
    label_en        VARCHAR(255),
    url             VARCHAR(2048),          -- external absolute URL (nullable if route_name set)
    route_name      VARCHAR(255),           -- named Laravel route (nullable if url set)
    route_params    JSONB,                  -- params for route() helper, e.g. {"locale":"ms"}
    icon            VARCHAR(100),           -- Heroicon name or custom icon identifier
    sort_order      SMALLINT DEFAULT 0,
    target          VARCHAR(10) DEFAULT '_self',  -- '_self' | '_blank'
    is_active       BOOLEAN DEFAULT TRUE,
    required_roles  JSONB,                  -- null = visible to all; ["super_admin","publisher"] = restricted
                                            -- applied server-side; filtered items never reach DOM
    mega_columns    SMALLINT DEFAULT 1,     -- Level 1 only: how many columns for Level 2 children in mega panel (1–4)
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_menu_items_menu ON menu_items (menu_id, sort_order);
CREATE INDEX idx_menu_items_parent ON menu_items (parent_id);
```

**Level rules (enforced by application):**

| Level | `parent_id` depth | Can have children? |
|-------|--------------------|-------------------|
| 1 — Main menu | `IS NULL` | Yes (Level 2) |
| 2 — Sub menu | 1 hop from root | Yes (Level 3) |
| 3 — Inner menu | 2 hops from root | Yes (Level 4) |
| 4 — Child menu | 3 hops from root | **No** — leaf node |

### `static_pages`

CMS-managed static pages served at `/{locale}/{slug}`. Replaces hard-coded Penafian/Dasar Privasi settings.

```sql
CREATE TABLE static_pages (
    id              BIGSERIAL PRIMARY KEY,
    category_id     BIGINT REFERENCES page_categories(id) ON DELETE SET NULL,
    title_ms        VARCHAR(500) NOT NULL,
    title_en        VARCHAR(500),
    slug            VARCHAR(600) NOT NULL UNIQUE,
    content_ms      TEXT,
    content_en      TEXT,
    excerpt_ms      VARCHAR(1000),
    excerpt_en      VARCHAR(1000),
    status          VARCHAR(20) DEFAULT 'draft',   -- 'draft' | 'published'
    is_in_sitemap   BOOLEAN DEFAULT TRUE,
    meta_title_ms   VARCHAR(255),
    meta_title_en   VARCHAR(255),
    meta_desc_ms    VARCHAR(500),
    meta_desc_en    VARCHAR(500),
    sort_order      SMALLINT DEFAULT 0,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_static_pages_slug ON static_pages (slug);
CREATE INDEX idx_static_pages_category ON static_pages (category_id);

-- Pre-seeded rows (migrated from settings table):
INSERT INTO static_pages (title_ms, title_en, slug, status) VALUES
    ('Penafian', 'Disclaimer', 'penafian', 'published'),
    ('Dasar Privasi', 'Privacy Policy', 'dasar-privasi', 'published');
```

### `page_categories`

Hierarchical categories for static pages. Self-referential — unlimited nesting depth, enforced at the application layer.

```sql
CREATE TABLE page_categories (
    id              BIGSERIAL PRIMARY KEY,
    parent_id       BIGINT REFERENCES page_categories(id) ON DELETE SET NULL,
                                            -- null = root category; children reference their parent
    name_ms         VARCHAR(255) NOT NULL,
    name_en         VARCHAR(255),
    slug            VARCHAR(300) NOT NULL UNIQUE,
    description_ms  TEXT,
    description_en  TEXT,
    sort_order      SMALLINT DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_page_categories_parent ON page_categories (parent_id);
```

**Nesting example:**

```
Maklumat Korporat (root)
  └── Latar Belakang
  └── Visi & Misi
  └── Struktur Organisasi
Dasar & Undang-Undang (root)
  └── Dasar Nasional
      └── Dasar Digital
      └── Dasar ICT
  └── Undang-Undang
```

### `footer_settings`

Replaces Payload Global: `Footer`

```sql
CREATE TABLE footer_settings (
    id              BIGSERIAL PRIMARY KEY,
    section         VARCHAR(100) NOT NULL,   -- links | social | legal
    label_ms        VARCHAR(200),
    label_en        VARCHAR(200),
    url             VARCHAR(2048),
    sort_order      INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### `minister_profiles`

Replaces Payload Global: `MinisterProfile`

```sql
CREATE TABLE minister_profiles (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    title_ms        VARCHAR(500),
    title_en        VARCHAR(500),
    bio_ms          TEXT,
    bio_en          TEXT,
    photo           VARCHAR(2048),           -- S3 key
    is_current      BOOLEAN DEFAULT TRUE,
    appointed_at    DATE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### `addresses`

Replaces Payload Global: `Addresses`

```sql
CREATE TABLE addresses (
    id              BIGSERIAL PRIMARY KEY,
    label_ms        VARCHAR(200),
    label_en        VARCHAR(200),
    address_ms      TEXT,
    address_en      TEXT,
    phone           VARCHAR(50),
    fax             VARCHAR(50),
    email           VARCHAR(255),
    google_maps_url VARCHAR(2048),
    sort_order      INTEGER DEFAULT 0,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### `feedback_settings`

Replaces Payload Global: `FeedbackSettings`

```sql
CREATE TABLE feedback_settings (
    key             VARCHAR(255) PRIMARY KEY,
    value           TEXT,
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- Default:
INSERT INTO feedback_settings VALUES
    ('is_enabled', 'true', NOW()),
    ('recipient_email', '', NOW()),
    ('success_message_ms', 'Terima kasih atas maklum balas anda.', NOW()),
    ('success_message_en', 'Thank you for your feedback.', NOW());
```

---

## Auth & RBAC Tables

### `users`

CMS admin users (Filament). Not for public authentication.

```sql
CREATE TABLE users (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    last_login_at   TIMESTAMPTZ,
    remember_token  VARCHAR(100),
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);
```

### Spatie Permission Tables

Standard Spatie Laravel Permission tables (generated by package migration):

```
model_has_roles        — assigns roles to users
model_has_permissions  — assigns permissions directly to users
role_has_permissions   — assigns permissions to roles
roles                  — named roles (super_admin, content_editor, publisher)
permissions            — named permissions (view broadcasts, create broadcasts, etc.)
```

**Default Roles (seeded):**

| Role | Description |
|------|-------------|
| `super_admin` | Full system access |
| `content_editor` | Create and edit content; cannot publish |
| `publisher` | Approve and publish content |
| `viewer` | Read-only CMS access |

**Default Permissions (seeded):**

Each content type gets: `view_*`, `create_*`, `edit_*`, `delete_*`, `publish_*`

Content types: `broadcasts`, `achievements`, `celebrations`, `staff_directories`, `policies`, `files`, `hero_banners`, `quick_links`, `media`, `feedbacks`

---

## Search Index (Laravel Scout)

PostgreSQL full-text search using `pg_search` / custom Scout driver:

```sql
CREATE TABLE searchable_content (
    id              BIGSERIAL PRIMARY KEY,
    searchable_type VARCHAR(100) NOT NULL,   -- App\Models\Broadcast, etc.
    searchable_id   BIGINT NOT NULL,
    title_ms        TEXT,
    title_en        TEXT,
    content_ms      TEXT,
    content_en      TEXT,
    url_ms          VARCHAR(2048),
    url_en          VARCHAR(2048),
    priority        INTEGER DEFAULT 0,       -- from Search-Overrides
    tsvector_ms     TSVECTOR GENERATED ALWAYS AS (
                        to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(content_ms,''))
                    ) STORED,
    tsvector_en     TSVECTOR GENERATED ALWAYS AS (
                        to_tsvector('english', COALESCE(title_en,'') || ' ' || COALESCE(content_en,''))
                    ) STORED,
    updated_at      TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (searchable_type, searchable_id)
);

CREATE INDEX idx_search_ms ON searchable_content USING GIN(tsvector_ms);
CREATE INDEX idx_search_en ON searchable_content USING GIN(tsvector_en);
```

Search priority (matching kd-portal Payload search plugin config):
- Achievements: priority 10
- Broadcasts: priority 20
- Staff Directories: priority 30
- Policies: priority 40

---

## Migration Order

Run migrations in this order to respect foreign key constraints:

1. `users`
2. `roles`, `permissions` (Spatie)
3. `media`
4. `hero_banners`
5. `quick_links`
6. `broadcasts`
7. `achievements`
8. `celebrations`
9. `staff_directories`
10. `policies`
11. `files`
12. `feedbacks`
13. `search_overrides`
14. `searchable_content`
15. `settings`
16. `navigation_items`
17. `footer_settings`
18. `minister_profiles`
19. `addresses`
20. `feedback_settings`

---

## Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| Separate `_ms`/`_en` columns (not a translations table) | Simpler queries; matches kd-portal's two-locale design (ms-MY, en-GB only) |
| S3 URLs stored as strings | Matches kd-portal's S3 storage setup; avoids premature abstraction |
| No department/tenant partitioning | kd-portal is a single-ministry site; no multi-tenancy needed |
| PostgreSQL FTS instead of Elasticsearch | Sufficient for the site's scale; avoids extra infrastructure |
| `settings` key-value table | Flexible for infrequent global config changes |
