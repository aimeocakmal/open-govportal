---
name: git-commits
description: "Use when writing git commit messages, naming branches, pushing, or opening pull requests in OpenGovPortal. Conventional Commits 1.0.0 with lowercase types and no emoji."
---

# Git Commit, Branch and Push Convention

[Conventional Commits 1.0.0](https://www.conventionalcommits.org/), unmodified: lowercase types, optional lowercase
scope, no emoji anywhere.

## Format

```
<type>[(<scope>)][!]: <subject>

[body — explain why, not what; wrap at 72 chars]

[footer(s)]
```

## Types

| Type       | When to use                                           |
|------------|-------------------------------------------------------|
| `feat`     | New user-facing feature                               |
| `fix`      | Bug fix                                               |
| `refactor` | Code change with no behavior change                   |
| `perf`     | Performance improvement                               |
| `test`     | Adding or updating tests                              |
| `docs`     | Documentation only                                    |
| `style`    | Formatting, whitespace, Pint output — no logic change |
| `build`    | Dependencies, Composer, Vite, build tooling           |
| `ci`       | CI workflow changes                                   |
| `chore`    | Routine maintenance                                   |
| `revert`   | Reverts a previous commit                             |

## Scopes

Optional, lowercase, one word. Use the area of the codebase the change belongs to. Scopes already in use:
`docs`, `content`, `seed`. Other natural ones for this project: `admin`, `ai`, `cache`, `i18n`, `theme`, `search`,
`auth`, or a page name (`siaran`, `direktori`, `dasar`).

Omit the scope when a change is genuinely repo-wide.

## Subject

- Imperative mood — "add", not "added" or "adds".
- Lowercase first word, no trailing period.
- 70 characters or fewer.
- Describe the change, not the file touched.

## Body

Optional for trivial changes, expected for everything else. Explain **why** the change was made and what it affects —
the diff already shows what changed. Wrap at 72 characters. Separate distinct points into paragraphs.

## Footers

- **Breaking change** — `!` before the colon *and* a `BREAKING CHANGE:` footer explaining the migration path.
- **Issue references** — `Refs: #12`, `Closes: #12`.
- **Attribution** — keep `Co-Authored-By:` trailers when pairing or when an AI agent authored part of the change.

## Examples

```
feat(seed): replace real-world demo data with OpenGovPortal content
docs(content): add sample content and image pack guides
chore(docs): align examples with OpenGovPortal and release 0.8.0
style: apply code formatting improvements in SetLocale, Setting, and web files
fix(direktori): return both locales from the live search query
feat(api)!: rename /users endpoint to /accounts
```

A fuller example with a body:

```
fix(cache): invalidate broadcast tags when a policy file is replaced

Replacing a policy file left the dasar listing serving a stale download
count, because the observer only flushed the policy tag and not the
file tag that the listing reads through.

Refs: #14
```

## Branches

`<type>/<kebab-case-description>`, branched from `main` — for example `feat/sample-content-pack`.

Use the same type vocabulary as commits. Keep one logical change per branch.

## Pushing and pull requests

Per [CONTRIBUTING.md](../../../CONTRIBUTING.md):

1. Branch from `main` — never commit directly to it.
2. Keep changes focused and small; no unrelated refactors bundled in.
3. Run the relevant tests and `vendor/bin/pint --dirty` before pushing.
4. Update `docs/` when behavior, schema, or routes change.
5. Confirm both locales still work when the change touches a public page.

PR titles follow the commit format exactly — a squash merge turns the PR title into the commit subject.

**Never commit or push unless you were asked to.** Pushing is outward-facing and hard to undo; confirm first when the
instruction is ambiguous. Never commit secrets, `.env` files, or credentials.

## Don't

- No emoji, anywhere — not in subjects, bodies, branch names, or PR titles.
- No capitalized types: `Feat:` and `Fix:` are wrong; use `feat:` and `fix:`.
- No vague subjects: `fix: bug`, `chore: wip`, `update file`.
- No bundled changes — split unrelated work into separate commits.
- Do not commit generated files: `CLAUDE.md`, `AGENTS.md`, `boost.json`, `.mcp.json` and `.claude/` are gitignored.
  Edit `.ai/guidelines/` and run `php artisan boost:update` instead.
