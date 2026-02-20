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
  
# Rule 3: Public pages - Short cache
/announcements/*, /services/*, /departments/*
  Cache Level: Cache Everything
  Edge Cache TTL: 1 hour
  Browser Cache TTL: 1 hour
  Bypass Cache on Cookie: gov_session
  
# Rule 4: API - Short cache
/api/v1/*
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
  --data '{"files":["https://govportal.gov.my/announcements/latest"]}'
```

## Layer 2: Redis (Application Cache)

### Cache Structure

```php
// Full-page cache (HTML)
Cache::store('redis')->put(
    'page:/announcements:ms',
    '<html>...</html>',
    now()->addHour()
);

// Query result cache
Cache::store('redis')->put(
    'query:announcements:latest:10:ms',
    $announcements,
    now()->addMinutes(10)
);

// Fragment cache (partial view)
Cache::store('redis')->put(
    'fragment:header:ms',
    $headerHtml,
    now()->addHours(2)
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

// Public pages - cached for 1 hour
Route::middleware('cache.response:3600')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{slug}', [AnnouncementController::class, 'show']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/departments', [DepartmentController::class, 'index']);
});

// Admin panel - no cache
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    // ...
});
```

### Query Cache

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Announcement extends Model
{
    public static function getLatest($limit = 10, $locale = 'ms')
    {
        $cacheKey = "query:announcements:latest:{$limit}:{$locale}";
        
        return Cache::remember($cacheKey, 600, function () use ($limit, $locale) {
            return self::where('locale', $locale)
                ->where('published', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }
    
    public static function getByDepartment($departmentId, $locale = 'ms')
    {
        $cacheKey = "query:announcements:dept:{$departmentId}:{$locale}";
        
        return Cache::remember($cacheKey, 600, function () use ($departmentId, $locale) {
            return self::where('department_id', $departmentId)
                ->where('locale', $locale)
                ->where('published', true)
                ->orderBy('published_at', 'desc')
                ->paginate(20);
        });
    }
}
```

### Cache Tags (For Selective Invalidation)

```php
use Illuminate\Support\Facades\Cache;

// Store with tags
Cache::tags(['announcements', 'department:5'])->put(
    'announcement:123:ms',
    $announcement,
    3600
);

// Invalidate all announcements
Cache::tags(['announcements'])->flush();

// Invalidate specific department
Cache::tags(['department:5'])->flush();
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

-- Create materialized view for heavy queries
CREATE MATERIALIZED VIEW mv_department_stats AS
SELECT 
    d.id,
    d.name,
    COUNT(a.id) as announcement_count,
    COUNT(s.id) as service_count
FROM departments d
LEFT JOIN announcements a ON a.department_id = d.id
LEFT JOIN services s ON s.department_id = d.id
GROUP BY d.id, d.name;

-- Create index on materialized view
CREATE INDEX idx_mv_dept_stats_id ON mv_department_stats(id);

-- Refresh materialized view (run periodically)
REFRESH MATERIALIZED VIEW CONCURRENTLY mv_department_stats;
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
// app/Observers/AnnouncementObserver.php
class AnnouncementObserver
{
    public function saved(Announcement $announcement)
    {
        // Clear related caches
        Cache::tags(['announcements'])->flush();
        Cache::tags(["department:{$announcement->department_id}"])->flush();
        
        // Clear CDN cache
        Cloudflare::purgeUrls([
            url("/announcements/{$announcement->slug}"),
            url('/announcements'),
        ]);
    }
    
    public function deleted(Announcement $announcement)
    {
        $this->saved($announcement);
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
    
    public function handle()
    {
        $this->info('Warming cache...');
        
        // Warm homepage
        $this->call('route:cache');
        
        // Warm latest announcements
        foreach (['ms', 'en'] as $locale) {
            Announcement::getLatest(10, $locale);
            $this->info("Warmed announcements for {$locale}");
        }
        
        // Warm services
        Service::getFeatured();
        
        // Warm departments
        Department::getActive();
        
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
