# Security Guide

## Table of Contents

1. [Overview](#overview)
2. [OWASP Top 10 Mapping](#owasp-top-10-mapping)
3. [Authentication and RBAC](#authentication-and-rbac)
4. [Password Policy](#password-policy)
5. [CSRF Protection](#csrf-protection)
6. [XSS Prevention](#xss-prevention)
7. [SQL Injection Prevention](#sql-injection-prevention)
8. [File Upload Security](#file-upload-security)
9. [Security Headers](#security-headers)
10. [Rate Limiting](#rate-limiting)
11. [Audit Logging](#audit-logging)
12. [Penetration Testing Checklist](#penetration-testing-checklist)
13. [Incident Response](#incident-response)
14. [PDPA Compliance](#pdpa-compliance)
15. [References](#references)

---

## Overview

OpenGovPortal applies defence-in-depth security across five layers:

```
Layer 1: CDN (Cloudflare WAF — DDoS, bot protection, rate limiting at edge)
Layer 2: Load Balancer (IP allowlisting for admin, rate limiting)
Layer 3: Application (CSRF, XSS, SQL injection, input validation)
Layer 4: Authentication (RBAC via Spatie Permission, MFA for admin)
Layer 5: Database (parameterised queries, no raw SQL, encrypted at rest)
```

This document covers Layers 3–5. CDN and Load Balancer configuration is in [docs/architecture.md](architecture.md).

**Government compliance baseline:** Kementerian Digital Malaysia follows MAMPU ICT Security Policy and references NIST SP 800-53 and ISO 27001 controls.

---

## OWASP Top 10 Mapping

| OWASP Risk | Mitigation in this project |
|-----------|---------------------------|
| A01 Broken Access Control | Spatie Permission RBAC; Filament resource policies; no direct object references |
| A02 Cryptographic Failures | HTTPS enforced; AES-256 at rest (RDS); bcrypt passwords (12 rounds) |
| A03 Injection | Eloquent ORM parameterised queries; never raw SQL with user input |
| A04 Insecure Design | Principle of least privilege in roles; no public user registration |
| A05 Security Misconfiguration | `APP_DEBUG=false`; security headers middleware; Cloudflare WAF |
| A06 Vulnerable Components | Dependabot alerts; `composer audit` in CI pipeline |
| A07 Auth Failures | Throttled login; bcrypt 12 rounds; session rotation on login |
| A08 Software and Data Integrity | Docker image signed with SHA digest; migration rollback tested |
| A09 Logging and Monitoring | Sentry errors; slow query log; audit log for admin actions |
| A10 SSRF | No user-controlled URLs in server-side requests; S3 URLs validated by SDK |

---

## Authentication and RBAC

### Filament Admin Authentication

The CMS admin panel (`/admin`) uses Laravel's default web guard with Filament. No public user registration exists — all accounts are provisioned by a `super_admin`.

```php
// app/Filament/Providers/FilamentServiceProvider.php
Filament::auth()->guard('web');
```

**Login throttling** (built into Filament):

```php
// config/auth.php
'throttle' => [
    'max_attempts' => 5,
    'decay_minutes' => 15,  // Lock out for 15 minutes after 5 failed attempts
],
```

### Roles and Permissions

Defined in [docs/database-schema.md](database-schema.md). Seeded by `RoleSeeder` and `PermissionSeeder`.

| Role | Capabilities |
|------|-------------|
| `super_admin` | All permissions; user management; system settings |
| `content_editor` | Create and edit content; cannot publish or delete |
| `publisher` | Approve and publish content; cannot delete |
| `viewer` | Read-only access to all CMS resources |

### Enforcing Policies in Filament Resources

```php
// app/Filament/Resources/BroadcastResource.php
public static function canCreate(): bool
{
    return auth()->user()->can('create_broadcasts');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->can('edit_broadcasts');
}

public static function canDelete(Model $record): bool
{
    return auth()->user()->can('delete_broadcasts');
}

public static function canPublish(Model $record): bool
{
    return auth()->user()->can('publish_broadcasts');
}
```

### Session Security

```php
// config/session.php
return [
    'driver'          => 'redis',
    'lifetime'        => 120,       // 2-hour timeout
    'expire_on_close' => false,
    'encrypt'         => true,      // Encrypt session data at rest
    'secure'          => true,      // HTTPS-only cookie
    'http_only'       => true,      // No JavaScript access
    'same_site'       => 'lax',
];
```

Rotate session ID on login to prevent session fixation:

```php
// In custom LoginController or Filament auth hook
$request->session()->regenerate();
```

---

## Password Policy

Minimum requirements for CMS admin accounts:

| Rule | Requirement |
|------|-------------|
| Minimum length | 12 characters |
| Complexity | Must contain uppercase, lowercase, digit, and symbol |
| Reuse | Cannot reuse last 5 passwords |
| Expiry | 90 days (enforced via Filament profile prompt) |
| Hashing | bcrypt, 12 rounds |

```php
// app/Rules/StrongPassword.php
use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    public function passes($attribute, $value): bool
    {
        return strlen($value) >= 12
            && preg_match('/[A-Z]/', $value)
            && preg_match('/[a-z]/', $value)
            && preg_match('/[0-9]/', $value)
            && preg_match('/[\W_]/', $value);
    }

    public function message(): string
    {
        return 'Kata laluan mesti mengandungi sekurang-kurangnya 12 aksara, '
             . 'huruf besar, huruf kecil, nombor, dan simbol.';
    }
}
```

Apply in Filament user management:

```php
// In UserResource form
TextInput::make('password')
    ->password()
    ->rules(['min:12', new StrongPassword])
    ->dehydrated(fn ($state) => filled($state))
    ->required(fn (string $context): bool => $context === 'create'),
```

---

## CSRF Protection

Laravel's CSRF middleware is enabled globally for all web routes. Livewire includes the CSRF token automatically in every wire request.

```php
// bootstrap/app.php — VerifyCsrfToken is in the web middleware group by default
$middleware->web(append: [
    \App\Http\Middleware\SecurityHeaders::class,
]);
```

**For Livewire components:** CSRF is automatically handled. Do not disable it.

**For API routes:** API routes are stateless and do not use CSRF tokens. They use Sanctum Bearer tokens for future authenticated endpoints.

**Verify CSRF is active:**

```bash
curl -X POST http://localhost:8000/ms/hubungi-kami \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "name=Test"
# Expected: 419 Page Expired (missing CSRF token)
```

---

## XSS Prevention

### Blade Auto-Escaping

Blade's `{{ }}` syntax HTML-escapes all output by default. **Always use `{{ }}` for user-supplied or database content.**

```blade
{{-- SAFE: HTML-escaped automatically --}}
{{ $broadcast->title_ms }}
{{ $broadcast->excerpt_ms }}

{{-- UNSAFE: only use for trusted, pre-sanitised HTML from the CMS --}}
{!! $broadcast->content_ms !!}
```

**When using `{!! !!}`:** Rich text content stored in the CMS must be sanitised at save time. Filament's `RichEditor` sanitises output before persisting. Do not render raw user input with `{!! !!}` under any circumstances.

### Alpine.js XSS Boundary

Never render user-supplied data into Alpine.js `x-data` or `x-init` expressions:

```blade
{{-- UNSAFE: user content interpolated into Alpine expression --}}
<div x-data="{ title: '{{ $userInput }}' }">

{{-- SAFE: pass data as JSON via a data attribute --}}
<div x-data="chartData" data-config="{{ json_encode($chartConfig) }}">
```

---

## SQL Injection Prevention

### Always Use Eloquent ORM

OpenGovPortal uses Eloquent exclusively for application queries. Raw SQL is only permitted in migrations and read-only reporting queries with named bindings.

```php
// SAFE: parameterised query via Eloquent
$broadcasts = Broadcast::published()
    ->where('type', $request->input('type'))
    ->orderBy('published_at', 'desc')
    ->paginate(15);

// SAFE: named bindings in raw expressions (migrations only)
DB::select('SELECT * FROM broadcasts WHERE type = :type', ['type' => $type]);

// UNSAFE: never concatenate user input into SQL
DB::statement("SELECT * FROM broadcasts WHERE type = '{$type}'");
```

### Full-Text Search (Safe)

FTS queries in `searchable_content` use parameterised bindings via `plainto_tsquery`, which sanitises the input before it reaches the database:

```php
// SAFE: plainto_tsquery sanitises user input
$results = DB::table('searchable_content')
    ->whereRaw(
        "tsvector_ms @@ plainto_tsquery('simple', ?)",
        [$request->input('q')]
    )
    ->orderByRaw(
        "ts_rank(tsvector_ms, plainto_tsquery('simple', ?)) DESC",
        [$request->input('q')]
    )
    ->get();
```

---

## File Upload Security

File uploads are handled through Filament's file upload field for media and policy documents.

### Allowed MIME Types

```php
// app/Filament/Resources/PolicyResource.php
FileUpload::make('file_url')
    ->disk('s3')
    ->directory('policies')
    ->acceptedFileTypes(['application/pdf'])  // PDFs only for policy documents
    ->maxSize(20480)                          // 20MB limit
    ->visibility('private'),                 // Signed URLs required for download

// app/Filament/Resources/MediaResource.php
FileUpload::make('file_url')
    ->disk('s3')
    ->directory('media')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
    ->maxSize(10240)                          // 10MB limit
    ->image()
    ->imageResizeMode('contain')
    ->visibility('public'),
```

### S3 Bucket Policy

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadMedia",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::govportal-media-prod/media/*"
    },
    {
      "Sid": "DenyDirectPolicyAccess",
      "Effect": "Deny",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::govportal-media-prod/policies/*",
      "Condition": {
        "StringNotEquals": {
          "aws:PrincipalServiceName": "s3.amazonaws.com"
        }
      }
    }
  ]
}
```

Policy documents use **signed S3 URLs** with 10-minute expiry to prevent hotlinking:

```php
// app/Http/Controllers/DasarController.php
public function download(int $id): RedirectResponse
{
    $policy = Policy::published()->findOrFail($id);

    $policy->increment('download_count');

    $url = Storage::disk('s3')->temporaryUrl(
        $policy->file_url,
        now()->addMinutes(10)
    );

    return redirect($url);
}
```

---

## Security Headers

Apply security headers via middleware on all HTTP responses.

```php
<?php
// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: https://govportal-media-prod.s3.ap-southeast-1.amazonaws.com",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        return $response;
    }
}
```

Register in `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\SecurityHeaders::class,
]);
```

### Header Verification

```bash
curl -I https://digital.gov.my/ms
# Verify presence of: Strict-Transport-Security, X-Frame-Options, Content-Security-Policy
```

Use [securityheaders.com](https://securityheaders.com) to verify headers score A or above before go-live.

---

## Rate Limiting

Rate limiting runs at two layers: Cloudflare WAF (edge) and Laravel throttle middleware (application).

### Cloudflare WAF Rules

```
Rule: Contact form protection
  URI path: /hubungi-kami (method: POST)
  Action: Rate limit — 5 requests per IP per hour

Rule: API protection
  URI path: /api/*
  Action: Rate limit — 100 requests per IP per minute
```

### Laravel Throttle Middleware

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting(): void
{
    // API read endpoints: 60 requests/minute
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)
            ->by($request->ip())
            ->response(fn () => response()->json([
                'error' => ['code' => 'RATE_LIMITED', 'status' => 429],
            ], 429));
    });

    // Feedback/contact form: 5 submissions per hour
    RateLimiter::for('feedback', function (Request $request) {
        return Limit::perHour(5)->by($request->ip());
    });

    // Admin login: 5 attempts per 15 minutes
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinutes(15, 5)->by($request->ip());
    });
}
```

---

## Audit Logging

All admin CMS actions must be logged for accountability and compliance. Use `spatie/laravel-activitylog`.

```php
// app/Models/Broadcast.php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Broadcast extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title_ms', 'title_en', 'status', 'published_at', 'type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Broadcast {$eventName}");
    }
}
```

**Logged events:**

| Event | Actor | Data logged |
|-------|-------|-------------|
| Broadcast created | CMS user | All fields |
| Broadcast published | CMS user | `status`, `published_at` |
| Broadcast deleted | CMS user | `id`, `slug`, `title_ms` |
| User login | System | IP, user_agent, timestamp |
| User login failed | System | IP, email attempted |
| Settings changed | CMS user | Key changed, old/new value |
| Role assigned | `super_admin` | User, role |

**Log retention:** 12 months (MAMPU ICT Security Policy requirement). Archive to S3 after 90 days.

```php
// config/activitylog.php
return [
    'delete_records_older_than_days' => 365,
];
```

---

## Penetration Testing Checklist

Run before each major release. All items must be signed off before production deployment.

### Authentication

- [ ] Brute force login triggers lockout after 5 attempts
- [ ] Session fixation: session ID rotates on login
- [ ] Session is invalidated on logout
- [ ] Password reset tokens expire after 60 minutes
- [ ] Admin panel is inaccessible without credentials

### Authorisation

- [ ] `content_editor` cannot publish (returns 403)
- [ ] `publisher` cannot delete (returns 403)
- [ ] `viewer` cannot create/edit (returns 403)
- [ ] Direct URL access to Filament resources enforces policy
- [ ] Modifying `id` in URL cannot access other users' records

### Injection

- [ ] SQL injection in search: `' OR '1'='1` returns no results or 400
- [ ] XSS in search: `<script>alert(1)</script>` is escaped in output
- [ ] HTML injection in contact form message is sanitised before display

### File Upload

- [ ] Uploading `.php` file via Filament is rejected
- [ ] Uploading `.exe` file is rejected
- [ ] File size > 20MB is rejected
- [ ] Policy document URL is a signed S3 URL that expires after 10 minutes

### CSRF

- [ ] POST to `/ms/hubungi-kami` without CSRF token returns 419
- [ ] Livewire requests without valid token are rejected

### Security Headers

- [ ] `X-Frame-Options: DENY` is present
- [ ] `Content-Security-Policy` is present and blocks inline scripts
- [ ] `Strict-Transport-Security` with preload directive is present
- [ ] No `Server: Apache` or `X-Powered-By: PHP` header leaks version info

### Information Disclosure

- [ ] `APP_DEBUG=false` — no stack traces in 500 error responses
- [ ] `.env` file returns 403 or 404 when accessed via HTTP
- [ ] `/admin` is not accessible to unauthenticated users (redirects to login)
- [ ] Error pages do not reveal file paths or database details

---

## Incident Response

### Severity Classification

| Level | Definition | Target response time |
|-------|-----------|---------------------|
| P1 Critical | Active breach, data exfiltration, system down | 30 minutes |
| P2 High | Suspected breach, auth bypass, data exposure | 2 hours |
| P3 Medium | Vulnerability found, no active exploit | 24 hours |
| P4 Low | Minor misconfiguration, information disclosure | 1 week |

### P1 Response Procedure

1. **Isolate** — Remove affected pod/instance from load balancer immediately:
   ```bash
   kubectl scale deployment/govportal-app --replicas=0 --namespace=govportal
   ```

2. **Preserve** — Snapshot database and application logs before any remediation:
   ```bash
   aws rds create-db-snapshot \
     --db-instance-identifier govportal-prod \
     --db-snapshot-identifier incident-$(date +%Y%m%d)
   ```

3. **Assess** — Identify scope: which data, which users, which systems were affected

4. **Notify** — Report to:
   - MAMPU GCERT (government CERT): gcert@mampu.gov.my
   - Ministry CISO within 1 hour of discovery
   - PDPC (Personal Data Protection Commissioner) within 72 hours if PII is affected

5. **Contain** — Block attacker IP at Cloudflare WAF; rotate compromised credentials

6. **Recover** — Restore from last verified clean backup; integrity-check before bringing back online

7. **Document** — Write full incident report within 48 hours

### Credential Compromise Response

If any production credential (DB password, AWS key, APP_KEY) is compromised:

```bash
# 1. Rotate APP_KEY (invalidates all existing sessions)
php artisan key:generate
# Update Kubernetes Secret with new APP_KEY and redeploy

# 2. Invalidate all sessions in Redis
php artisan cache:clear

# 3. Force re-login for all admin users
php artisan tinker --execute="DB::table('personal_access_tokens')->delete();"
```

---

## PDPA Compliance

Malaysia's Personal Data Protection Act 2010 (PDPA) applies to all collection of personal data from residents.

### Data Collected

| Data | Purpose | Retention |
|------|---------|-----------|
| Feedback name, email | Ministry contact response | 2 years |
| Feedback IP address | Rate limiting, spam prevention | 90 days |
| Staff directory (name, position, email, phone) | Public ministry directory | Active employment |
| Admin user accounts | CMS access control | Employment duration |

### Implementation Requirements

Anonymise feedback IP addresses after 90 days:

```php
<?php
// app/Console/Commands/AnonymiseFeedbackIp.php

namespace App\Console\Commands;

use App\Models\Feedback;
use Illuminate\Console\Command;

class AnonymiseFeedbackIp extends Command
{
    protected $signature = 'feedback:anonymise-ip';

    public function handle(): void
    {
        $count = Feedback::where('created_at', '<', now()->subDays(90))
            ->whereNotNull('ip_address')
            ->update(['ip_address' => null]);

        $this->info("Anonymised IP addresses for {$count} feedback records.");
    }
}
```

Schedule daily in `app/Console/Kernel.php`:

```php
$schedule->command('feedback:anonymise-ip')->daily();
```

### API Privacy Rules

- Staff `email`, `phone`, and `fax` are **excluded** from API responses (see [docs/api.md](api.md))
- Feedback submissions are stored in the CMS only; not exposed via API
- Google Analytics must use anonymised IP mode (or equivalent)
- Sentry error reports: `send_default_pii = false` (see [docs/deployment.md](deployment.md))

### Data Subject Rights

Under PDPA, individuals may request:
- Access to their personal data
- Correction of inaccurate data
- Deletion of data that is no longer necessary

Requests are handled manually by the ministry's data protection officer. No self-service portal is required at this stage.

---

## References

- [Architecture](architecture.md) — CDN and load balancer security layers
- [API Reference](api.md) — Rate limiting for API endpoints
- [Deployment Guide](deployment.md) — Production environment hardening
- [Database Schema](database-schema.md) — RBAC tables and audit logging schema
- [MAMPU ICT Security Policy](https://www.mampu.gov.my) — Government baseline
- [PDPA 2010](https://www.pdp.gov.my) — Malaysian data protection law
- [OWASP Top 10](https://owasp.org/www-project-top-ten/) — Web security risks
- [Laravel Security](https://laravel.com/docs/11.x/security)
