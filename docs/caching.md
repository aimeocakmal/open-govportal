# 💾 Caching Strategy

## Overview

OpenGovPortal uses a **3-layer caching architecture** to achieve sub-second response times and handle 10,000+ concurrent users.

```
User Request
     ↓
┌─────────────────────────────────────────┐
│  LAYER 1: CDN (Cloudflare)              │
│  - Check edge cache                     │
│  - If HIT: Return immediately (< 50ms)  │
│  - If MISS: Continue to origin          │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  LAYER 2: Redis (Application Cache)     │
│  - Check full-page cache                │
│  - If HIT: Return HTML (< 10ms)         │
│  - If MISS: Generate, store, return     │
└──────────────┬──────────────────────────┘
               ↓
┌─────────────────────────────────────────┐
│  LAYER 3: Database (PostgreSQL)         │
│  - Query cache (internal)               │
│  - Materialized views                   │
└─────────────────────────────────────────┘
```

## Layer 1: CDN (Cloudflare)

### What to Cache

| Content Type | TTL | Strategy |
|--------------|-----|----------|
| **Static Assets** | 1 year | Cache everything (CSS, JS, images, fonts) |
| **Public Pages** | 1 hour | Full HTML cache (anonymous users) |
| **API Responses** | 5 minutes | Cache GET requests with query params |
| **User Content** | 0 | Don't cache (authenticated pages) |

### Cloudflare Configuration

```yaml
# Page Rules (ordered by priority)

# Rule 1: Admin panel - No cache
/admin/*
  Cache Level: Bypass

# Rule 2: Static assets - Long cache
/*.css, /*.js, /*.png, /*.jpg, /*.woff2
  Cache Level: Cache Everything
  Edge Cache TTL: 1 month
  Browser Cache TTL: 1 month

# Rule 3: Public pages - Short cache (locale-prefixed routes)
/ms/*, /en/*
  Cache Level: Cache Everything
  Edge Cache TTL: 1 hour
  Browser Cache TTL: 1 hour
  Bypass Cache on Cookie: gov_session

# Rule 4: API search endpoint - Short cache
/api/direktori*
  Cache Level: Cache Everything
  Edge Cache TTL: 5 minutes
```

### Cache Invalidation

```bash
# Purge everything (emergency)
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'

# Purge specific URLs
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  --data '{"files":["https://digital.gov.my/ms/siaran","https://digital.gov.my/en/siaran"]}'
```

## Layer 2: Redis (Application Cache)

### Cache Structure

```php
// Full-page cache (HTML) — key includes locale segment from URL
Cache::store('redis')->put(
    'page:/ms/siaran',
    '<html>...</html>',
    now()->addHour()
);

// Query result cache — broadcasts listing
Cache::store('redis')->put(
    'query:broadcasts:latest:6:ms',
    $broadcasts,
    now()->addMinutes(10)
);

// Fragment cache (partial view)
Cache::store('redis')->put(
    'fragment:navigation:ms',
    $navHtml,
    now()->addHours(24)
);
```

### Implementation

#### Full-Page Cache Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle($request, Closure $next, $ttl = 3600)
    {
        // Skip cache for authenticated users
        if (auth()->check()) {
            return $next($request);
        }
        
        // Skip cache for POST/PUT/DELETE
        if (!$request->isMethod('GET')) {
            return $next($request);
        }
        
        // Generate cache key
        $cacheKey = $this->getCacheKey($request);
        
        // Check cache
        if (Cache::has($cacheKey)) {
            return response(Cache::get($cacheKey));
        }
        
        // Generate response
        $response = $next($request);
        
        // Cache the response
        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, $response->getContent(), $ttl);
        }
        
        return $response;
    }
    
    private function getCacheKey($request): string
    {
        $locale = app()->getLocale();
        $path = $request->path();
        $query = md5(serialize($request->query()));
        
        return "page:{$path}:{$locale}:{$query}";
    }
}
```

#### Usage in Routes

```php
// routes/web.php

// Public locale-prefixed pages — cached for 1 hour
Route::prefix('{locale}')->middleware(['set.locale', 'cache.response:3600'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/siaran', [BroadcastController::class, 'index']);
    Route::get('/siaran/{slug}', [BroadcastController::class, 'show']);
    Route::get('/pencapaian', [AchievementController::class, 'index']);
    Route::get('/direktori', [DirectoriController::class, 'index']);
    Route::get('/dasar', [DasarController::class, 'index']);
    Route::get('/statistik', [StatistikController::class, 'index']);
    Route::get('/profil-kementerian', [ProfilKementerianController::class, 'index']);
    Route::get('/penafian', [StaticPageController::class, 'penafian']);
    Route::get('/dasar-privasi', [StaticPageController::class, 'dasarPrivasi']);
});

// Contact form POST — never cached
Route::prefix('{locale}')->middleware('set.locale')->group(function () {
    Route::get('/hubungi-kami', [HubungiKamiController::class, 'index']);
    Route::post('/hubungi-kami', [HubungiKamiController::class, 'submit']);
    Route::get('/dasar/{id}/muat-turun', [DasarController::class, 'download']);
});

// Admin panel — Filament handles its own auth; no caching
// Filament routes are registered automatically under /admin
```

### Query Cache

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Broadcast extends Model
{
    // Scoped query result cache — used by HomeController and BroadcastController
    public static function getLatest(int $limit = 6): Collection
    {
        $locale = app()->getLocale(); // 'ms' or 'en'
        $cacheKey = "query:broadcasts:latest:{$limit}:{$locale}";

        return Cache::remember($cacheKey, 600, function () use ($limit) {
            return self::published()
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    // Local scope used by getLatest()
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }
}
```

### Cache Tags (For Selective Invalidation)

```php
use Illuminate\Support\Facades\Cache;

// Store with tags (use exact tag names from docs/pages-features.md)
Cache::tags(['broadcasts', 'homepage'])->put(
    'broadcast:my-slug:ms',
    $broadcast,
    3600
);

// Invalidate all broadcast-related caches (listing + homepage section)
Cache::tags(['broadcasts'])->flush();

// Invalidate homepage only (e.g., after QuickLink change)
Cache::tags(['homepage'])->flush();

// Full tag-to-model mapping: see docs/pages-features.md → "Cache Tag → Route / Model Mapping"
```

### Octane In-Memory Cache

```php
// config/octane.php
'tables' => [
    'cache' => [
        'rows' => 1000,
        'columns' => ['key', 'value', 'expiration'],
    ],
],
```

```php
// Ultra-fast in-memory cache (shared across workers)
use Laravel\Octane\Facades\Octane;

Octane::table('cache')->set('config:app_name', json_encode([
    'value' => 'GovPortal',
    'expiration' => time() + 3600,
]));

// Retrieve
$config = Octane::table('cache')->get('config:app_name');
```

## Layer 3: Database Cache

### PostgreSQL Configuration

```sql
-- Enable query cache (shared_buffers)
ALTER SYSTEM SET shared_buffers = '4GB';
ALTER SYSTEM SET effective_cache_size = '12GB';
ALTER SYSTEM SET work_mem = '256MB';

-- Materialized view for statistics page (refreshed hourly via scheduler)
-- Counts published content per type for the Statistik page
CREATE MATERIALIZED VIEW mv_content_stats AS
SELECT
    'broadcasts'        AS content_type,
    COUNT(*)            AS total,
    COUNT(*) FILTER (WHERE published_at >= NOW() - INTERVAL '30 days') AS last_30_days
FROM broadcasts WHERE status = 'published'
UNION ALL
SELECT
    'achievements'      AS content_type,
    COUNT(*)            AS total,
    COUNT(*) FILTER (WHERE date >= NOW() - INTERVAL '30 days') AS last_30_days
FROM achievements WHERE status = 'published'
UNION ALL
SELECT
    'policies'          AS content_type,
    COUNT(*)            AS total,
    COUNT(*) FILTER (WHERE published_at >= NOW() - INTERVAL '30 days') AS last_30_days
FROM policies WHERE status = 'published';

CREATE UNIQUE INDEX idx_mv_content_stats_type ON mv_content_stats(content_type);

-- Refresh hourly (add to Laravel scheduler)
REFRESH MATERIALIZED VIEW CONCURRENTLY mv_content_stats;
```

### Query Result Cache

```php
// config/database.php
'connections' => [
    'pgsql' => [
        // ...
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ],
],
```

## Cache Invalidation Strategies

### 1. Time-Based (TTL)

```php
// Automatic expiration
Cache::put('key', $value, 3600); // 1 hour
```

### 2. Event-Based

```php
// app/Observers/BroadcastObserver.php
// Registered in AppServiceProvider: Broadcast::observe(BroadcastObserver::class)
class BroadcastObserver
{
    public function saved(Broadcast $broadcast): void
    {
        // Flush Redis tags (see full mapping in docs/pages-features.md)
        Cache::tags(['broadcasts', 'homepage'])->flush();

        // Flush specific detail page cache if slug changed
        if ($broadcast->wasChanged('slug') && $broadcast->getOriginal('slug')) {
            Cache::tags(["broadcast:{$broadcast->getOriginal('slug')}"])->flush();
        }
        Cache::tags(["broadcast:{$broadcast->slug}"])->flush();
    }

    public function deleted(Broadcast $broadcast): void
    {
        Cache::tags(['broadcasts', 'homepage', "broadcast:{$broadcast->slug}"])->flush();
    }
}
```

### 3. Version-Based (Cache Busting)

```php
// config/cache.php
'prefix' => env('CACHE_PREFIX', 'govportal'),

// When deploying major changes
php artisan cache:clear
php artisan config:cache
php artisan view:cache
```

## Cache Warming

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Announcement;
use App\Models\Service;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm up the cache with frequently accessed data';
    
    public function handle(): void
    {
        $this->info('Warming cache...');

        foreach (['ms', 'en'] as $locale) {
            app()->setLocale($locale);

            // Warm homepage data
            Broadcast::getLatest(6);
            Achievement::getLatest(7);
            $this->info("Warmed homepage data for {$locale}");

            // Warm broadcasts listing
            Broadcast::published()->orderBy('published_at', 'desc')->paginate(15);
            $this->info("Warmed siaran listing for {$locale}");

            // Warm achievements listing
            Achievement::published()->orderBy('date', 'desc')->get();
            $this->info("Warmed pencapaian listing for {$locale}");
        }

        // Warm navigation (locale-independent structure)
        NavigationItem::whereNull('parent_id')->with('children')->ordered()->get();

        $this->info('Cache warming complete!');
    }
}
```

## Performance Monitoring

### Cache Hit Rate

```php
// In AppServiceProvider
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;

public function boot()
{
    Event::listen(CacheHit::class, function ($event) {
        Statsd::increment('cache.hit');
    });
    
    Event::listen(CacheMissed::class, function ($event) {
        Statsd::increment('cache.miss');
    });
}
```

### Target Metrics

| Metric | Target | Alert Threshold |
|--------|--------|-----------------|
| **CDN Hit Rate** | > 90% | < 80% |
| **Redis Hit Rate** | > 85% | < 75% |
| **Average Response Time** | < 100ms | > 500ms |
| **Cache Size** | < 80% of memory | > 90% |

## Best Practices

### DO ✅

1. **Cache at multiple layers** — CDN, Redis, Database
2. **Use cache tags** — For selective invalidation
3. **Set appropriate TTLs** — Balance freshness vs performance
4. **Warm cache on deploy** — Prevent cold start
5. **Monitor hit rates** — Optimize based on data
6. **Graceful degradation** — Serve stale if cache fails

### DON'T ❌

1. **Don't cache user-specific data** — Privacy issues
2. **Don't cache POST requests** — Side effects
3. **Don't use infinite TTL** — Memory leaks
4. **Don't forget to invalidate** — Stale data
5. **Don't cache in development** — Hard to debug

## Troubleshooting

### Cache Not Working

```bash
# Check Redis connection
redis-cli ping

# Check cache driver
php artisan tinker
>>> Cache::store('redis')->put('test', 'value', 60);
>>> Cache::store('redis')->get('test');

# Check Octane cache
php artisan octane:status
```

### High Cache Miss Rate

```bash
# Check cache size
redis-cli info stats | grep keyspace

# Eviction policy
redis-cli config get maxmemory-policy
# Should be: allkeys-lru

# Memory usage
redis-cli info memory | grep used_memory_human
```

## Next Steps

- [Documentation Guide](README.md)
- [Architecture](architecture.md)
- [Agentic Coding Playbook](agentic-coding.md)
