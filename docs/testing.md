# Testing Guide

## Table of Contents

1. [Overview](#overview)
2. [Testing Pyramid](#testing-pyramid)
3. [PHPUnit Configuration](#phpunit-configuration)
4. [Test Directory Structure](#test-directory-structure)
5. [Model Factories](#model-factories)
6. [Unit Tests](#unit-tests)
7. [Feature Tests](#feature-tests)
8. [Livewire Component Tests](#livewire-component-tests)
9. [Database Testing](#database-testing)
10. [Mocking External Services](#mocking-external-services)
11. [Code Coverage](#code-coverage)
12. [CI/CD Pipeline](#cicd-pipeline)
13. [Per-Feature Test Reference](#per-feature-test-reference)

---

## Overview

OpenGovPortal uses PHPUnit (via Laravel's test runner) for all automated testing. Tests are organised into Unit and Feature layers. Browser (E2E) testing is deferred — the TALL stack's server-side rendering makes feature tests with `$this->get()` and Livewire's `Livewire::test()` sufficient for most coverage.

**Golden rule:** A task is not done until its test passes. Do not mark acceptance criteria as complete without running the commands and recording the output.

---

## Testing Pyramid

```
          [Browser / E2E]
         -----------------   <- Deferred (Playwright, Dusk)
              few tests

        [Feature Tests]
       -------------------   <- HTTP routes, Livewire, forms, cache, email
           most coverage

         [Unit Tests]
        -----------------    <- Model scopes, observers, helpers, transformers
           fast, isolated
```

**Target coverage thresholds:**

| Layer | Target | Tool |
|-------|--------|------|
| Unit | 90%+ | PHPUnit |
| Feature | 80%+ | PHPUnit + Livewire test helpers |
| Browser | Deferred | — |

---

## PHPUnit Configuration

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_KEY" value="base64:testkey0000000000000000000000000000000="/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="pgsql"/>
        <env name="DB_DATABASE" value="govportal_test"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="FILESYSTEM_DISK" value="local"/>
        <env name="AWS_BUCKET" value="test-bucket"/>
        <env name="OCTANE_SERVER" value="frankenphp"/>
    </php>
    <coverage>
        <report>
            <html outputDirectory="coverage-report"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>
</phpunit>
```

**Key test environment overrides:**

| Setting | Test Value | Reason |
|---------|-----------|--------|
| `CACHE_DRIVER` | `array` | Fast, in-memory; no Redis needed in tests |
| `MAIL_MAILER` | `array` | Captures emails without sending |
| `QUEUE_CONNECTION` | `sync` | Jobs run immediately; no worker needed |
| `FILESYSTEM_DISK` | `local` | No S3 calls in unit/feature tests |
| `BCRYPT_ROUNDS` | `4` | Faster password hashing |

---

## Test Directory Structure

```
tests/
├── TestCase.php                        <- Base class; sets up locale helper
├── Unit/
│   ├── Models/
│   │   ├── BroadcastTest.php           <- Scopes, casts, getters
│   │   ├── AchievementTest.php
│   │   ├── StaffDirectoryTest.php
│   │   ├── PolicyTest.php
│   │   └── FeedbackTest.php
│   ├── Observers/
│   │   └── BroadcastObserverTest.php   <- Cache tag invalidation
│   └── Helpers/
│       └── LocaleHelperTest.php
├── Feature/
│   ├── Http/
│   │   ├── HomeControllerTest.php
│   │   ├── BroadcastControllerTest.php
│   │   ├── AchievementControllerTest.php
│   │   ├── StatistikControllerTest.php
│   │   ├── DirectoriControllerTest.php
│   │   ├── DasarControllerTest.php
│   │   ├── ProfilKementerianControllerTest.php
│   │   ├── HubungiKamiControllerTest.php
│   │   └── StaticPageControllerTest.php
│   ├── Livewire/
│   │   ├── SiaranListTest.php
│   │   ├── PencapaianListTest.php
│   │   ├── DirectoriSearchTest.php
│   │   ├── ContactFormTest.php
│   │   └── SearchResultsTest.php
│   ├── Api/
│   │   ├── BroadcastApiTest.php
│   │   ├── AchievementApiTest.php
│   │   ├── DirectoryApiTest.php
│   │   └── FeedbackApiTest.php
│   └── Admin/
│       ├── BroadcastResourceTest.php
│       └── UserManagementTest.php
```

---

## Model Factories

Factories are defined in `database/factories/`. Use `HasFactory` on every model.

### BroadcastFactory

```php
<?php
// database/factories/BroadcastFactory.php

namespace Database\Factories;

use App\Models\Broadcast;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BroadcastFactory extends Factory
{
    protected $model = Broadcast::class;

    public function definition(): array
    {
        $titleMs = $this->faker->sentence(6);

        return [
            'title_ms'       => $titleMs,
            'title_en'       => $this->faker->sentence(6),
            'slug'           => Str::slug($titleMs) . '-' . $this->faker->unique()->randomNumber(4),
            'content_ms'     => $this->faker->paragraphs(3, true),
            'content_en'     => $this->faker->paragraphs(3, true),
            'excerpt_ms'     => $this->faker->sentence(15),
            'excerpt_en'     => $this->faker->sentence(15),
            'featured_image' => 'broadcasts/image-' . $this->faker->randomNumber(4) . '.jpg',
            'type'           => $this->faker->randomElement(['announcement', 'press_release', 'news']),
            'status'         => 'draft',
            'published_at'   => null,
            'created_by'     => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status'       => 'published',
            'published_at' => now()->subHours(rand(1, 720)),
        ]);
    }

    public function announcement(): static
    {
        return $this->state(['type' => 'announcement']);
    }

    public function pressRelease(): static
    {
        return $this->state(['type' => 'press_release']);
    }
}
```

### AchievementFactory

```php
<?php
// database/factories/AchievementFactory.php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        $titleMs = $this->faker->sentence(5);

        return [
            'title_ms'       => $titleMs,
            'title_en'       => $this->faker->sentence(5),
            'slug'           => Str::slug($titleMs) . '-' . $this->faker->unique()->randomNumber(4),
            'description_ms' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'date'           => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'icon'           => null,
            'is_featured'    => false,
            'status'         => 'published',
            'published_at'   => now()->subDays(rand(1, 365)),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
```

### StaffDirectoryFactory

```php
<?php
// database/factories/StaffDirectoryFactory.php

namespace Database\Factories;

use App\Models\StaffDirectory;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffDirectoryFactory extends Factory
{
    protected $model = StaffDirectory::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->name(),
            'position_ms'   => 'Pegawai Tadbir',
            'position_en'   => 'Administrative Officer',
            'department_ms' => 'Bahagian Teknologi Maklumat',
            'department_en' => 'Information Technology Division',
            'division_ms'   => null,
            'division_en'   => null,
            'email'         => $this->faker->safeEmail(),
            'phone'         => '03-' . $this->faker->numerify('########'),
            'fax'           => null,
            'photo'         => null,
            'sort_order'    => $this->faker->numberBetween(0, 100),
            'is_active'     => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
```

---

## Unit Tests

### Model Scope Test

```php
<?php
// tests/Unit/Models/BroadcastTest.php

namespace Tests\Unit\Models;

use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_scope_returns_only_published_rows(): void
    {
        Broadcast::factory()->published()->count(3)->create();
        Broadcast::factory()->count(2)->create(); // draft

        $results = Broadcast::published()->get();

        $this->assertCount(3, $results);
        $results->each(fn ($b) => $this->assertEquals('published', $b->status));
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Broadcast::factory()->state([
            'status'       => 'published',
            'published_at' => now()->addDay(), // future — not yet live
        ])->create();

        $this->assertCount(0, Broadcast::published()->get());
    }

    public function test_slug_is_unique(): void
    {
        Broadcast::factory()->published()->create(['slug' => 'my-slug']);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Broadcast::factory()->create(['slug' => 'my-slug']);
    }
}
```

### Observer Test

```php
<?php
// tests/Unit/Observers/BroadcastObserverTest.php

namespace Tests\Unit\Observers;

use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BroadcastObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_broadcast_flushes_broadcasts_and_homepage_tags(): void
    {
        Cache::tags(['broadcasts', 'homepage'])->put('test-key', 'value', 60);

        $broadcast = Broadcast::factory()->published()->create();
        $broadcast->title_ms = 'Updated';
        $broadcast->save();

        $this->assertNull(Cache::tags(['broadcasts'])->get('test-key'));
    }

    public function test_deleting_a_broadcast_flushes_broadcast_slug_tag(): void
    {
        $broadcast = Broadcast::factory()->published()->create(['slug' => 'my-broadcast']);
        Cache::tags(['broadcast:my-broadcast'])->put('detail', 'html', 60);

        $broadcast->delete();

        $this->assertNull(Cache::tags(['broadcast:my-broadcast'])->get('detail'));
    }
}
```

---

## Feature Tests

### HTTP Route Test

```php
<?php
// tests/Feature/Http/BroadcastControllerTest.php

namespace Tests\Feature\Http;

use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_page_returns_200_for_ms_locale(): void
    {
        Broadcast::factory()->published()->count(3)->create();

        $this->get('/ms/siaran')->assertStatus(200);
    }

    public function test_listing_page_returns_200_for_en_locale(): void
    {
        Broadcast::factory()->published()->count(3)->create();

        $this->get('/en/siaran')->assertStatus(200);
    }

    public function test_detail_page_returns_200_for_published_broadcast(): void
    {
        Broadcast::factory()->published()->create(['slug' => 'test-broadcast']);

        $this->get('/ms/siaran/test-broadcast')->assertStatus(200);
    }

    public function test_detail_page_returns_404_for_draft_broadcast(): void
    {
        Broadcast::factory()->create(['slug' => 'draft-broadcast']); // status=draft

        $this->get('/ms/siaran/draft-broadcast')->assertStatus(404);
    }

    public function test_listing_page_shows_ms_title_for_ms_locale(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Tajuk Berita',
            'title_en' => 'News Title',
        ]);

        $this->get('/ms/siaran')->assertSee('Tajuk Berita');
    }

    public function test_listing_page_shows_en_title_for_en_locale(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Tajuk Berita',
            'title_en' => 'News Title',
        ]);

        $this->get('/en/siaran')->assertSee('News Title');
    }

    public function test_unknown_locale_returns_404(): void
    {
        $this->get('/fr/siaran')->assertStatus(404);
    }
}
```

### Homepage Feature Test

```php
<?php
// tests/Feature/Http/HomeControllerTest.php

namespace Tests\Feature\Http;

use App\Models\Achievement;
use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200_for_both_locales(): void
    {
        $this->get('/ms')->assertStatus(200);
        $this->get('/en')->assertStatus(200);
    }

    public function test_homepage_shows_latest_six_broadcasts(): void
    {
        Broadcast::factory()->published()->count(8)->create();

        $response = $this->get('/ms');

        // The view receives exactly 6 broadcasts
        $response->assertViewHas('broadcasts', fn ($b) => $b->count() === 6);
    }

    public function test_homepage_shows_latest_seven_achievements(): void
    {
        Achievement::factory()->count(10)->create();

        $response = $this->get('/ms');

        $response->assertViewHas('achievements', fn ($a) => $a->count() === 7);
    }
}
```

---

## Livewire Component Tests

Use `Livewire::test()` for all Livewire component assertions.

```php
<?php
// tests/Feature/Livewire/DirectoriSearchTest.php

namespace Tests\Feature\Livewire;

use App\Livewire\DirectoriSearch;
use App\Models\StaffDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectoriSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        Livewire::test(DirectoriSearch::class)->assertStatus(200);
    }

    public function test_search_by_name_filters_results(): void
    {
        StaffDirectory::factory()->create(['name' => 'Ahmad Razif']);
        StaffDirectory::factory()->create(['name' => 'Siti Nurhaliza']);

        Livewire::test(DirectoriSearch::class)
            ->set('query', 'Ahmad')
            ->assertSee('Ahmad Razif')
            ->assertDontSee('Siti Nurhaliza');
    }

    public function test_department_filter_limits_results(): void
    {
        StaffDirectory::factory()->create([
            'name'          => 'Staff A',
            'department_ms' => 'Bahagian IT',
        ]);
        StaffDirectory::factory()->create([
            'name'          => 'Staff B',
            'department_ms' => 'Bahagian Kewangan',
        ]);

        Livewire::test(DirectoriSearch::class)
            ->set('jabatan', 'Bahagian IT')
            ->assertSee('Staff A')
            ->assertDontSee('Staff B');
    }

    public function test_empty_search_returns_all_active_staff(): void
    {
        StaffDirectory::factory()->count(5)->create();
        StaffDirectory::factory()->inactive()->count(2)->create();

        $component = Livewire::test(DirectoriSearch::class);

        // Only active staff appear
        $component->assertViewHas('staff', fn ($s) => $s->count() === 5);
    }
}
```

### ContactForm Test

```php
<?php
// tests/Feature/Livewire/ContactFormTest.php

namespace Tests\Feature\Livewire;

use App\Livewire\ContactForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_stores_feedback_and_sends_email(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set('name', 'Ahmad Abdullah')
            ->set('email', 'ahmad@example.com')
            ->set('subject', 'Pertanyaan Umum')
            ->set('message', 'Saya ingin bertanya mengenai perkhidmatan yang ditawarkan oleh kementerian.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('feedbacks', [
            'email'   => 'ahmad@example.com',
            'subject' => 'Pertanyaan Umum',
        ]);
    }

    public function test_empty_submission_returns_validation_errors(): void
    {
        Livewire::test(ContactForm::class)
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_message_shorter_than_20_chars_fails_validation(): void
    {
        Livewire::test(ContactForm::class)
            ->set('message', 'Too short')
            ->call('submit')
            ->assertHasErrors(['message']);
    }

    public function test_invalid_email_fails_validation(): void
    {
        Livewire::test(ContactForm::class)
            ->set('email', 'not-an-email')
            ->call('submit')
            ->assertHasErrors(['email']);
    }
}
```

---

## Database Testing

All feature tests that touch the database must use `RefreshDatabase`.

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase; // Wraps each test in a transaction, rolls back after
}
```

**N+1 detection in tests:**

```php
public function test_broadcast_listing_has_no_n_plus_one(): void
{
    Broadcast::factory()->published()->count(15)->create();

    DB::enableQueryLog();

    $this->get('/ms/siaran');

    $queryCount = count(DB::getQueryLog());

    // Listing should execute <= 3 queries regardless of count
    $this->assertLessThanOrEqual(3, $queryCount, "Expected <=3 queries, got {$queryCount}");
}
```

---

## Mocking External Services

### Mocking AWS S3

```php
use Illuminate\Support\Facades\Storage;

public function test_policy_download_redirects_to_signed_url(): void
{
    Storage::fake('s3');
    Storage::disk('s3')->put('policies/test.pdf', 'PDF content');

    $policy = Policy::factory()->create(['file_url' => 'policies/test.pdf']);

    $this->get("/ms/dasar/{$policy->id}/muat-turun")
         ->assertRedirect(); // Redirects to temporary URL
}
```

### Mocking SES (Email)

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\FeedbackReceived;

public function test_contact_form_dispatches_feedback_email(): void
{
    Mail::fake();

    // ... submit contact form via Livewire test ...

    Mail::assertQueued(FeedbackReceived::class, function ($mail) {
        return $mail->hasTo(config('mail.admin_address'));
    });
}
```

### Mocking Cache for Isolation

```php
use Illuminate\Support\Facades\Cache;

public function test_controller_uses_cached_broadcasts(): void
{
    $cached = Broadcast::factory()->published()->count(6)->make();

    Cache::shouldReceive('tags->remember')
         ->once()
         ->andReturn($cached);

    $this->get('/ms')->assertStatus(200);
}
```

---

## Code Coverage

```bash
# Generate HTML coverage report
php artisan test --coverage --min=80

# Generate Clover XML (for CI)
XDEBUG_MODE=coverage php artisan test --coverage-clover=coverage.xml

# View report in browser
open coverage-report/index.html
```

**Coverage requirements before merging:**

| Path | Minimum |
|------|---------|
| `app/Models/` | 90% |
| `app/Livewire/` | 85% |
| `app/Http/Controllers/` | 80% |
| `app/Observers/` | 80% |
| Overall | 80% |

---

## CI/CD Pipeline

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_USER: govportal
          POSTGRES_PASSWORD: secret
          POSTGRES_DB: govportal_test
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 5432:5432

      redis:
        image: redis:7
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_pgsql, redis, pcntl, zip, gd
          coverage: xdebug

      - name: Copy .env.testing
        run: cp .env.testing .env

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction --no-scripts

      - name: Generate app key
        run: php artisan key:generate

      - name: Run migrations
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: govportal_test
          DB_USERNAME: govportal
          DB_PASSWORD: secret
        run: php artisan migrate --force

      - name: Run Unit tests
        run: php artisan test --testsuite=Unit --stop-on-failure

      - name: Run Feature tests
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: govportal_test
          DB_USERNAME: govportal
          DB_PASSWORD: secret
        run: php artisan test --testsuite=Feature --stop-on-failure

      - name: Generate coverage report
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: govportal_test
          DB_USERNAME: govportal
          DB_PASSWORD: secret
        run: XDEBUG_MODE=coverage php artisan test --coverage --min=80 --coverage-clover=coverage.xml

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v4
        with:
          file: coverage.xml
```

---

## Per-Feature Test Reference

Use these commands to validate each feature slice before marking `Implemented` in [docs/pages-features.md](pages-features.md).

| Feature | Test command | Expected |
|---------|-------------|----------|
| Homepage | `php artisan test --filter=HomeControllerTest` | All pass |
| Siaran listing | `php artisan test --filter=BroadcastControllerTest` | All pass |
| Siaran Livewire | `php artisan test --filter=SiaranListTest` | All pass |
| Pencapaian | `php artisan test --filter=AchievementControllerTest` | All pass |
| Direktori search | `php artisan test --filter=DirectoriSearchTest` | All pass |
| Contact form | `php artisan test --filter=ContactFormTest` | All pass |
| Dasar download | `php artisan test --filter=DasarControllerTest` | All pass |
| API broadcasts | `php artisan test --filter=BroadcastApiTest` | All pass |
| Static pages | `php artisan test --filter=StaticPageControllerTest` | All pass |

**Run all tests:**

```bash
php artisan test
```

**Run with parallel workers:**

```bash
php artisan test --parallel
```

---

## References

- [Architecture](architecture.md)
- [Database Schema](database-schema.md)
- [Pages & Features](pages-features.md)
- [Agentic Coding Playbook](agentic-coding.md)
- [Laravel Testing Docs](https://laravel.com/docs/11.x/testing)
- [Livewire Testing Docs](https://livewire.laravel.com/docs/testing)
