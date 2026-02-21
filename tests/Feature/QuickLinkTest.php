<?php

namespace Tests\Feature;

use App\Models\QuickLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_quick_link_via_factory(): void
    {
        $link = QuickLink::factory()->create();

        $this->assertDatabaseHas('quick_links', [
            'id' => $link->id,
            'url' => $link->url,
        ]);
    }

    public function test_active_scope_returns_only_active_links(): void
    {
        QuickLink::factory()->create(['is_active' => true, 'sort_order' => 2]);
        QuickLink::factory()->create(['is_active' => false, 'sort_order' => 1]);
        QuickLink::factory()->create(['is_active' => true, 'sort_order' => 3]);

        $active = QuickLink::active()->get();

        $this->assertCount(2, $active);
        $active->each(fn (QuickLink $l) => $this->assertTrue($l->is_active));
    }

    public function test_active_scope_orders_by_sort_order(): void
    {
        QuickLink::factory()->create(['is_active' => true, 'sort_order' => 3]);
        QuickLink::factory()->create(['is_active' => true, 'sort_order' => 1]);
        QuickLink::factory()->create(['is_active' => true, 'sort_order' => 2]);

        $active = QuickLink::active()->get();

        $this->assertEquals([1, 2, 3], $active->pluck('sort_order')->toArray());
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $link = QuickLink::factory()->create();

        $this->assertIsBool($link->is_active);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $link = QuickLink::factory()->create();

        $this->assertIsInt($link->sort_order);
    }

    public function test_inactive_factory_state(): void
    {
        $link = QuickLink::factory()->inactive()->create();

        $this->assertFalse($link->is_active);
    }

    public function test_bilingual_labels_are_stored(): void
    {
        $link = QuickLink::factory()->create([
            'label_ms' => 'Pautan Pantas',
            'label_en' => 'Quick Link',
        ]);

        $this->assertEquals('Pautan Pantas', $link->label_ms);
        $this->assertEquals('Quick Link', $link->label_en);
    }
}
