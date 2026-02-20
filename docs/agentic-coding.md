# Agentic Coding Playbook

This document defines how coding agents should execute work in this repository.

## Goals

- Minimize ambiguity before implementation.
- Keep scope aligned to kd-portal parity.
- Require validation evidence before marking work complete.
- Keep documentation synchronized with code changes.

## Standard Task Format

When creating or accepting a task, include:

- `Objective`: single sentence outcome.
- `Scope`: files, routes, models, or modules in scope.
- `Out of Scope`: explicit exclusions.
- `Acceptance Criteria`: testable checks.
- `Validation`: commands to run and expected result.
- `Risks`: migration, performance, or security risks.

## Execution Loop

1. `Understand`
   - Read relevant docs in `docs/README.md` order.
   - Identify assumptions and unresolved decisions.
2. `Plan`
   - Break work into small, verifiable steps.
   - Sequence DB changes before application logic, then UI.
3. `Implement`
   - Make minimal changes required for the acceptance criteria.
   - Keep naming and locale conventions consistent with existing docs.
4. `Validate`
   - Run targeted tests first, then broader suite if needed.
   - Record command outputs and failures in the work summary.
5. `Document`
   - Update affected docs in the same change.
   - Mark changed behavior and constraints explicitly.

## Definition Of Done

A task is done only when all are true:

- Acceptance criteria are satisfied.
- Required tests or checks were run and reported.
- No unresolved contradictions between docs and code.
- Any new route/model/config is documented.
- Caching, i18n, and security implications are acknowledged.

## Required Quality Gates

- `Correctness`: behavior matches the documented route/model contract.
- `Safety`: no leakage of private data in cache or logs.
- `Performance`: avoid N+1 queries and unnecessary cache misses.
- `Operability`: failures are observable (logs/metrics), not silent.
- `Recoverability`: migration/rollback path is clear for schema changes.

## Validation Baseline

Use the smallest command set that proves the change:

```bash
php artisan test --filter=Feature
php artisan test --filter=Unit
```

If tests are not yet available for the changed area, state that gap explicitly and provide manual verification steps.

## Documentation Update Policy

- Update docs in the same PR for any behavior, route, schema, cache, or role change.
- Remove stale references instead of leaving TODO links.
- Prefer exact file paths and concrete examples over narrative-only guidance.
