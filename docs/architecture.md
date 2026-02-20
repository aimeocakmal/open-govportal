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
│  Cloudflare / AWS CloudFront                                    │
│  - Static asset caching (CSS, JS, images)                      │
│  - Full-page HTML caching                                      │
│  - DDoS protection                                             │
│  - SSL termination                                             │
│  - WAF (Web Application Firewall)                              │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│                     LOAD BALANCER                               │
│  Nginx / AWS ALB / HAProxy                                      │
│  - Request distribution                                         │
│  - Health checks                                                │
│  - Rate limiting (100 req/min per IP)                          │
│  - SSL passthrough                                              │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│                   APPLICATION SERVERS                           │
│  Laravel Octane (Swoole) - Multiple Instances                   │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐              │
│  │  Worker 1   │ │  Worker 2   │ │  Worker N   │              │
│  │  (Swoole)   │ │  (Swoole)   │ │  (Swoole)   │              │
│  └─────────────┘ └─────────────┘ └─────────────┘              │
│                                                                 │
│  - Application booted once, kept in memory                     │
│  - Handle 10,000+ concurrent connections                       │
│  - Shared nothing architecture                                 │
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

### 2. Load Balancer (Nginx)

**Configuration:**
```nginx
upstream octane_backend {
    least_conn;
    server 10.0.1.10:8000 weight=5;
    server 10.0.1.11:8000 weight=5;
    server 10.0.1.12:8000 weight=5;
    keepalive 32;
}

server {
    listen 443 ssl http2;
    server_name govportal.gov.my;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=100r/m;
    limit_req zone=api burst=20 nodelay;
    
    # Proxy to Octane
    location / {
        proxy_pass http://octane_backend;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### 3. Laravel Octane (Swoole)

**Why Octane:**
- Traditional PHP: Boot Laravel on every request (~50-100ms)
- Octane: Boot once, keep in memory (~1-2ms per request)
- **Performance gain: 10-20x faster**

**Worker Configuration:**
```php
// config/octane.php
return [
    'server' => 'swoole',
    
    'swoole' => [
        'workers' => 8,              // CPU cores * 2
        'task_workers' => 4,         // Background jobs
        'max_requests' => 1000,      // Restart worker after N requests
        'max_execution_time' => 30,  // Request timeout
    ],
    
    'cache' => [
        'rows' => 1000,              // In-memory table rows
    ],
];
```

### 4. Redis Cache Layer

**Structure:**
```
Redis DB 0: Full-Page Cache
  key: "page:/announcements:ms"
  value: <html>...</html>
  ttl: 3600

Redis DB 1: Query Cache
  key: "query:announcements:latest:10:ms"
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

## Next Steps

1. [Installation Guide](installation.md)
2. [Caching Strategy](caching.md)
3. [Database Schema](database-schema.md)
4. [Security Configuration](security.md)
