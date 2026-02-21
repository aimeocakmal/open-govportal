<?php

namespace Tests\Feature;

use App\Models\SearchOverride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_search_override_via_factory(): void
    {
        $override = SearchOverride::factory()->create();

        $this->assertDatabaseHas('search_overrides', [
            'id' => $override->id,
            'query' => $override->query,
        ]);
    }

    public function test_active_scope_returns_only_active_records(): void
    {
        SearchOverride::factory()->create(['is_active' => true, 'priority' => 10]);
        SearchOverride::factory()->create(['is_active' => true, 'priority' => 50]);
        SearchOverride::factory()->inactive()->create();

        $active = SearchOverride::active()->get();

        $this->assertCount(2, $active);
        $this->assertEquals(50, $active->first()->priority);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $override = SearchOverride::factory()->create(['is_active' => true]);

        $this->assertIsBool($override->is_active);
        $this->assertTrue($override->is_active);
    }

    public function test_priority_is_cast_to_integer(): void
    {
        $override = SearchOverride::factory()->create(['priority' => 100]);

        $this->assertIsInt($override->priority);
        $this->assertEquals(100, $override->priority);
    }

    public function test_inactive_factory_state(): void
    {
        $override = SearchOverride::factory()->inactive()->create();

        $this->assertFalse($override->is_active);
    }

    public function test_bilingual_fields_are_stored(): void
    {
        $override = SearchOverride::factory()->create([
            'title_ms' => 'Carian Ujian',
            'title_en' => 'Test Search',
            'description_ms' => 'Keterangan ujian',
            'description_en' => 'Test description',
        ]);

        $this->assertDatabaseHas('search_overrides', [
            'id' => $override->id,
            'title_ms' => 'Carian Ujian',
            'title_en' => 'Test Search',
        ]);
    }
}
