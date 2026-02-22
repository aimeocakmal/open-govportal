<?php

namespace Tests\Feature;

use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    // ── Theme discovery ──────────────────────────────────────────────

    public function test_discover_finds_default_theme(): void
    {
        $service = app(ThemeService::class);
        $themes = $service->discover();

        $this->assertArrayHasKey('default', $themes);
        $this->assertEquals('default', $themes['default']['name']);
    }

    public function test_discover_returns_manifest_fields(): void
    {
        $service = app(ThemeService::class);
        $themes = $service->discover();

        $manifest = $themes['default'];
        $this->assertArrayHasKey('label', $manifest);
        $this->assertArrayHasKey('version', $manifest);
        $this->assertArrayHasKey('css', $manifest);
        $this->assertArrayHasKey('js', $manifest);
    }

    public function test_discover_caches_results(): void
    {
        $service = app(ThemeService::class);
        $service->discover();

        $this->assertTrue(Cache::has('themes:discovered'));
    }

    // ── Active theme management ──────────────────────────────────────

    public function test_default_active_theme_is_default(): void
    {
        $service = app(ThemeService::class);

        $this->assertEquals('default', $service->getActive());
    }

    public function test_set_active_validates_against_discovered_themes(): void
    {
        $service = app(ThemeService::class);
        $service->setActive('nonexistent-theme');

        $this->assertEquals('default', $service->getActive());
    }

    public function test_set_active_accepts_valid_theme(): void
    {
        $service = app(ThemeService::class);
        $service->setActive('default');

        $this->assertEquals('default', $service->getActive());
    }

    // ── View paths ───────────────────────────────────────────────────

    public function test_get_views_path_returns_correct_path(): void
    {
        $service = app(ThemeService::class);
        $expected = resource_path('themes/default/views');

        $this->assertEquals($expected, $service->getViewsPath('default'));
    }

    // ── Vite entries ─────────────────────────────────────────────────

    public function test_get_vite_entries_returns_css_and_js(): void
    {
        $service = app(ThemeService::class);
        $entries = $service->getViteEntries('default');

        $this->assertArrayHasKey('css', $entries);
        $this->assertArrayHasKey('js', $entries);
        $this->assertEquals('resources/themes/default/css/app.css', $entries['css']);
        $this->assertEquals('resources/themes/default/js/app.js', $entries['js']);
    }

    public function test_get_vite_entries_falls_back_for_unknown_theme(): void
    {
        $service = app(ThemeService::class);
        $entries = $service->getViteEntries('nonexistent');

        $this->assertStringContainsString('resources/themes/', $entries['css']);
        $this->assertStringContainsString('resources/themes/', $entries['js']);
    }

    // ── Theme options ────────────────────────────────────────────────

    public function test_get_theme_options_returns_ms_labels(): void
    {
        $service = app(ThemeService::class);
        $options = $service->getThemeOptions('ms');

        $this->assertArrayHasKey('default', $options);
        $this->assertEquals('GovPortal 1.0 - Tema Standard', $options['default']);
    }

    public function test_get_theme_options_returns_en_labels(): void
    {
        $service = app(ThemeService::class);
        $options = $service->getThemeOptions('en');

        $this->assertArrayHasKey('default', $options);
        $this->assertEquals('GovPortal 1.0 - Standard Theme', $options['default']);
    }

    // ── HTTP integration ─────────────────────────────────────────────

    public function test_homepage_renders_with_themed_views(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
    }

    public function test_homepage_renders_in_en_with_themed_views(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
    }

    public function test_default_theme_data_attribute_is_present(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('data-theme="default"', false);
    }

    public function test_invalid_theme_cookie_falls_back_to_default(): void
    {
        $response = $this->withCookie('govportal_theme', 'nonexistent-theme')
            ->get('/ms');

        $response->assertOk();
        $response->assertSee('data-theme="default"', false);
    }

    public function test_valid_theme_cookie_is_respected(): void
    {
        // Only 'default' theme exists, so setting it should work
        $response = $this->withCookie('govportal_theme', 'default')
            ->get('/ms');

        $response->assertOk();
        $response->assertSee('data-theme="default"', false);
    }

    // ── Scoped singleton (Octane safety) ─────────────────────────────

    public function test_theme_service_is_scoped_singleton(): void
    {
        $instance1 = app(ThemeService::class);
        $instance2 = app(ThemeService::class);

        $this->assertSame($instance1, $instance2);
    }
}
