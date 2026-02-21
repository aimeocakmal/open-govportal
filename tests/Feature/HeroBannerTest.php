<?php

namespace Tests\Feature;

use App\Models\HeroBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_hero_banner_via_factory(): void
    {
        $banner = HeroBanner::factory()->create();

        $this->assertDatabaseHas('hero_banners', [
            'id' => $banner->id,
        ]);
    }

    public function test_active_scope_returns_only_active_banners(): void
    {
        HeroBanner::factory()->create(['is_active' => true, 'sort_order' => 2]);
        HeroBanner::factory()->create(['is_active' => false, 'sort_order' => 1]);
        HeroBanner::factory()->create(['is_active' => true, 'sort_order' => 3]);

        $active = HeroBanner::active()->get();

        $this->assertCount(2, $active);
        $active->each(fn (HeroBanner $b) => $this->assertTrue($b->is_active));
    }

    public function test_active_scope_orders_by_sort_order(): void
    {
        HeroBanner::factory()->create(['is_active' => true, 'sort_order' => 3]);
        HeroBanner::factory()->create(['is_active' => true, 'sort_order' => 1]);
        HeroBanner::factory()->create(['is_active' => true, 'sort_order' => 2]);

        $active = HeroBanner::active()->get();

        $this->assertEquals([1, 2, 3], $active->pluck('sort_order')->toArray());
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $banner = HeroBanner::factory()->create();

        $this->assertIsBool($banner->is_active);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $banner = HeroBanner::factory()->create();

        $this->assertIsInt($banner->sort_order);
    }

    public function test_inactive_factory_state(): void
    {
        $banner = HeroBanner::factory()->inactive()->create();

        $this->assertFalse($banner->is_active);
    }

    public function test_bilingual_fields_are_stored(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'Tajuk BM',
            'title_en' => 'Title EN',
            'subtitle_ms' => 'Subtajuk BM',
            'subtitle_en' => 'Subtitle EN',
        ]);

        $this->assertEquals('Tajuk BM', $banner->title_ms);
        $this->assertEquals('Title EN', $banner->title_en);
        $this->assertEquals('Subtajuk BM', $banner->subtitle_ms);
        $this->assertEquals('Subtitle EN', $banner->subtitle_en);
    }
}
