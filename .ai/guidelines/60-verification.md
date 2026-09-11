# How To Verify Your Work

Minimum checks before marking any task complete.

**Run tests:**

```bash
php artisan test --filter=Feature
php artisan test --filter=Unit
```

**Check both locales respond 200:**

```bash
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/ms/{route}
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/en/{route}
```

**Check migration status:**

```bash
php artisan migrate:status
```

**Check the page cache is set after the first request:**

```bash
php artisan tinker --execute="Cache::has('page:/ms/{route}')"
```

Full per-feature validation commands: [docs/agentic-coding.md → Per-Feature Validation Reference](docs/agentic-coding.md)

## Status Labels

When updating [docs/pages-features.md](docs/pages-features.md), use exactly:

- `Planned` — not yet built
- `Implemented` — built and validated (record the test name)
- `Deferred` — postponed (record why)

Never remove a status label. Change `Planned` → `Implemented` only after validation passes.
