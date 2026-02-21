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
}
