# PHPUnit To Pest 4 Migration Plan

Plan date: 2026-09-21. Branch: `test/pest-migration`.
Tracks [issue #1](https://github.com/aimeocakmal/open-govportal/issues/1);
unblocks [issue #8](https://github.com/aimeocakmal/open-govportal/issues/8).

This document is a time-boxed implementation plan, not a standing reference. Once the migration
merges, [../testing.md](../testing.md) becomes the source of truth and this file is history.

## Table of Contents

1. [Overview](#overview)
2. [Why Not Pest 5](#why-not-pest-5)
3. [Global Constraints](#global-constraints)
4. [Phase 0: Fix The 15 Locale Failures](#phase-0-fix-the-15-locale-failures)
5. [Phase 1: Dependency Swap](#phase-1-dependency-swap)
6. [Phase 2: Convert The Suite](#phase-2-convert-the-suite)
7. [Phase 3: Idiomatic Patterns](#phase-3-idiomatic-patterns)
8. [Phase 4: Architecture Tests](#phase-4-architecture-tests)
9. [Phase 5: Documentation And Guidelines](#phase-5-documentation-and-guidelines)
10. [Verification](#verification)
11. [Risks And Rollback](#risks-and-rollback)
12. [Related Docs](#related-docs)

---

## Overview

The suite is currently red. Baseline measured on `main` at `b7a9da5`:

```bash
php artisan test --compact
```

```
Tests:    15 failed, 649 passed (1271 assertions)
Duration: 37.15s
```

All 15 failures share one cause: `Livewire::test()` bypasses route middleware, so `SetLocale` never runs and components
render under the framework default `en` while the tests assert Malay copy. Full diagnosis in issue #1.

**Suite shape**, measured rather than assumed:

| Metric                                        | Value                                                              |
|-----------------------------------------------|--------------------------------------------------------------------|
| Test files                                    | 59 (`TestCase.php`, `Unit/ExampleTest.php`, 57 flat in `Feature/`) |
| Test methods                                  | 644, all `public function test_*(): void`                          |
| Test cases at runtime                         | 664 (644 − 2 provider-driven methods + 22 expanded cases)          |
| Files using `RefreshDatabase`                 | 56 of 57 — only `AiProviderValidatorTest.php` does not             |
| Files defining `setUp()`                      | 14; none define `tearDown()`                                       |
| Data providers                                | 1 (`PolicyAuthorizationTest`, 11 named cases, used twice)          |
| `#[Test]` / `@test` / `#[Group]` / `@depends` | 0 of each                                                          |
| Native PHPUnit mocks (`createMock` etc.)      | 0                                                                  |
| `Livewire::test()` call sites                 | 68 across 10 files, plus 14 `Livewire::actingAs()->test()`         |
| CI workflows                                  | none — there is no `.github/` directory                            |

The suite is unusually friendly to conversion: one empty base class, one trait, no groups, no dependencies between
tests, no native mocks.

**Environment at planning time** — every version below was read from `composer show --installed`, not from
`composer.json` constraints. The gap between the two matters in [Phase 1](#phase-1-dependency-swap).

| Package | Installed |
|---------|-----------|
| PHP (Herd CLI) | `8.5.8` |
| `laravel/framework` | `12.52.0` |
| `filament/filament` | `5.2.2` |
| `livewire/livewire` | `4.1.4` |
| `phpunit/phpunit` | `11.5.55` |
| `nunomaduro/collision` | `8.9.1` |

**Decisions taken before planning:**

| Decision         | Choice                                  | Basis                                                                        |
|------------------|-----------------------------------------|------------------------------------------------------------------------------|
| Pest major       | Pest 4 (`v4.7.8`)                       | Pest 5 is hard-blocked on Laravel 12 — see below                             |
| Conversion depth | Full idiomatic rewrite                  | `it()`/`describe()`, `expect()`, datasets, arch tests                        |
| Sequencing       | Fix locale failures first, then convert | Converting a green suite makes any later red unambiguously conversion-caused |

---

## Why Not Pest 5

Verified with `composer update --dry-run` against scratch copies of `composer.json`, not inferred from changelogs.

| Target                            | Result                                                                                                                                                                                                                                                      |
|-----------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Pest 5 on Laravel 12              | **Fails.** `pestphp/pest v5.2.1` requires `symfony/process ^8.1.7`; `laravel/framework v12.x` requires `symfony/process ^7.2.0`. Only one version of a package can be installed.                                                                            |
| Pest 5 drift plugin on Laravel 12 | **Fails.** `pestphp/pest-plugin-drift v5.0.0` additionally requires `symfony/finder ^8.1.1` against Laravel 12's `^7.2.0`.                                                                                                                                  |
| Pest 5 on Laravel 13              | **Resolves**, but requires `laravel/framework ^13`, `laravel/tinker ^3`, `spatie/laravel-activitylog 5.x`, `spatie/laravel-permission 8.x`, `malzariey/filament-daterangepicker-filter 5.x` and `php ^8.4` — a framework major plus four dependency majors. |
| Pest 4 on Laravel 12              | **Resolves**, but only with `--with-all-dependencies`, which upgrades ~60 packages. See [Phase 1](#phase-1-dependency-swap).                                                                                                                                                               |

Pest 5 belongs to the Symfony 8 / Laravel 13 generation. Revisit it as a follow-up when the Laravel 13 upgrade is
scheduled — not as part of this migration.

---

## Global Constraints

Every phase inherits these. They are not negotiable within this branch.

- **`APP_LOCALE=en` stays.** `.env` and `config/app.php` are not touched. This is a recorded decision in issue #1, not
  an oversight.
- **Two senses of "default".** The site default a visitor lands on is `ms` (`routes/web.php:14` hardcodes
  `$browser === 'en' ? 'en' : 'ms'`). The framework default — what `App::getLocale()` returns for Livewire tests,
  console commands and non-locale-prefixed routes — is `en`. All 15 failures sit in the second category.
- **Do not change production FTS behaviour** in `app/Services/SearchService.php` to satisfy a test.
- **Never edit `CLAUDE.md`, `AGENTS.md`, `boost.json` or `.mcp.json`.** They are gitignored generated output. Edit
  `.ai/guidelines/`, then run `php artisan boost:update`.
- **Both locales must pass.** A task is not done until `ms` and `en` are both tested.
- **`vendor/bin/pint --dirty` clean before every commit.**
- **The case count must not drop below 664.** See [Verification](#verification) for why this stays meaningful even as
  tests are consolidated into datasets.

---

## Phase 0: Fix The 15 Locale Failures

Still on PHPUnit. The goal is a green suite to convert *from*.

### The helper

`tests/TestCase.php` is currently a 10-line empty stub. Add an opt-in helper:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\App;

abstract class TestCase extends BaseTestCase
{
    protected function useMalayLocale(): void
    {
        App::setLocale('ms');
    }
}
```

**Opt-in by design — do not override `setUp()`.** A global locale switch would break tests that legitimately assert
English under the framework default. The clearest example is `tests/Feature/AiChatTest.php:60`:

```php
public function test_widget_renders_default_bot_name_when_not_configured(): void
{
    $this->enableChatbot();

    Livewire::test(AiChat::class)
        ->assertSee(__('ai.default_name'));
}
```

That resolves `__('ai.default_name')` under whatever locale is active and passes today. A blanket `setlocale` in
`setUp()` changes what it asserts without anyone noticing.

### The 15 call sites

Add `$this->useMalayLocale();` as the first line of each, before the factory and `Setting::set()` calls.

| File                                   | Method                                                | Line |
|----------------------------------------|-------------------------------------------------------|------|
| `tests/Feature/SiaranPageTest.php`     | `test_siaran_list_filters_by_type`                    | 104  |
| `tests/Feature/SiaranPageTest.php`     | `test_siaran_list_shows_all_types_when_no_filter`     | 119  |
| `tests/Feature/PencapaianPageTest.php` | `test_pencapaian_list_filters_by_year`                | 104  |
| `tests/Feature/PencapaianPageTest.php` | `test_pencapaian_list_shows_all_years_when_no_filter` | 121  |
| `tests/Feature/DirektoriPageTest.php`  | `test_direktori_filters_by_department_dropdown`       | 181  |
| `tests/Feature/DirektoriPageTest.php`  | `test_direktori_department_dropdown_populated`        | 226  |
| `tests/Feature/CarianPageTest.php`     | `test_search_results_returns_matching_content`        | 35   |
| `tests/Feature/CarianPageTest.php`     | `test_search_results_case_insensitive`                | 54   |
| `tests/Feature/CarianPageTest.php`     | `test_search_no_results_message`                      | 73   |
| `tests/Feature/CarianPageTest.php`     | `test_search_overrides_shown_first`                   | 87   |
| `tests/Feature/AiChatTest.php`         | `test_widget_renders_bot_name`                        | 51   |
| `tests/Feature/AiChatTest.php`         | `test_welcome_message_displayed`                      | 68   |
| `tests/Feature/AiChatTest.php`         | `test_custom_disclaimer_from_settings`                | 304  |
| `tests/Feature/AiChatTest.php`         | `test_custom_placeholder_from_settings`               | 313  |
| `tests/Feature/ContentPreviewTest.php` | `test_preview_shows_preview_banner`                   | 58   |

Worked example — `tests/Feature/SiaranPageTest.php:104`:

```php
public function test_siaran_list_filters_by_type(): void
{
    $this->useMalayLocale();

    Broadcast::factory()->published()->announcement()->create([
        'title_ms' => 'Pengumuman Khas',
    ]);
    Broadcast::factory()->published()->news()->create([
        'title_ms' => 'Berita Terkini',
    ]);

    Livewire::test(\App\Livewire\SiaranList::class)
        ->set('type', 'announcement')
        ->assertSee('Pengumuman Khas')
        ->assertDontSee('Berita Terkini');
}
```

And `tests/Feature/AiChatTest.php:51`:

```php
public function test_widget_renders_bot_name(): void
{
    $this->useMalayLocale();

    $this->enableChatbot();
    Setting::set('ai_chatbot_name_ms', 'Pembantu Ujian');

    Livewire::test(AiChat::class)
        ->assertSee('Pembantu Ujian');
}
```

### Also in this phase

- Fix the stale comment at `tests/Feature/ContentPreviewTest.php:75`:
  ```php
  // Default locale is ms, so banner renders in Malay
  ```
  That is wrong today — the framework default is `en` — and becomes actively misleading once the test sets the locale
  explicitly. Replace with something true, e.g.
  `// Preview routes are not locale-prefixed, so set the locale explicitly.`

- **`CarianPageTest` caveat.** The suite runs SQLite `:memory:` (`phpunit.xml:27-28`), so `SearchService` takes the
  `LIKE` fallback rather than PostgreSQL FTS. Apply the locale fix, re-run, and only investigate those four further if
  they still fail. Do not touch `SearchService` to make a test pass.

### Gate

- [ ] `php artisan test --compact` reports 0 failures, 664 tests
- [ ] `vendor/bin/pint --dirty` clean
- [ ] `git diff main -- .env config/app.php` is empty
- [ ] Commit: `fix(i18n): set malay locale explicitly in livewire tests` with footer `Refs: #1`

---

## Phase 1: Dependency Swap

```bash
composer remove --dev phpunit/phpunit
composer require --dev \
  pestphp/pest:^4.7 \
  pestphp/pest-plugin-laravel:^4.1 \
  pestphp/pest-plugin-livewire:^4.1 \
  pestphp/pest-plugin-drift:^4.1 \
  --with-all-dependencies
```

Also raise the PHP floor in `composer.json` — Pest 4 requires `^8.3.0`:

```diff
  "require": {
-     "php": "^8.2",
+     "php": "^8.3",
```

`config.allow-plugins` already contains `"pestphp/pest-plugin": true`, so no change is needed there.

**Both version pins are load-bearing. Do not relax either one:**

| Pin | Why |
|-----|-----|
| `pest-plugin-livewire:^4.1` | `v4.0.1` requires `livewire/livewire ^3.6.4`. This project runs **livewire 4.1.4**. Only `v4.1.0` allows `^3.7.4\|^4.0.1`. Relaxing to `^4.0` makes Composer try to downgrade Livewire to 3.x. |
| `pest-plugin-laravel:^4.1` | `v4.1.0` requires `laravel/framework ^11.45.2\|^12.52.0\|^13.0`. Installed is **exactly 12.52.0** — at the floor. Any resolve that downgrades Laravel breaks this. |

**Expected resolved set**, from the verified dry run:

| Package                        | Version  | Note                                 |
|--------------------------------|----------|--------------------------------------|
| `pestphp/pest`                 | `v4.7.8` |                                      |
| `pestphp/pest-plugin-laravel`  | `v4.1.0` |                                      |
| `pestphp/pest-plugin-livewire` | `v4.1.0` |                                      |
| `pestphp/pest-plugin-drift`    | `v4.1.0` | removed again after Phase 2          |
| `pestphp/pest-plugin-arch`     | `v4.0.2` | pulled transitively, used in Phase 4 |
| `pestphp/pest-plugin-mutate`   | `v4.0.1` | pulled transitively                  |
| `nunomaduro/collision`         | `v8.9.5` | upgraded from installed `v8.9.1`     |
| `phpunit/phpunit`              | `12.5.x` | replaces installed `11.5.55`         |

### `--with-all-dependencies` is unavoidable, and it upgrades far more than Pest

This is the single biggest surprise in the migration. **Read this before running the install.**

A minimal partial update does not resolve. `pest v4.7.8` needs `symfony/process ^7.4.8`, `pest-plugin-drift` needs
`symfony/finder ^7.4.6`, `collision v8.9.5` needs `symfony/console ^7.4.14`, and Pest's bundled `paratest` needs
`phpunit/php-code-coverage ^12.5.3` — all of which sit below those floors in the current lock file. Naming them
one by one in a partial `composer update` just moves the error to the next package down the chain; it was tried
and it cascades without converging.

So `-W` is required, and it carries a large upgrade set along with it. Verified by dry run, the notable ones:

| Package | Installed | After `-W` |
|---------|-----------|------------|
| `filament/*` (all 10 packages) | `v5.2.2` | `v5.8.4` |
| `livewire/livewire` | `v4.1.4` | `v4.4.5` |
| `laravel/framework` | `v12.52.0` | `v12.69.2` |
| `laravel/octane` | `v2.13.5` | `v2.19.1` |
| `laravel/boost` | `v2.1.8` | `v2.9.1` |
| `echolabsdev/prism` | `v0.99.19` | `v0.99.22` |
| `guzzlehttp/guzzle` | `7.10.0` | `7.15.5` |
| `nesbot/carbon` | `3.11.1` | `3.14.0` |

…plus roughly 50 more minor bumps. All are within their declared semver ranges, so nothing here is reckless — but
a Filament minor jump of six releases and a Livewire jump of three alongside a test-framework swap is more change
than the phrase "install Pest" suggests.

**This is exactly what the gate below is for.** Do not skip it, and do not batch it with any conversion work. If the
suite goes red at this gate, the cause is the upgrade or PHPUnit 11 → 12.5 — not Pest syntax, because no test has
been rewritten yet.

If the upgrade turns out to be the problem and you want to decouple it, the fallback is to land the ecosystem
upgrade as its own separate commit *first* (`composer update -W` with PHPUnit still in place, verify 664 green),
then do the Pest swap on top. That splits one risky step into two attributable ones at the cost of an extra cycle.

### `tests/Pest.php`

Run `./vendor/bin/pest --init`, then correct the generated file. The `uses()` configuration **must not** blanket-apply
`Tests\TestCase` to `tests/Unit`, because `tests/Unit/ExampleTest.php` extends raw `PHPUnit\Framework\TestCase`:

```php
<?php

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```

`RefreshDatabase` is applied suite-wide across `Feature` because 56 of 57 files already use it. The exception is
`tests/Feature/AiProviderValidatorTest.php` — pure service and HTTP-fake tests that touch no database. Applying
`RefreshDatabase` to it is harmless but wasteful; if the suite slows measurably, narrow the `->use()` rather than the
`->extend()`.

### Supporting files

- `.gitignore` — add `/.pest` alongside the existing `/.phpunit.cache` and `.phpunit.result.cache` entries.
- `phpunit.xml` — keep as-is. Pest reads it. Note that it deliberately sets no `APP_LOCALE`;
  see [Global Constraints](#global-constraints).
- `composer.json` `scripts.test` — no change needed; `php artisan test` dispatches to Pest once installed.

### Gate

- [ ] `php artisan test --compact` still reports 0 failures, 664 tests — with every test still in PHPUnit class form,
  running under the Pest binary
- [ ] `vendor/bin/pint --dirty` clean
- [ ] Commit: `build: replace phpunit with pest 4` with footer `Refs: #1`

This gate matters more than any other in the plan. Pest runs class-based PHPUnit tests unchanged, so a green suite
here proves both the dependency swap *and* the ecosystem upgrade are sound **before a single test is rewritten**. If
it is red, the cause is the upgrade, Filament 5.8.4, Livewire 4.4.5 or PHPUnit 11 → 12.5 — never the conversion.
That is what makes "any post-conversion failure is conversion-caused" actually true rather than merely hoped for.

---

## Phase 2: Convert The Suite

**Use `pest-plugin-drift` as a mechanical first pass, then hand-polish.** Drift handles the rote 644-method
class-to-closure transform correctly and tirelessly; hand work is better spent on the idiomatic polish in Phase 3 than
on retyping 644 method signatures. Drift is not a substitute for Phase 3 — it produces "PHPUnit in Pest clothing", which
is the starting point, not the goal.

```bash
./vendor/bin/pest --drift
```

Drift rewrites files in place. Run it per batch, not across the whole suite at once, so each commit stays reviewable.

### Batches

Grouped by feature area rather than alphabetically, so a reviewer sees related changes together. Each batch is its own
commit and each must end green.

| # | Batch                       | Files                                                                                                                                                                                                                                                                                           | Why grouped                                                       |
|---|-----------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|
| 1 | Public pages                | `SiaranPageTest`, `PencapaianPageTest`, `DirektoriPageTest`, `CarianPageTest`, `DasarPageTest`, `HubungiKamiPageTest`, `ProfilKementerianPageTest`, `StatistikPageTest`, `HomepageTest`, `StaticPageTest`, `AccessibilityMenuTest`                                                              | Heaviest Livewire and locale surface; where datasets pay off most |
| 2 | AI                          | `AiChatTest`, `AiChatConversationTest`, `AiChatMessageTest`, `AiServiceTest`, `AiEditorActionsTest`, `AiProviderValidatorTest`, `AiUsageLogTest`, `ManageAiSettingsTest`, `RagServiceTest`, `GenerateConversationTitleJobTest`, `GenerateEmbeddingJobTest`                                      | `Prism::fake()` and all 12 `$this->mock()` sites live here        |
| 3 | Admin and Filament          | `ActivityLogResourceTest`, `MenuResourceTest`, `RoleResourceTest`, `UserResourceTest`, `EditProfileTest`, `PasswordResetTest`, `PlatformVersionTest`, `PolicyAuthorizationTest`                                                                                                                 | The triplicated `getAdmin()` and the sole data provider           |
| 4 | Models and content          | `BroadcastTest`, `AchievementTest`, `PolicyTest`, `PolicyFileTest`, `CelebrationTest`, `HeroBannerTest`, `QuickLinkTest`, `MediaTest`, `MenuTest`, `MenuItemTest`, `PageCategoryTest`, `StaffDirectoryTest`, `ContentRevisionTest`, `SearchOverrideTest`, `UserModelTest`                       | Mostly plain assertions; lowest risk                              |
| 5 | Services and infrastructure | `SearchServiceTest`, `MediaDiskServiceTest`, `ImageOptimizationServiceTest`, `PageCacheObserverTest`, `ActivityLogTest`, `PurgeOldActivityLogsTest`, `PurgeOldChatConversationsTest`, `SitemapTest`, `ThemeSystemTest`, `ContentPreviewTest`, `FeedbackTest`, `ExampleTest`, `Unit/ExampleTest` | Instance properties and `markTestSkipped`                         |

### Per-batch gate

```bash
vendor/bin/pest --list-tests | grep -c '^ - '   # 664, every time
php artisan test --compact                      # 0 failures
vendor/bin/pint --dirty                         # clean
```

Commit each batch as `test: convert <area> tests to pest` with footer `Refs: #1`.

### After the final batch

Remove the conversion tool — it has no further use and should not ship as a permanent dev dependency:

```bash
composer remove --dev pestphp/pest-plugin-drift
```

---

## Phase 3: Idiomatic Patterns

Standardise on these. Every row is drawn from a real site in this codebase, so there is nothing to guess.

| Pattern                    | From                                                                                                                                                                                                                                        | To                                                                                           |
|----------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------|
| `setUp()` → `beforeEach()` | 14 files, e.g. `MenuResourceTest:15` calling `$this->seed(RoleSeeder::class)`                                                                                                                                                               | `beforeEach(fn () => seed(RoleSeeder::class));`                                              |
| Duplicated helper          | `getAdmin(): User` byte-identical in `AiEditorActionsTest:30`, `MenuResourceTest:21`, `RoleResourceTest:21`, `UserResourceTest:20`, plus `ManageAiSettingsTest:27` `superAdmin()` which differs only by a redundant `['is_active' => true]` (UserFactory already defaults it) — 5 methods, one helper                                                                                                                                         | one `function getAdmin(): User` in `tests/Pest.php`                                          |
| Data provider              | `PolicyAuthorizationTest:84` and `:98` use `#[DataProvider('contentModelProvider')]`; provider at `:112` returns 11 named cases                                                                                                             | `dataset('contentModels', [...])` in `tests/Pest.php`, consumed as `->with('contentModels')` |
| `expectException`          | `AchievementTest:84`, `BroadcastTest:95`, `PolicyTest:86` (all `QueryException`); `AiServiceTest:296` (`BadMethodCallException`)                                                                                                            | `->throws(QueryException::class)`                                                            |
| `markTestSkipped`          | `ImageOptimizationServiceTest:57,86,103,125`, each behind `if (! extension_loaded('gd'))`                                                                                                                                                   | `->skip(fn () => ! extension_loaded('gd'), 'GD extension not available')`                    |
| `Livewire::test()`         | 68 sites across 10 files                                                                                                                                                                                                                    | `livewire()` from `pest-plugin-livewire`                                                     |
| Assigned-variable Livewire | `AiChatTest:30,39,47,135,249,261,355`                                                                                                                                                                                                       | `expect(livewire(AiChat::class)->instance()->shouldRender())->toBeFalse()`                   |
| Typed instance property    | `ImageOptimizationServiceTest:18`, `SearchServiceTest:16`                                                                                                                                                                                   | assign in `beforeEach()`, read as `$this->service` inside closures                           |
| Private helpers            | `AiChatTest::enableChatbot()`, `AiServiceTest::setUpApiKey()`, `ManageAiSettingsTest::superAdmin()`, `GenerateConversationTitleJobTest::createConversationWithMessages()`, `AiEditorActionsTest::{getAdmin,enableAiEditor,disableAiEditor}` | file-local closures; promote to `tests/Pest.php` only where genuinely shared                 |
| Locale helper              | Phase 0's `useMalayLocale()`                                                                                                                                                                                                                | `function useMalayLocale(): void` in `tests/Pest.php`                                        |

Worked example — the `expectException` conversion at `tests/Feature/AchievementTest.php:84`:

```php
it('rejects an achievement without a title', function () {
    Achievement::factory()->create(['title_ms' => null]);
})->throws(\Illuminate\Database\QueryException::class);
```

And the data provider at `tests/Feature/PolicyAuthorizationTest.php:112` becomes, in `tests/Pest.php`:

```php
dataset('contentModels', [
    'Broadcast' => [App\Models\Broadcast::class],
    'Achievement' => [App\Models\Achievement::class],
    'Celebration' => [App\Models\Celebration::class],
    'Policy' => [App\Models\Policy::class],
    'StaffDirectory' => [App\Models\StaffDirectory::class],
    'PolicyFile' => [App\Models\PolicyFile::class],
    'HeroBanner' => [App\Models\HeroBanner::class],
    'QuickLink' => [App\Models\QuickLink::class],
    'Media' => [App\Models\Media::class],
    'Feedback' => [App\Models\Feedback::class],
    'SearchOverride' => [App\Models\SearchOverride::class],
]);
```

### Datasets for the bilingual duplication

Measured: 33 `_ms`-suffixed and 30 `_en`-suffixed test methods — roughly 30 locale pairs. Concentrated in:

| File                                                                                                  | `_ms` methods |
|-------------------------------------------------------------------------------------------------------|---------------|
| `AccessibilityMenuTest`                                                                               | 7             |
| `StatistikPageTest`, `StaticPageTest`, `SiaranPageTest`, `PencapaianPageTest`, `DirektoriPageTest`    | 3 each        |
| `ProfilKementerianPageTest`, `HubungiKamiPageTest`, `HomepageTest`, `DasarPageTest`, `CarianPageTest` | 2 each        |
| `UserModelTest`                                                                                       | 1             |

Where the assertion is identical across locales — `SiaranPageTest:16` and `:23`, both just `assertOk()` — collapse to a
plain dataset:

```php
it('returns ok', function (string $locale) {
    $this->get("/$locale/siaran")->assertOk();
})->with(['ms', 'en']);
```

Where the expected copy differs — `SiaranPageTest:30` asserts `'Siaran'`, `:38` asserts `'Broadcasts'` — use a keyed
dataset rather than forcing a shared assertion:

```php
it('shows the page title', function (string $locale, string $expected) {
    $this->get("/$locale/siaran")
        ->assertOk()
        ->assertSee($expected);
})->with([
    'ms' => ['ms', 'Siaran'],
    'en' => ['en', 'Broadcasts'],
]);
```

Do not flatten a pair into one test that asserts only the shared part. Losing the `en` copy assertion to save three
lines defeats the bilingual constraint.

---

## Phase 4: Architecture Tests

Full idiomatic Pest includes `arch()`. Add `tests/Feature/ArchTest.php`, and keep it small and true — an arch test that
fails on day one is noise that teaches people to ignore the suite.

Start with rules this codebase already satisfies:

```php
arch('no debug statements ship')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die'])
    ->not->toBeUsed();

arch('models extend eloquent')
    ->expect('App\Models')
    ->toExtend(Illuminate\Database\Eloquent\Model::class);

arch('livewire components extend component')
    ->expect('App\Livewire')
    ->toExtend(Livewire\Component::class);
```

Verify each rule passes before committing it. Any rule that fails is either a real finding worth a separate fix, or a
bad rule — resolve which before it lands.

---

## Phase 5: Documentation And Guidelines

### Rewrite `../testing.md`

The doc is 889 lines and substantially wrong **independent of this migration**. Verified discrepancies:

| Location           | Claims                                                                    | Reality                                                          |
|--------------------|---------------------------------------------------------------------------|------------------------------------------------------------------|
| `testing.md:82-84` | `CACHE_DRIVER=array`, `DB_CONNECTION=pgsql`, `DB_DATABASE=govportal_test` | `phpunit.xml:26-28` is `CACHE_STORE=array`, `sqlite`, `:memory:` |
| `testing.md:92-95` | a `<coverage>` block with HTML and clover output                          | no `<coverage>` block exists                                     |
| `testing.md:309`   | `tests/Unit/Models/BroadcastTest.php`                                     | does not exist                                                   |
| `testing.md:400`   | `tests/Feature/Http/BroadcastControllerTest.php`                          | does not exist                                                   |
| `testing.md:471`   | `tests/Feature/Http/HomeControllerTest.php`                               | does not exist                                                   |
| `testing.md:519`   | `tests/Feature/Livewire/DirectoriSearchTest.php`                          | does not exist                                                   |
| `testing.md:583`   | `tests/Feature/Livewire/ContactFormTest.php`                              | does not exist                                                   |

`find tests -type d` returns only `tests`, `tests/Unit`, `tests/Feature`. The per-feature `--filter=` table references
those phantom classes throughout.

Rewrite it against the real suite: the flat layout, Pest syntax, the shared helpers and datasets in `tests/Pest.php`,
and the actual `phpunit.xml`.

### Other files

- **`.ai/guidelines/60-verification.md`** mandates `php artisan test --filter=Feature` and `--filter=Unit`. Update to
  the Pest equivalents, then run `php artisan boost:update`. Edit the guideline, never `CLAUDE.md`.
- **`../conversion-timeline.md`** names PHPUnit at lines 282, 308, 323 and 811. Line 811 is the Week 14 coverage task
  this migration unblocks. Update the test-count progression with the post-migration figure and a
  `✅ COMPLETED YYYY-MM-DD` marker when the branch merges.
- **`README.md:210`** references `php artisan test --compact --filter=Feature`.
- **`../agentic-coding.md`** — check its Required Quality Gates and Per-Feature Validation Reference for PHPUnit
  invocations.

Commit as `docs: rewrite testing guide for pest` with footer `Refs: #1`.

---

## Verification

The acceptance criteria from issue #1, unchanged, plus the conversion-specific count gate:

```bash
php artisan test --compact                                              # 0 failures, 664 tests
vendor/bin/pest --list-tests | grep -c '^ - '                           # 664
vendor/bin/pint --dirty                                                 # clean
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/ms/siaran  # 200
curl -s -o /dev/null -w "%{http_code}" http://govportal.test/en/siaran  # 200
git diff --stat main -- .env config/app.php                             # empty
```

**On the 664 figure.** It is measured, not estimated: `vendor/bin/phpunit --list-tests | grep -c '^ - '` returns 664 on
`main` today, and the baseline run reports 15 failed + 649 passed = 664. Use `grep -c '^ - '` rather than `wc -l`, which
returns 667 because of the PHPUnit banner and the `Available tests:` header.

**Why consolidating into datasets does not break this gate.** Pest counts each dataset case as its own test. Collapsing
60 locale-paired methods into 30 dataset-driven tests still reports 60 cases. So if the count drops, a case was
genuinely lost — the gate stays meaningful throughout Phase 3 rather than becoming an obstacle to it.

---

## Risks And Rollback

| Risk                                                                                                       | Mitigation                                                                                                           |
|------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `composer update -W` upgrades Filament 5.2.2 → 5.8.4, Livewire 4.1.4 → 4.4.5 and ~60 other packages alongside Pest | The Phase 1 gate runs with **zero** tests converted, so any breakage is attributable to the upgrade. Fallback: land the ecosystem upgrade as a separate prior commit |
| `pest-plugin-livewire` relaxed to `^4.0`, which pins `livewire ^3.6.4` against this project's 4.1.4 | Pin `^4.1` exactly. `composer update --dry-run` will show a Livewire **downgrade** if this is wrong — check for it |
| `pest-plugin-laravel ^4.1` needs `laravel/framework ^12.52.0`; installed is exactly at that floor | Dry run proposes a Laravel downgrade. If so, add `laravel/framework:^12.52` to the require line |
| PHPUnit 11 → 12.5 arrives transitively with Pest 4 and shifts Filament v5 or Livewire v4 testing behaviour | Phase 1 gates on a green suite *before* any test is rewritten, isolating framework breakage from conversion breakage |
| `RefreshDatabase` applied suite-wide via `uses()` behaves differently from the current per-file trait      | Phase 1 gate catches it; `AiProviderValidatorTest` is the one file that never used it                                |
| `Prism::fake()` and the 12 `$this->mock()` sites misbehave under closure scope                             | Batch 2 is scoped to exactly those files, so a failure is localised                                                  |
| Test cases silently lost during conversion                                                                 | The 664 count gate runs after every batch, not just at the end                                                       |
| Parallel execution (`--parallel`) conflicts with `RefreshDatabase` on SQLite `:memory:`                    | Leave it off for this migration; evaluate separately once the suite is green on Pest                                 |

**Rollback** at any point:

```bash
git checkout main -- composer.json composer.lock tests/ phpunit.xml
composer install
php artisan test --compact
```

This is precisely why Phase 0 commits a green PHPUnit suite before the dependency swap — there is always a known-good
state one command away.

---

## Related Docs

- [../testing.md](../testing.md) — testing strategy; rewritten by Phase 5
- [../agentic-coding.md](../agentic-coding.md) — execution workflow and quality gates
- [../conversion-timeline.md](../conversion-timeline.md) — scope baseline; Week 14 is unblocked by this work
- [issue #1](https://github.com/aimeocakmal/open-govportal/issues/1) — the 15 locale failures
- [issue #8](https://github.com/aimeocakmal/open-govportal/issues/8) — Week 14 coverage above 80%, blocked by #1
- [Pest documentation](https://pestphp.com/docs/installation)
