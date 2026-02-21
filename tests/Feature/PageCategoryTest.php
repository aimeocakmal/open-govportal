<?php

namespace Tests\Feature;

use App\Models\PageCategory;
use App\Models\StaticPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_page_category_via_factory(): void
    {
        $category = PageCategory::factory()->create();

        $this->assertDatabaseHas('page_categories', ['id' => $category->id]);
    }

    public function test_parent_child_relationship(): void
    {
        $parent = PageCategory::factory()->create();
        $child = PageCategory::factory()->withParent($parent)->create();

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertCount(1, $parent->children);
    }

    public function test_static_pages_relationship(): void
    {
        $category = PageCategory::factory()->create();
        StaticPage::factory()->create(['category_id' => $category->id]);

        $this->assertCount(1, $category->staticPages);
    }

    public function test_active_scope(): void
    {
        PageCategory::factory()->create(['is_active' => true]);
        PageCategory::factory()->inactive()->create();

        $this->assertCount(1, PageCategory::active()->get());
    }

    public function test_root_scope(): void
    {
        $parent = PageCategory::factory()->create();
        PageCategory::factory()->withParent($parent)->create();

        $this->assertCount(1, PageCategory::root()->get());
    }

    public function test_null_on_delete_parent(): void
    {
        $parent = PageCategory::factory()->create();
        $child = PageCategory::factory()->withParent($parent)->create();

        $parent->delete();

        $this->assertNull($child->fresh()->parent_id);
    }
}
