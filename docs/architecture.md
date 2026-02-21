# 🏗️ Architecture

## System Overview

OpenGovPortal is designed as a high-performance, scalable government portal capable of serving **10,000+ concurrent users** with sub-second response times.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                           CLIENTS                               │
│  Mobile Apps │ Web Browsers │ Third-party APIs │ Kiosks        │
└───────────────────────┬─────────────────────────────────────────┘
                        │ HTTPS
┌───────────────────────▼─────────────────────────────────────────┐
│                        CDN LAYER                                │
│  Cloudflare                                                     │
│  - Static asset caching (CSS, JS, images)                      │
│  - Full-page HTML caching                                      │
│  - DDoS protection + WAF                                       │
│  - SSL/TLS termination                                         │
│  - HTTP/3 at edge                                              │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│                AWS ALB (Load Balancer)                          │
│  - Request distribution across FrankenPHP pods                 │
│  - Health checks (/up endpoint)                                │
│  - Rate limiting (100 req/min per IP, via ALB rules)           │
│  - SSL passthrough to FrankenPHP                               │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│              APPLICATION SERVERS (FrankenPHP)                   │
│  Laravel Octane + FrankenPHP — Multiple Kubernetes Pods        │
│                                                                 │
│  ┌─────────────────┐  ┌─────────────────┐  ┌───────────────┐  │
│  │   Pod 1         │  │   Pod 2         │  │   Pod N       │  │
│  │  FrankenPHP     │  │  FrankenPHP     │  │  FrankenPHP   │  │
│  │  (Caddy built-in│  │  (Caddy built-in│  │  ...          │  │
│  │  + PHP workers) │  │  + PHP workers) │  │               │  │
│  └─────────────────┘  └─────────────────┘  └───────────────┘  │
│                                                                 │
│  - Caddy web server built-in (no separate Nginx needed)        │
│  - Application booted once, kept in memory per worker          │
│  - HTTP/2 + HTTP/3 natively via Caddy                         │
│  - Automatic TLS (Caddy handles certs, CDN handles edge)       │
│  - Better Livewire worker isolation vs Swoole                  │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│                      CACHE LAYER                                │
│  Redis Cluster (Primary + 2 Replicas)                          │
│                                                                 │
│  ┌─────────────────────────────────────────┐                   │
│  │  Full-Page Cache                        │                   │
│  │  - Generated HTML stored                │                   │
│  │  - Tag-based invalidation               │                   │
│  └─────────────────────────────────────────┘                   │
│                                                                 │
│  ┌─────────────────────────────────────────┐                   │
│  │  Query Cache                            │                   │
│  │  - Database query results               │                   │
│  │  - Cache tags: user, role, dept         │                   │
│  └─────────────────────────────────────────┘                   │
│                                                                 │
│  ┌─────────────────────────────────────────┐                   │
│  │  Session Storage                        │                   │
│  │  - User sessions                        │                   │
│  │  - CSRF tokens                          │                   │
│  └─────────────────────────────────────────┘                   │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│                     DATABASE LAYER                              │
│  PostgreSQL (Primary-Replica Setup)                            │
│                                                                 │
│  ┌─────────────┐         ┌─────────────┐ ┌─────────────┐      │
│  │   PRIMARY   │────────▶│  REPLICA 1  │ │  REPLICA 2  │      │
│  │   (Write)   │  async  │   (Read)    │ │   (Read)    │      │
│  └─────────────┘         └─────────────┘ └─────────────┘      │
│                                                                 │
│  - PgBouncer for connection pooling                            │
│  - Read/write splitting                                        │
│  - Automated backups                                           │
│  - Point-in-time recovery                                      │
└─────────────────────────────────────────────────────────────────┘
```

## Component Details

### 1. CDN Layer (Cloudflare)

**Purpose:** Reduce latency and offload traffic from origin servers

**Configuration:**
```yaml
Caching Rules:
  - Pattern: *.css, *.js, *.png, *.jpg
    TTL: 1 year
    Cache Level: Cache Everything
    
  - Pattern: /announcements/*
    TTL: 1 hour
    Cache Level: Cache Everything
    Bypass Cache on Cookie: gov_session
    
  - Pattern: /admin/*
    TTL: 0 (no cache)
    
Page Rules:
  - Always Use HTTPS
  - Auto Minify: CSS, JS, HTML
  - Rocket Loader: Off (conflicts with Octane)
  - Polish: Lossless
```

### 2. Load Balancer (AWS ALB)

FrankenPHP includes Caddy as the built-in web server, so a separate Nginx reverse proxy is **not needed**. AWS ALB distributes traffic directly to FrankenPHP pods.

**ALB target group health check:**
```
Protocol: HTTP
Path: /up
Port: 8000
Healthy threshold: 2
Unhealthy threshold: 3
Interval: 30s
```

**Rate limiting:** Applied at the Cloudflare WAF level (100 req/min per IP for public routes, 5/hour for contact form submissions).

### 3. Laravel Octane + FrankenPHP

**Why FrankenPHP over Swoole for this project:**

| Factor | Decision |
|--------|---------|
| Nginx not needed | FrankenPHP/Caddy is the web server — one fewer service in Kubernetes |
| Livewire isolation | Better worker process isolation vs Swoole shared memory |
| Docker/K8s fit | Official `dunglas/frankenphp` image; no custom PHP extension compilation |
| `Octane::table()` | Not used — Redis handles all shared state; Swoole-only feature not needed |
| Laravel direction | Default server in Laravel 12; actively maintained by Laravel team |

**Why Octane at all:**
- Traditional PHP-FPM: Boot Laravel on every request (~50-100ms overhead)
- FrankenPHP worker mode: Boot once, keep in memory (~1-2ms overhead)
- **Performance gain: 10-20x faster than PHP-FPM**

**Octane configuration:**
```php
// config/octane.php
return [
    'server' => 'frankenphp',

    'frankenphp' => [
        'workers' => env('OCTANE_WORKERS', 8),  // CPU cores * 2
        'max_requests' => env('OCTANE_MAX_REQUESTS', 500),
    ],
];
```

**Dockerfile (FrankenPHP):**
```dockerfile
FROM dunglas/frankenphp:latest-php8.3

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    redis \
    pcntl \
    zip \
    gd

# Copy application
COPY . /app
WORKDIR /app

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

EXPOSE 8000
CMD ["php", "artisan", "octane:frankenphp", "--port=8000", "--workers=8"]
```

**Kubernetes pod spec:**
```yaml
# .kube/deployment.yaml (excerpt)
containers:
  - name: govportal
    image: your-registry/govportal:latest
    ports:
      - containerPort: 8000
    env:
      - name: OCTANE_WORKERS
        value: "8"
      - name: OCTANE_MAX_REQUESTS
        value: "500"
    livenessProbe:
      httpGet:
        path: /up
        port: 8000
      initialDelaySeconds: 10
      periodSeconds: 30
    resources:
      requests:
        cpu: "500m"
        memory: "512Mi"
      limits:
        cpu: "2000m"
        memory: "2Gi"
```

**Important FrankenPHP + Livewire config:**
```php
// config/livewire.php
return [
    'inject_assets' => true,
    'navigate' => false,  // Keep off — verify Octane compatibility before enabling
];
```

### 4. Redis Cache Layer

**Structure:**
```
Redis DB 0: Full-Page Cache
  key: "page:/ms/siaran"
  value: <html>...</html>
  ttl: 3600

Redis DB 1: Query Cache
  key: "query:broadcasts:latest:6:ms"
  value: [ {...}, {...} ]
  ttl: 600

Redis DB 2: Sessions
  key: "session:abc123"
  value: { user_id: 123, role: 'editor' }
  ttl: 7200

Redis DB 3: Rate Limiting
  key: "ratelimit:192.168.1.1"
  value: 45
  ttl: 60
```

### 5. PostgreSQL Database

**Schema Design Principles:**
- Normalize data (3NF)
- Strategic denormalization for read-heavy tables
- Proper indexing
- Partitioning for large tables

**Connection Pooling (PgBouncer):**
```ini
[databases]
govportal = host=localhost port=5432 dbname=govportal

[pgbouncer]
pool_mode = transaction
max_client_conn = 10000
default_pool_size = 20
min_pool_size = 10
reserve_pool_size = 5
```

## Scaling Strategy

### Horizontal Scaling

```
Phase 1 (1-10K users):
  1x Application Server (Octane)
  1x PostgreSQL (Primary)
  1x Redis
  
Phase 2 (10-50K users):
  3x Application Server (Octane)
  1x PostgreSQL (Primary) + 1 Replica
  1x Redis (Cluster)
  
Phase 3 (50-100K users):
  5-10x Application Server (Octane)
  1x PostgreSQL (Primary) + 3 Replicas
  3x Redis (Cluster)
  CDN fully utilized
```

### Database Scaling

**Read Replicas:**
```php
// config/database.php
'pgsql' => [
    'read' => [
        'host' => ['192.168.1.10', '192.168.1.11', '192.168.1.12'],
    ],
    'write' => [
        'host' => ['192.168.1.10'],
    ],
    'sticky' => true, // Read your writes
],
```

**Usage:**
```php
// Writes go to primary
User::create([...]);

// Reads distributed to replicas
$users = User::all(); // From replica
```

## Security Architecture

### Defense in Depth

```
Layer 1: CDN (DDoS protection, WAF)
Layer 2: Load Balancer (Rate limiting, IP blocking)
Layer 3: Application (CSRF, XSS, SQL injection protection)
Layer 4: Authentication (RBAC, MFA)
Layer 5: Database (Row-level security, encryption)
```

### Security Headers

```php
// middleware/SecurityHeaders.php
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
$response->headers->set('Content-Security-Policy', "default-src 'self'");
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
```

## Monitoring & Logging

### Application Monitoring

```php
// Log slow queries
DB::listen(function ($query) {
    if ($query->time > 100) {
        Log::warning('Slow query', [
            'sql' => $query->sql,
            'time' => $query->time,
        ]);
    }
});

// Log cache hits/misses
Cache::events()->listen(function ($event) {
    if ($event instanceof CacheHit) {
        Statsd::increment('cache.hit');
    } elseif ($event instanceof CacheMissed) {
        Statsd::increment('cache.miss');
    }
});
```

### Infrastructure Monitoring

- **Prometheus** + **Grafana** for metrics
- **ELK Stack** (Elasticsearch, Logstash, Kibana) for logs
- **Sentry** for error tracking

## Cost Estimation (AWS)

| Component | Specs | Monthly Cost |
|-----------|-------|--------------|
| EC2 (App Servers) | 3x t3.large | ~$180 |
| RDS PostgreSQL | db.r5.large + 2 replicas | ~$400 |
| ElastiCache Redis | cache.r5.large | ~$150 |
| ALB | Load balancer | ~$20 |
| Cloudflare | Pro plan | ~$20 |
| S3 + CloudFront | Static assets | ~$50 |
| **Total** | | **~$820/mo** |

*Can scale down for lower traffic or use smaller instances for development.*

## AI Services Layer

### Overview

The AI features run as an additional service layer on top of the existing architecture. No separate AI infrastructure is required — all AI work happens within the Laravel application process via Prism PHP, with vector storage in the existing PostgreSQL cluster.

```
┌─────────────────────────────────────────────────────────────────┐
│                        AI SERVICES LAYER                        │
│                   (all providers configurable                   │
│                    via ManageAiSettings in /admin)              │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │               LLM PROVIDER (admin-configurable)          │  │
│  │  Anthropic │ OpenAI │ Google Gemini │ Groq │ Mistral     │  │
│  │  xAI │ Ollama (local) │ OpenAI-compatible endpoint       │  │
│  │  (Qwen/DashScope, Moonshot, DeepSeek, Together AI, ...)  │  │
│  │                                                          │  │
│  │  - Chatbot responses      - Grammar check BM/EN          │  │
│  │  - Translate BM ↔ EN     - Expand / Summarise            │  │
│  │  - TLDR generation        - Generate from prompt/image   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌───────────────────────────┐  ┌─────────────────────────┐   │
│  │ EMBEDDING PROVIDER        │  │ pgvector (PostgreSQL)    │   │
│  │ (admin-configurable)      │  │ content_embeddings table │   │
│  │ OpenAI │ Google │ Cohere  │  │ - morphic (type + id)    │   │
│  │ VoyageAI │ Ollama (local) │  │ - chunk_index, locale    │   │
│  │                           │  │ - embedding vector(n)    │   │
│  │ Dimension varies by model │  │ - metadata JSON          │   │
│  └───────────────────────────┘  └─────────────────────────┘   │
│                                                                 │
│  Prism PHP (echolabsdev/prism) — unified interface;            │
│  AiService resolves active provider from settings table        │
└─────────────────────────────────────────────────────────────────┘
```

### RAG Pipeline (Retrieval-Augmented Generation)

Content is embedded on every save and queried at chat time:

```
WRITE PATH (content save):
  Model saved → EmbeddingObserver → dispatch GenerateEmbeddingJob
  → GenerateEmbeddingJob (queued, async):
      1. Chunk content into ~500-token segments (ms + en separately)
      2. Call OpenAI text-embedding-3-small via Prism PHP
      3. Upsert into content_embeddings (embeddable_type, embeddable_id, chunk_index, locale)
      4. Log embedding stats for monitoring

READ PATH (chatbot query):
  User message → AiChat Livewire component:
      1. Embed user query via OpenAI (same model for consistent vector space)
      2. pgvector similarity search: top-5 chunks by cosine distance, filtered by locale
      3. Build context prompt: system prompt + retrieved chunks + conversation history
      4. Call Claude claude-sonnet-4-6 via Prism PHP (streaming)
      5. Return response; append to session-only conversation history
```

### Models That Generate Embeddings

| Model | Fields embedded | Priority |
|-------|----------------|---------|
| `Broadcast` | `title_{locale}`, `content_{locale}`, `excerpt_{locale}` | High |
| `Achievement` | `title_{locale}`, `description_{locale}` | High |
| `Policy` | `title_{locale}`, `description_{locale}` | Medium |
| `StaffDirectory` | `name`, `position_{locale}`, `department_{locale}` | Low |
| Settings (`homepage_*`, `disclaimer_*`, `privacy_policy_*`) | value | Medium |

### Admin AI Editor (Filament)

AI actions are implemented as **Filament custom actions** on RichEditor fields. They call Claude synchronously (no job queue) since admin users expect immediate feedback.

```
Admin opens content editor in Filament
→ Clicks AI action button on RichEditor field
→ Filament Action calls AiService::handle($operation, $text, $locale)
→ Prism PHP calls Claude claude-sonnet-4-6
→ Returns result; Filament replaces/appends field content
```

**Available operations:**

| Action | Trigger | Input | Output |
|--------|---------|-------|--------|
| Grammar Check (BM) | Button | Selected text | Corrected text + explanation |
| Grammar Check (EN) | Button | Selected text | Corrected text + explanation |
| Translate BM → EN | Button | Full field content | English translation |
| Translate EN → BM | Button | Full field content | Bahasa Malaysia translation |
| Expand | Button | Selected text | Longer, more detailed version |
| Summarise | Button | Full field content | Condensed version |
| Auto TLDR | Button | Full field content | 2-3 sentence summary (saved to `excerpt_{locale}`) |
| Generate from Prompt | Modal | Text prompt | Draft content |
| Generate from Image | Modal | Image URL + prompt | Description or content based on image |

### Rate Limiting

| Feature | Limit | Scope |
|---------|-------|-------|
| Public chatbot | 10 messages/hour | Per IP (Redis rate limiter) |
| Admin AI editor | No limit | Authenticated users only |
| Embedding job | Throttled via queue | 5 concurrent workers max |

### Privacy & PDPA Compliance

- No PII (names, emails, ICs) is stored in `content_embeddings`
- Conversation history stored in PHP session only — not persisted to database
- Chat logs (if any) anonymised — no user identification
- User messages sent to Anthropic API per their privacy policy; inform users via chatbot disclaimer

### Environment Variables (Fallback Defaults)

All values below are **fallback defaults** only — they are overridden by the admin-configured settings stored in the `settings` table via `ManageAiSettings`. Use `.env` for local development; use the admin panel in production.

```env
# LLM provider fallback
AI_LLM_PROVIDER=anthropic         # anthropic | openai | google | groq | mistral | xai | ollama | openai-compatible
AI_LLM_MODEL=claude-sonnet-4-6
AI_LLM_API_KEY=sk-ant-...
AI_LLM_BASE_URL=                   # only for openai-compatible (e.g., Qwen, Moonshot)

# Embedding provider fallback
AI_EMBEDDING_PROVIDER=openai
AI_EMBEDDING_MODEL=text-embedding-3-small
AI_EMBEDDING_API_KEY=sk-...
AI_EMBEDDING_DIMENSION=1536        # must match pgvector column; changing requires reindex

# pgvector
PGVECTOR_ENABLED=true
PGVECTOR_DIMENSION=1536            # set before running migrations; changing requires column rebuild

# Feature flags (also configurable in ManageAiSettings)
AI_CHATBOT_ENABLED=false
AI_ADMIN_EDITOR_ENABLED=false
AI_CHATBOT_RATE_LIMIT=10
```

---

## Next Steps

1. [Documentation Guide](README.md)
2. [Caching Strategy](caching.md)
3. [Database Schema](database-schema.md)
4. [Agentic Coding Playbook](agentic-coding.md)
5. [AI Features](ai.md)
