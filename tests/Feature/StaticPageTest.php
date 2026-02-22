<?php

namespace Tests\Feature;

use App\Models\PageCategory;
use App\Models\StaticPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_static_page_via_factory(): void
    {
        $page = StaticPage::factory()->create();

        $this->assertDatabaseHas('static_pages', ['id' => $page->id]);
    }

    public function test_published_scope(): void
    {
        StaticPage::factory()->create(['status' => 'draft']);
        StaticPage::factory()->published()->create();
        StaticPage::factory()->published()->create();

        $this->assertCount(2, StaticPage::published()->get());
    }

    public function test_category_relationship(): void
    {
        $category = PageCategory::factory()->create();
        $page = StaticPage::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $page->category->id);
    }

    public function test_is_in_sitemap_cast(): void
    {
        $page = StaticPage::factory()->create(['is_in_sitemap' => 1]);

        $this->assertIsBool($page->is_in_sitemap);
        $this->assertTrue($page->is_in_sitemap);
    }

    public function test_null_on_delete_category(): void
    {
        $category = PageCategory::factory()->create();
        $page = StaticPage::factory()->create(['category_id' => $category->id]);

        $category->delete();

        $this->assertNull($page->fresh()->category_id);
    }

    public function test_penafian_route_returns_200_ms(): void
    {
        StaticPage::factory()->published()->create(['slug' => 'penafian']);

        $this->get('/ms/penafian')->assertOk();
    }

    public function test_penafian_route_returns_200_en(): void
    {
        StaticPage::factory()->published()->create(['slug' => 'penafian']);

        $this->get('/en/penafian')->assertOk();
    }

    public function test_dasar_privasi_route_returns_200_ms(): void
    {
        StaticPage::factory()->published()->create(['slug' => 'dasar-privasi']);

        $this->get('/ms/dasar-privasi')->assertOk();
    }

    public function test_dasar_privasi_route_returns_200_en(): void
    {
        StaticPage::factory()->published()->create(['slug' => 'dasar-privasi']);

        $this->get('/en/dasar-privasi')->assertOk();
    }

    public function test_penafian_page_shows_content_ms(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'penafian',
            'title_ms' => 'Penafian',
            'content_ms' => '<p>Kandungan penafian di sini.</p>',
        ]);

        $this->get('/ms/penafian')
            ->assertSee('Penafian')
            ->assertSee('Kandungan penafian di sini.');
    }

    public function test_dasar_privasi_page_shows_content_en(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'dasar-privasi',
            'title_en' => 'Privacy Policy',
            'content_en' => '<p>Privacy policy content here.</p>',
        ]);

        $this->get('/en/dasar-privasi')
            ->assertSee('Privacy Policy')
            ->assertSee('Privacy policy content here.');
    }

    public function test_static_page_404_when_not_published(): void
    {
        StaticPage::factory()->create([
            'slug' => 'penafian',
            'status' => 'draft',
        ]);

        $this->get('/ms/penafian')->assertNotFound();
    }

    public function test_static_page_has_breadcrumb(): void
    {
        StaticPage::factory()->published()->create(['slug' => 'penafian', 'title_ms' => 'Penafian']);

        $this->get('/ms/penafian')->assertSee('Laman Utama');
    }

    public function test_static_page_has_seo_meta(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'penafian',
            'meta_title_ms' => 'Penafian KKD',
            'meta_desc_ms' => 'Halaman penafian rasmi.',
        ]);

        $this->get('/ms/penafian')
            ->assertSee('Penafian KKD')
            ->assertSee('Halaman penafian rasmi.');
    }

    public function test_generic_static_page_slug_route_returns_200_and_renders_content(): void
    {
        StaticPage::factory()->published()->create([
            'slug' => 'faq-digital',
            'title_ms' => 'Soalan Lazim Digital',
            'content_ms' => '<p>Jawapan soalan lazim dalam Bahasa Malaysia.</p>',
            'title_en' => 'Digital FAQ',
            'content_en' => '<p>Frequently asked questions in English.</p>',
        ]);

        $this->get('/ms/faq-digital')
            ->assertOk()
            ->assertSee('Soalan Lazim Digital')
            ->assertSee('Jawapan soalan lazim dalam Bahasa Malaysia.');

        $this->get('/en/faq-digital')
            ->assertOk()
            ->assertSee('Digital FAQ')
            ->assertSee('Frequently asked questions in English.');
    }
}
