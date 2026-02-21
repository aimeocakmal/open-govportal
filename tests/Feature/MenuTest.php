<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_menu_via_factory(): void
    {
        $menu = Menu::factory()->create();

        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
    }

    public function test_menu_has_items_relationship(): void
    {
        $menu = Menu::factory()->create();
        MenuItem::factory()->forMenu($menu)->count(3)->create();

        $this->assertCount(3, $menu->items);
    }

    public function test_menu_root_items_excludes_children(): void
    {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->forMenu($menu)->create();
        MenuItem::factory()->withParent($parent)->create();

        $this->assertCount(1, $menu->rootItems);
    }

    public function test_active_scope(): void
    {
        Menu::factory()->create(['is_active' => true]);
        Menu::factory()->inactive()->create();

        $this->assertCount(1, Menu::active()->get());
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $menu = Menu::factory()->create(['is_active' => 1]);

        $this->assertIsBool($menu->is_active);
    }
}
