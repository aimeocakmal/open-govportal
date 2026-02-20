# API Reference

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URL and Versioning](#base-url-and-versioning)
4. [Request Format](#request-format)
5. [Response Format](#response-format)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)
8. [Endpoints](#endpoints)
   - [Broadcasts](#broadcasts)
   - [Achievements](#achievements)
   - [Directory](#directory)
   - [Feedback](#feedback)
9. [API Routes Registration](#api-routes-registration)
10. [API Controller Example](#api-controller-example)
11. [Postman Collection](#postman-collection)
12. [References](#references)

---

## Overview

OpenGovPortal exposes a REST API under `/api/v1/` for third-party integrations such as mobile apps, kiosks, and other government portals. The API serves read-only public content (broadcasts, achievements, directory) and accepts contact form submissions.

**Design principles:**
- Read-only for public content (GET); write-only for feedback (POST)
- All content endpoints return bilingual fields (`_ms`, `_en`)
- Responses use a consistent JSON envelope with `data` and `meta` keys
- Pagination follows Laravel's default LengthAwarePaginator structure
- Rate-limited per IP: 60 requests/minute for read endpoints, 5/hour for feedback

The API does **not** expose authentication, user management, or any Filament admin operations. The Filament CMS is not API-driven.

---

## Authentication

Most public endpoints do not require authentication. Future authenticated endpoints (e.g., draft preview) will use Laravel Sanctum with Bearer tokens.

```bash
# Authenticated request (future use)
curl -H "Authorization: Bearer {your-token}" \
     https://digital.gov.my/api/v1/broadcasts
```

To obtain a token (future use):

```http
POST /api/v1/auth/token
Content-Type: application/json

{
  "email": "editor@digital.gov.my",
  "password": "••••••••"
}
```

```json
{
  "data": {
    "token": "1|abc123...",
    "expires_at": "2026-03-01T00:00:00Z"
  }
}
```

---

## Base URL and Versioning

```
Production:  https://digital.gov.my/api/v1/
Staging:     https://staging.digital.gov.my/api/v1/
Local:       http://localhost:8000/api/v1/
```

API versions are path-prefixed (`/v1/`). Breaking changes will be published under `/v2/` with a deprecation notice period of 6 months.

---

## Request Format

```http
GET /api/v1/broadcasts?locale=ms&page=1&per_page=15
Accept: application/json
```

**Common query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | `ms` \| `en` | `ms` | Content language for title/description fields |
| `page` | integer | `1` | Page number for paginated responses |
| `per_page` | integer | `15` | Items per page (max: 50) |

---

## Response Format

All responses use a consistent JSON envelope:

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

For single-resource responses, `meta` is omitted:

```json
{
  "data": {
    "id": 1,
    "title": "Kementerian Digital Melancarkan Inisiatif Baru"
  }
}
```

---

## Error Handling

All errors use a consistent JSON structure:

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "The requested resource was not found.",
    "status": 404
  }
}
```

**HTTP status codes:**

| Status | Code | Meaning |
|--------|------|---------|
| 200 | OK | Successful GET |
| 201 | CREATED | Successful POST (feedback) |
| 400 | BAD_REQUEST | Invalid parameters |
| 401 | UNAUTHORIZED | Missing or invalid token |
| 404 | NOT_FOUND | Resource does not exist |
| 422 | VALIDATION_ERROR | Request body failed validation |
| 429 | RATE_LIMITED | Too many requests |
| 500 | SERVER_ERROR | Internal error |

**Validation error response (422):**

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "status": 422,
    "details": {
      "email": ["The email field must be a valid email address."],
      "message": ["The message field must be at least 20 characters."]
    }
  }
}
```

---

## Rate Limiting

Rate limits are enforced per IP address using Laravel's throttle middleware backed by Redis.

| Endpoint group | Limit | Window |
|----------------|-------|--------|
| Read endpoints (`GET /api/v1/*`) | 60 requests | 1 minute |
| Feedback submission (`POST /api/v1/feedback`) | 5 requests | 1 hour |

When the limit is exceeded, the API returns `429 Too Many Requests` with retry headers:

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
Retry-After: 45
```

**Laravel rate limiter configuration:**

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('feedback', function (Request $request) {
    return Limit::perHour(5)->by($request->ip())->response(function () {
        return response()->json([
            'error' => [
                'code'    => 'RATE_LIMITED',
                'message' => 'Too many feedback submissions. Please try again later.',
                'status'  => 429,
            ],
        ], 429);
    });
});
```

---

## Endpoints

### Broadcasts

#### List Broadcasts

Returns a paginated list of published broadcasts ordered by `published_at` descending.

```http
GET /api/v1/broadcasts
```

**Query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | string | `ms` | Content locale |
| `type` | string | — | Filter by type: `announcement`, `press_release`, `news` |
| `page` | integer | `1` | Page number |
| `per_page` | integer | `15` | Items per page (max: 50) |

**Example request:**

```bash
curl "https://digital.gov.my/api/v1/broadcasts?locale=ms&type=announcement&page=1"
```

**Example response (200):**

```json
{
  "data": [
    {
      "id": 42,
      "title": "Kementerian Digital Melancarkan Portal Baru",
      "slug": "kementerian-digital-melancarkan-portal-baru",
      "excerpt": "Kementerian Digital Malaysia dengan bangganya memperkenalkan portal rasmi yang dinaik taraf...",
      "featured_image": "https://bucket.s3.ap-southeast-1.amazonaws.com/broadcasts/image-001.jpg",
      "type": "announcement",
      "published_at": "2026-02-15T08:00:00Z",
      "url": "https://digital.gov.my/ms/siaran/kementerian-digital-melancarkan-portal-baru"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 87,
    "last_page": 6
  }
}
```

---

#### Get Broadcast by Slug

```http
GET /api/v1/broadcasts/{slug}
```

**Path parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | Unique broadcast slug |

**Query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | string | `ms` | Content locale |

**Example request:**

```bash
curl "https://digital.gov.my/api/v1/broadcasts/kementerian-digital-melancarkan-portal-baru?locale=ms"
```

**Example response (200):**

```json
{
  "data": {
    "id": 42,
    "title": "Kementerian Digital Melancarkan Portal Baru",
    "slug": "kementerian-digital-melancarkan-portal-baru",
    "content": "<p>Kementerian Digital Malaysia dengan bangganya memperkenalkan...</p>",
    "excerpt": "Kementerian Digital Malaysia dengan bangganya memperkenalkan portal rasmi yang dinaik taraf...",
    "featured_image": "https://bucket.s3.ap-southeast-1.amazonaws.com/broadcasts/image-001.jpg",
    "type": "announcement",
    "published_at": "2026-02-15T08:00:00Z",
    "url": "https://digital.gov.my/ms/siaran/kementerian-digital-melancarkan-portal-baru"
  }
}
```

**Error response (404):**

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Broadcast not found.",
    "status": 404
  }
}
```

---

### Achievements

#### List Achievements

Returns published achievements ordered by `date` descending.

```http
GET /api/v1/achievements
```

**Query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | string | `ms` | Content locale |
| `year` | integer | — | Filter by year (e.g., `2025`) |
| `page` | integer | `1` | Page number |
| `per_page` | integer | `15` | Items per page (max: 50) |

**Example request:**

```bash
curl "https://digital.gov.my/api/v1/achievements?locale=en&year=2025"
```

**Example response (200):**

```json
{
  "data": [
    {
      "id": 17,
      "title": "Digital Economy Contribution Reaches RM 280 Billion",
      "slug": "digital-economy-contribution-reaches-rm-280-billion",
      "description": "Malaysia's digital economy contribution to GDP reached RM 280 billion in 2025...",
      "date": "2025-12-31",
      "icon": null,
      "is_featured": true,
      "url": "https://digital.gov.my/en/pencapaian/digital-economy-contribution-reaches-rm-280-billion"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 24,
    "last_page": 2
  }
}
```

---

### Directory

#### Search Staff Directory

Returns active staff matching the search query, filtered by department if provided.

```http
GET /api/v1/directory
```

**Query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `locale` | string | `ms` | Content locale (for position/department labels) |
| `q` | string | — | Search term (name, position, or department) |
| `jabatan` | string | — | Filter by department name |
| `page` | integer | `1` | Page number |
| `per_page` | integer | `20` | Items per page (max: 50) |

**Note:** Staff email and phone are **excluded** from API responses for privacy. Only publicly displayable fields are returned.

**Example request:**

```bash
curl "https://digital.gov.my/api/v1/directory?locale=ms&q=Ahmad&jabatan=Bahagian+IT"
```

**Example response (200):**

```json
{
  "data": [
    {
      "id": 55,
      "name": "Ahmad Razif bin Othman",
      "position": "Ketua Bahagian",
      "department": "Bahagian Teknologi Maklumat",
      "division": null,
      "photo": "https://bucket.s3.ap-southeast-1.amazonaws.com/staff/ahmad-razif.jpg"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

**Privacy note:** Fields `email`, `phone`, and `fax` are intentionally omitted from this endpoint. These are displayed only on the rendered HTML page (`/ms/direktori`), not via the API, to prevent harvesting.

---

### Feedback

#### Submit Feedback

Stores a feedback submission and dispatches an email notification to the ministry.

```http
POST /api/v1/feedback
Content-Type: application/json
```

**Request body:**

```json
{
  "name": "Siti Aminah binti Rahman",
  "email": "siti@example.com",
  "subject": "Pertanyaan Mengenai Lesen Digital",
  "message": "Saya ingin bertanya lebih lanjut mengenai prosedur permohonan lesen digital untuk syarikat saya.",
  "page_url": "https://digital.gov.my/ms/hubungi-kami"
}
```

**Field validation:**

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | Optional, max 255 chars |
| `email` | string | Required, valid email, max 255 |
| `subject` | string | Required, max 500 chars |
| `message` | string | Required, min 20 chars, max 5000 |
| `page_url` | string | Optional, valid URL |

**Example request:**

```bash
curl -X POST "https://digital.gov.my/api/v1/feedback" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Siti Aminah",
    "email": "siti@example.com",
    "subject": "Pertanyaan Lesen Digital",
    "message": "Saya ingin bertanya lebih lanjut mengenai prosedur permohonan lesen digital."
  }'
```

**Example response (201):**

```json
{
  "data": {
    "id": 1201,
    "submitted_at": "2026-02-20T10:30:00Z",
    "message": "Terima kasih atas maklum balas anda. Kami akan menghubungi anda dalam masa 3 hari bekerja."
  }
}
```

**Validation error (422):**

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "status": 422,
    "details": {
      "email": ["The email field is required."],
      "message": ["The message field must be at least 20 characters."]
    }
  }
}
```

---

## API Routes Registration

```php
// routes/api.php
use App\Http\Controllers\Api\BroadcastController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\FeedbackController;

Route::prefix('v1')->group(function () {
    // Public read endpoints — 60 req/min
    Route::middleware('throttle:api')->group(function () {
        Route::get('/broadcasts', [BroadcastController::class, 'index']);
        Route::get('/broadcasts/{slug}', [BroadcastController::class, 'show']);
        Route::get('/achievements', [AchievementController::class, 'index']);
        Route::get('/directory', [DirectoryController::class, 'index']);
    });

    // Feedback submission — 5/hour
    Route::middleware('throttle:feedback')->group(function () {
        Route::post('/feedback', [FeedbackController::class, 'store']);
    });
});
```

---

## API Controller Example

```php
<?php
// app/Http/Controllers/Api/BroadcastController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'ms');
        abort_unless(in_array($locale, ['ms', 'en']), 400, 'Invalid locale.');

        $perPage = min((int) $request->query('per_page', 15), 50);

        $query = Broadcast::published()
            ->orderBy('published_at', 'desc');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->map(fn (Broadcast $b) => [
            'id'             => $b->id,
            'title'          => $b->{"title_{$locale}"},
            'slug'           => $b->slug,
            'excerpt'        => $b->{"excerpt_{$locale}"},
            'featured_image' => $b->featured_image,
            'type'           => $b->type,
            'published_at'   => $b->published_at?->toIso8601String(),
            'url'            => url("/{$locale}/siaran/{$b->slug}"),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $request->query('locale', 'ms');
        abort_unless(in_array($locale, ['ms', 'en']), 400, 'Invalid locale.');

        $broadcast = Broadcast::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'             => $broadcast->id,
                'title'          => $broadcast->{"title_{$locale}"},
                'slug'           => $broadcast->slug,
                'content'        => $broadcast->{"content_{$locale}"},
                'excerpt'        => $broadcast->{"excerpt_{$locale}"},
                'featured_image' => $broadcast->featured_image,
                'type'           => $broadcast->type,
                'published_at'   => $broadcast->published_at?->toIso8601String(),
                'url'            => url("/{$locale}/siaran/{$broadcast->slug}"),
            ],
        ]);
    }
}
```

---

## Postman Collection

Save as `postman/govportal-api.json` and import into Postman:

```json
{
  "info": {
    "name": "OpenGovPortal API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    { "key": "base_url", "value": "http://localhost:8000/api/v1" },
    { "key": "locale", "value": "ms" }
  ],
  "item": [
    {
      "name": "Broadcasts",
      "item": [
        {
          "name": "List Broadcasts",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/broadcasts?locale={{locale}}&page=1"
          }
        },
        {
          "name": "Get Broadcast by Slug",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/broadcasts/{{slug}}?locale={{locale}}"
          }
        }
      ]
    },
    {
      "name": "Achievements",
      "item": [
        {
          "name": "List Achievements",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/achievements?locale={{locale}}"
          }
        }
      ]
    },
    {
      "name": "Directory",
      "item": [
        {
          "name": "Search Staff",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/directory?locale={{locale}}&q=Ahmad"
          }
        }
      ]
    },
    {
      "name": "Feedback",
      "item": [
        {
          "name": "Submit Feedback",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/feedback",
            "header": [
              { "key": "Content-Type", "value": "application/json" }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"subject\":\"Test Subject\",\"message\":\"This is a test message with more than twenty characters.\"}"
            }
          }
        }
      ]
    }
  ]
}
```

---

## References

- [Architecture](architecture.md)
- [Database Schema](database-schema.md)
- [Pages & Features](pages-features.md) — route naming conventions
- [Security Guide](security.md) — API security headers and rate limiting
- [Testing Guide](testing.md) — API feature test examples
- [Laravel HTTP Tests](https://laravel.com/docs/11.x/http-tests)
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
