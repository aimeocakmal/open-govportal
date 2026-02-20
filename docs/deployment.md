# Deployment Guide

## Table of Contents

1. [Overview](#overview)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Environment Configuration](#environment-configuration)
4. [Docker Configuration](#docker-configuration)
5. [Kubernetes Deployment](#kubernetes-deployment)
6. [Database Migration Strategy](#database-migration-strategy)
7. [Zero-Downtime Deployment](#zero-downtime-deployment)
8. [Queue Workers](#queue-workers)
9. [CDN Configuration](#cdn-configuration)
10. [Backup Strategy](#backup-strategy)
11. [Monitoring and Alerting](#monitoring-and-alerting)
12. [Rollback Procedures](#rollback-procedures)
13. [Performance Benchmarking](#performance-benchmarking)
14. [References](#references)

---

## Overview

OpenGovPortal runs on **Laravel Octane + FrankenPHP** in a containerised Kubernetes environment behind Cloudflare CDN. The production stack:

```
Cloudflare CDN
    |
AWS ALB (load balancer)
    |
Kubernetes Pods (FrankenPHP workers)
    |
Redis Cluster + PostgreSQL Primary/Replica
    |
AWS S3 (media/files) + AWS SES (email)
```

FrankenPHP replaces both PHP-FPM and Nginx. Caddy is built in. See [docs/architecture.md](architecture.md) for the full system diagram.

---

## Pre-Deployment Checklist

Complete **all** items before deploying to production.

### Application

- [ ] `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- [ ] `APP_KEY` is a 32-character base64 string (never reuse staging key)
- [ ] All migrations run without error on a copy of production data
- [ ] `php artisan config:cache` runs without error
- [ ] `php artisan route:cache` runs without error
- [ ] `php artisan view:cache` runs without error
- [ ] `php artisan optimize` completes
- [ ] No hardcoded credentials in code (`grep -r "password\|secret\|key" --include="*.php" app/`)
- [ ] All tests pass: `php artisan test`

### Database

- [ ] Migration rollback tested: `php artisan migrate:rollback --step=1`
- [ ] All `down()` methods are non-destructive (create backup before running `down()` on production)
- [ ] PostgreSQL connection uses read replica for reads (read/write split in `config/database.php`)
- [ ] PgBouncer connection pooling configured
- [ ] Database backup verified and restorable

### Security

- [ ] HTTPS enforced (Cloudflare Always HTTPS rule active)
- [ ] Security headers middleware applied (see [docs/security.md](security.md))
- [ ] File upload validation in place
- [ ] Rate limiting active on contact form and API endpoints
- [ ] Filament `/admin` accessible only to authenticated users
- [ ] `SANCTUM_STATEFUL_DOMAINS` set correctly for the production domain

### Infrastructure

- [ ] Cloudflare zones configured with correct page rules
- [ ] AWS S3 bucket policy allows public read for media; private for policies
- [ ] AWS SES domain verified and out of sandbox mode
- [ ] Redis AUTH password configured
- [ ] SSL certificates valid (Caddy handles this automatically)
- [ ] Health check endpoint (`/up`) returns 200

---

## Environment Configuration

### Production `.env`

```ini
# Application
APP_NAME="OpenGovPortal"
APP_ENV=production
APP_KEY=base64:REPLACE_WITH_REAL_32_CHAR_KEY=
APP_DEBUG=false
APP_URL=https://digital.gov.my

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

# Database — write to primary, reads distributed to replicas
DB_CONNECTION=pgsql
DB_HOST=db-primary.internal
DB_PORT=5432
DB_DATABASE=govportal
DB_USERNAME=govportal_app
DB_PASSWORD=REPLACE_WITH_DB_PASSWORD

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis-cluster.internal
REDIS_PORT=6379
REDIS_PASSWORD=REPLACE_WITH_REDIS_PASSWORD
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Cache
CACHE_PREFIX=govportal_prod

# Octane
OCTANE_SERVER=frankenphp
OCTANE_WORKERS=8
OCTANE_MAX_REQUESTS=500

# Mail (AWS SES)
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@digital.gov.my
MAIL_FROM_NAME="Kementerian Digital Malaysia"
AWS_ACCESS_KEY_ID=REPLACE
AWS_SECRET_ACCESS_KEY=REPLACE
AWS_DEFAULT_REGION=ap-southeast-1
AWS_SES_REGION=ap-southeast-1

# Storage (AWS S3)
FILESYSTEM_DISK=s3
AWS_BUCKET=govportal-media-prod
AWS_URL=https://govportal-media-prod.s3.ap-southeast-1.amazonaws.com

# Filament
FILAMENT_AUTH_GUARD=web

# Sentry (error tracking)
SENTRY_LARAVEL_DSN=https://REPLACE@sentry.io/PROJECT_ID
SENTRY_TRACES_SAMPLE_RATE=0.1
```

**Never commit `.env` to version control.** Use AWS Secrets Manager, Kubernetes Secrets, or Vault to inject environment variables at runtime.

### Read Replica Configuration

```php
// config/database.php
'pgsql' => [
    'read' => [
        'host' => [
            env('DB_READ_HOST_1', env('DB_HOST', '127.0.0.1')),
            env('DB_READ_HOST_2', env('DB_HOST', '127.0.0.1')),
        ],
    ],
    'write' => [
        'host' => [env('DB_HOST', '127.0.0.1')],
    ],
    'sticky'   => true,
    'driver'   => 'pgsql',
    'port'     => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'govportal'),
    'username' => env('DB_USERNAME', 'govportal_app'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => 'utf8',
    'prefix'   => '',
    'schema'   => 'public',
],
```

---

## Docker Configuration

### Dockerfile

```dockerfile
# Dockerfile
FROM dunglas/frankenphp:latest-php8.3

# Install required PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    redis \
    pcntl \
    zip \
    gd \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# Copy application files
COPY . .

# Run post-install scripts (package:discover)
RUN composer run-script post-autoload-dump

# Cache configuration for production
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan event:cache

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 755 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", \
     "--host=0.0.0.0", \
     "--port=8000", \
     "--workers=${OCTANE_WORKERS:-8}", \
     "--max-requests=${OCTANE_MAX_REQUESTS:-500}"]
```

### docker-compose.yml (Local Development)

```yaml
# docker-compose.yml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
      DB_HOST: postgres
      REDIS_HOST: redis
    volumes:
      - .:/app
      - /app/vendor
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_USER: govportal
      POSTGRES_PASSWORD: secret
      POSTGRES_DB: govportal
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U govportal"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      APP_ENV: local
      DB_HOST: postgres
      REDIS_HOST: redis
    depends_on:
      - postgres
      - redis
    restart: unless-stopped

volumes:
  postgres_data:
  redis_data:
```

---

## Kubernetes Deployment

### Application Deployment

```yaml
# .kube/deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: govportal-app
  namespace: govportal
  labels:
    app: govportal
    component: app
spec:
  replicas: 3
  selector:
    matchLabels:
      app: govportal
      component: app
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxSurge: 1
      maxUnavailable: 0      # Zero-downtime rolling update
  template:
    metadata:
      labels:
        app: govportal
        component: app
    spec:
      containers:
        - name: govportal
          image: your-registry/govportal:latest
          ports:
            - containerPort: 8000
          envFrom:
            - secretRef:
                name: govportal-env
          resources:
            requests:
              cpu: "500m"
              memory: "512Mi"
            limits:
              cpu: "2000m"
              memory: "2Gi"
          livenessProbe:
            httpGet:
              path: /up
              port: 8000
            initialDelaySeconds: 10
            periodSeconds: 30
            failureThreshold: 3
          readinessProbe:
            httpGet:
              path: /up
              port: 8000
            initialDelaySeconds: 5
            periodSeconds: 10
            failureThreshold: 3
          lifecycle:
            preStop:
              exec:
                command: ["/bin/sh", "-c", "sleep 5"]
      terminationGracePeriodSeconds: 30
```

### Service

```yaml
# .kube/service.yaml
apiVersion: v1
kind: Service
metadata:
  name: govportal-app
  namespace: govportal
spec:
  selector:
    app: govportal
    component: app
  ports:
    - port: 80
      targetPort: 8000
  type: ClusterIP
```

### Queue Worker Deployment

```yaml
# .kube/queue-worker.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: govportal-queue
  namespace: govportal
spec:
  replicas: 2
  selector:
    matchLabels:
      app: govportal
      component: queue
  template:
    metadata:
      labels:
        app: govportal
        component: queue
    spec:
      containers:
        - name: queue-worker
          image: your-registry/govportal:latest
          command: ["php", "artisan", "queue:work",
                    "--sleep=3", "--tries=3", "--max-time=3600"]
          envFrom:
            - secretRef:
                name: govportal-env
          resources:
            requests:
              cpu: "200m"
              memory: "256Mi"
            limits:
              cpu: "500m"
              memory: "512Mi"
```

---

## Database Migration Strategy

### First Deployment

```bash
# Run all pending migrations
php artisan migrate --force

# Seed required data (roles, permissions, default settings)
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=SettingsSeeder --force
```

### Subsequent Deployments

Always follow this order to avoid downtime:

1. **Deploy backward-compatible migration** — new nullable columns, new tables (old application code still works)
2. **Deploy new application code** — reads new columns, writes to new schema
3. **Deploy cleanup migration** — drop old columns, add NOT NULL constraints

```bash
# Verify migration status before and after
php artisan migrate:status

# Run pending migrations (--force required in production)
php artisan migrate --force
```

### Rollback Procedure

```bash
# Rollback last migration batch
php artisan migrate:rollback

# Rollback specific number of steps
php artisan migrate:rollback --step=3

# Emergency: restore from backup (see Backup Strategy below)
```

**Rule:** Every `up()` method must have a working `down()` method. Test rollback locally before every deploy containing a migration.

---

## Zero-Downtime Deployment

Kubernetes rolling updates (`maxUnavailable: 0`) ensure no requests are dropped during deployment.

### Deployment Script

```bash
#!/bin/bash
# scripts/deploy.sh

set -euo pipefail

IMAGE_TAG="${1:-latest}"
REGISTRY="your-registry.dkr.ecr.ap-southeast-1.amazonaws.com"
IMAGE="${REGISTRY}/govportal:${IMAGE_TAG}"

echo "Building Docker image: ${IMAGE}"
docker build -t "${IMAGE}" .

echo "Pushing image to registry"
docker push "${IMAGE}"

echo "Running pre-deploy migrations"
kubectl run govportal-migrate \
  --image="${IMAGE}" \
  --rm \
  --restart=Never \
  --namespace=govportal \
  --env-from=secret/govportal-env \
  -- php artisan migrate --force

echo "Warming cache after migration"
kubectl run govportal-cache-warm \
  --image="${IMAGE}" \
  --rm \
  --restart=Never \
  --namespace=govportal \
  --env-from=secret/govportal-env \
  -- php artisan cache:warm

echo "Rolling update"
kubectl set image deployment/govportal-app \
  govportal="${IMAGE}" \
  --namespace=govportal

echo "Waiting for rollout to complete"
kubectl rollout status deployment/govportal-app \
  --namespace=govportal \
  --timeout=300s

echo "Deployment complete"
```

---

## Queue Workers

Queue workers run as a separate Kubernetes Deployment (see above). For VM deployments, use Supervisor.

### Supervisor Configuration (VM / Bare Metal)

```ini
; /etc/supervisor/conf.d/govportal-worker.conf
[program:govportal-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/govportal/artisan queue:work redis
    --sleep=3
    --tries=3
    --max-time=3600
    --queue=default,emails
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/govportal-worker.log
stopwaitsecs=3600
```

```bash
# Reload after config changes
supervisorctl reread
supervisorctl update
supervisorctl start govportal-worker:*

# Restart workers gracefully after deploying new code
supervisorctl restart govportal-worker:*
```

### Scheduler

The Laravel scheduler must run every minute. Add to crontab:

```bash
# crontab -e (as www-data user)
* * * * * cd /var/www/govportal && php artisan schedule:run >> /dev/null 2>&1
```

Or as a Kubernetes CronJob:

```yaml
# .kube/scheduler.yaml
apiVersion: batch/v1
kind: CronJob
metadata:
  name: govportal-scheduler
  namespace: govportal
spec:
  schedule: "* * * * *"
  concurrencyPolicy: Forbid
  jobTemplate:
    spec:
      template:
        spec:
          containers:
            - name: scheduler
              image: your-registry/govportal:latest
              command: ["php", "artisan", "schedule:run"]
              envFrom:
                - secretRef:
                    name: govportal-env
          restartPolicy: OnFailure
```

---

## CDN Configuration

### Cloudflare Page Rules

Configure in the Cloudflare dashboard (ordered by priority):

| Rule | Match | Settings |
|------|-------|---------|
| 1 | `/admin/*` | Cache Level: Bypass |
| 2 | `*.css, *.js, *.png, *.jpg, *.woff2` | Cache Level: Cache Everything, Edge TTL: 1 month |
| 3 | `/ms/*, /en/*` | Cache Level: Cache Everything, Edge TTL: 1 hour, Bypass on Cookie: `gov_session` |
| 4 | `/api/v1/directory*` | Cache Level: Cache Everything, Edge TTL: 5 minutes |

### Cache Purge on Deploy

```bash
# Purge entire zone (use for major version deploys)
curl -X POST "https://api.cloudflare.com/client/v4/zones/${CF_ZONE_ID}/purge_cache" \
  -H "Authorization: Bearer ${CF_API_TOKEN}" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'

# Purge specific URLs (preferred — use for targeted content deploys)
curl -X POST "https://api.cloudflare.com/client/v4/zones/${CF_ZONE_ID}/purge_cache" \
  -H "Authorization: Bearer ${CF_API_TOKEN}" \
  -H "Content-Type: application/json" \
  --data '{
    "files": [
      "https://digital.gov.my/ms",
      "https://digital.gov.my/en",
      "https://digital.gov.my/ms/siaran"
    ]
  }'
```

---

## Backup Strategy

### Database Backups

```bash
#!/bin/bash
# scripts/backup-db.sh

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="govportal_${TIMESTAMP}.dump"
S3_BUCKET="govportal-backups"

# Create PostgreSQL dump (custom format — faster restore)
PGPASSWORD="${DB_PASSWORD}" pg_dump \
  --host="${DB_HOST}" \
  --username="${DB_USERNAME}" \
  --dbname="${DB_DATABASE}" \
  --format=custom \
  --file="/tmp/${BACKUP_FILE}"

# Upload to S3
aws s3 cp "/tmp/${BACKUP_FILE}" \
  "s3://${S3_BUCKET}/db/${BACKUP_FILE}" \
  --storage-class STANDARD_IA

rm "/tmp/${BACKUP_FILE}"
echo "Backup complete: s3://${S3_BUCKET}/db/${BACKUP_FILE}"
```

Schedule in crontab (02:00 MYT = 18:00 UTC):

```bash
0 18 * * * /var/www/govportal/scripts/backup-db.sh >> /var/log/db-backup.log 2>&1
```

### Backup Retention Policy

| Backup type | Retention |
|-------------|-----------|
| Daily | 30 days |
| Weekly (Sunday) | 3 months |
| Monthly (1st) | 1 year |
| Pre-deploy snapshot | 7 days |

### Restore from Backup

```bash
# List available backups
aws s3 ls s3://govportal-backups/db/

# Download backup
aws s3 cp s3://govportal-backups/db/govportal_20260220_020000.dump /tmp/restore.dump

# Restore
PGPASSWORD="${DB_PASSWORD}" pg_restore \
  --host="${DB_HOST}" \
  --username="${DB_USERNAME}" \
  --dbname="${DB_DATABASE}" \
  --clean \
  --no-owner \
  /tmp/restore.dump
```

---

## Monitoring and Alerting

### Health Check Endpoints

```
GET /up       -> 200 OK (used by ALB health check and Kubernetes probes)
```

### Laravel Health Package

```php
// app/Providers/AppServiceProvider.php
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;

Health::checks([
    DatabaseCheck::new(),
    RedisCheck::new(),
    UsedDiskSpaceCheck::new()->warnWhenUsedSpaceIsAbovePercentage(80),
]);
```

### Sentry Error Tracking

```php
// config/sentry.php
return [
    'dsn'                  => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate'   => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    'profiles_sample_rate' => 0.1,
    'send_default_pii'     => false,  // PDPA compliance — no PII in error reports
];
```

### Slow Query Logging

```php
// app/Providers/AppServiceProvider.php
DB::listen(function ($query) {
    if ($query->time > 500) {  // > 500ms
        Log::channel('slow_queries')->warning('Slow query detected', [
            'sql'     => $query->sql,
            'time_ms' => $query->time,
        ]);
    }
});
```

### Target Metrics

| Metric | Target | Alert threshold |
|--------|--------|----------------|
| CDN hit rate | > 90% | < 80% |
| Redis hit rate | > 85% | < 75% |
| Avg response time | < 100ms | > 500ms |
| Error rate | < 0.1% | > 1% |
| Queue backlog | < 100 jobs | > 500 jobs |

---

## Rollback Procedures

### Application Rollback

```bash
# Roll back to previous image tag
kubectl set image deployment/govportal-app \
  govportal=your-registry/govportal:PREVIOUS_TAG \
  --namespace=govportal

kubectl rollout status deployment/govportal-app --namespace=govportal

# Or use Kubernetes rollout history
kubectl rollout history deployment/govportal-app --namespace=govportal
kubectl rollout undo deployment/govportal-app --namespace=govportal
```

### Database Rollback

```bash
# Roll back last migration batch
php artisan migrate:rollback

# If rollback causes data loss, restore from pre-deploy backup instead
PGPASSWORD="${DB_PASSWORD}" pg_restore \
  --host="${DB_HOST}" \
  --username="${DB_USERNAME}" \
  --dbname="${DB_DATABASE}" \
  --clean \
  /tmp/pre-deploy-backup.dump
```

### Cache Rollback

After any rollback, clear caches to prevent stale HTML from serving:

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## Performance Benchmarking

Run before and after each major deploy to detect regressions.

### wrk Benchmark

```bash
# Install wrk
brew install wrk      # macOS
apt install wrk       # Ubuntu

# Benchmark homepage (100 concurrent, 30 seconds)
wrk -t4 -c100 -d30s https://digital.gov.my/ms

# Expected output on a warmed cache:
# Requests/sec: > 1000
# Latency avg:  < 50ms
# Latency 99%:  < 200ms
```

### Artillery Load Test

```yaml
# artillery/load-test.yml
config:
  target: https://digital.gov.my
  phases:
    - duration: 60
      arrivalRate: 50
      name: Ramp up
    - duration: 120
      arrivalRate: 200
      name: Sustained load
  defaults:
    headers:
      Accept-Language: ms-MY

scenarios:
  - name: Public pages
    flow:
      - get:
          url: /ms
      - get:
          url: /ms/siaran
      - get:
          url: /ms/pencapaian
      - get:
          url: /ms/direktori
```

```bash
npx artillery run artillery/load-test.yml
```

### Performance Targets

| Metric | Target | Alert if exceeded |
|--------|--------|------------------|
| Homepage (cached) | < 50ms p99 | > 200ms |
| Homepage (cold) | < 500ms p99 | > 1s |
| API `/broadcasts` | < 100ms p99 | > 300ms |
| Concurrent users | 10,000+ | < 5,000 |
| Error rate | < 0.1% | > 1% |

---

## References

- [Architecture](architecture.md)
- [Caching Strategy](caching.md)
- [Security Guide](security.md)
- [Testing Guide](testing.md)
- [API Reference](api.md)
- [Laravel Octane Docs](https://laravel.com/docs/11.x/octane)
- [FrankenPHP Docs](https://frankenphp.dev/docs/)
