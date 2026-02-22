<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\StaticPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_homepage(): void
    {
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        $this->assertStringContainsString(url('/ms/'), $content);
        $this->assertStringContainsString(url('/en/'), $content);
    }

    public function test_sitemap_contains_static_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee(url('/ms/siaran'));
        $response->assertSee(url('/ms/dasar'));
        $response->assertSee(url('/ms/direktori'));
    }

    public function test_sitemap_contains_broadcast_slugs(): void
    {
        $broadcast = Broadcast::factory()->published()->create([
            'slug' => 'test-broadcast',
        ]);

        $this->get('/sitemap.xml')
            ->assertSee(url('/ms/siaran/test-broadcast'))
            ->assertSee(url('/en/siaran/test-broadcast'));
    }

    public function test_sitemap_contains_static_pages_in_sitemap(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'penafian',
            'is_in_sitemap' => true,
        ]);

        $this->get('/sitemap.xml')
            ->assertSee(url('/ms/penafian'));
    }

    public function test_sitemap_excludes_pages_not_in_sitemap(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'hidden-page',
            'is_in_sitemap' => false,
        ]);

        $this->get('/sitemap.xml')
            ->assertDontSee('hidden-page');
    }

    public function test_sitemap_valid_xml_structure(): void
    {
        $response = $this->get('/sitemap.xml');

        $this->assertStringContainsString('<?xml version="1.0"', $response->getContent());
        $this->assertStringContainsString('<urlset', $response->getContent());
        $this->assertStringContainsString('</urlset>', $response->getContent());
    }
}
