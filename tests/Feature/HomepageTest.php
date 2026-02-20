<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_root_redirects_to_locale(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertMatchesRegularExpression('#/(ms|en)$#', $location);
    }

    public function test_homepage_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Kementerian Digital Malaysia');
    }

    public function test_homepage_returns_ok_in_en(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('Ministry of Digital Malaysia');
    }

    public function test_invalid_locale_returns_404(): void
    {
        $response = $this->get('/fr');

        // /fr does not match the locale route pattern (ms|en), so no route found
        $response->assertNotFound();
    }

    public function test_default_theme_is_applied_to_html_element(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('data-theme="default"', false);
    }
}
