<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_menu_item_via_factory(): void
    {
        $item = MenuItem::factory()->create();

        $this->assertDatabaseHas('menu_items', ['id' => $item->id]);
    }

    public function test_menu_item_belongs_to_menu(): void
    {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->forMenu($menu)->create();

        $this->assertEquals($menu->id, $item->menu->id);
    }

    public function test_menu_item_has_parent_relationship(): void
    {
        $parent = MenuItem::factory()->create();
        $child = MenuItem::factory()->withParent($parent)->create();

        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_menu_item_has_children_relationship(): void
    {
        $parent = MenuItem::factory()->create();
        MenuItem::factory()->withParent($parent)->count(2)->create();

        $this->assertCount(2, $parent->children);
    }

    public function test_route_params_cast_to_json(): void
    {
        $item = MenuItem::factory()->create(['route_params' => ['locale' => 'ms']]);

        $this->assertIsArray($item->route_params);
        $this->assertEquals('ms', $item->route_params['locale']);
    }

    public function test_required_roles_cast_to_json(): void
    {
        $item = MenuItem::factory()->create(['required_roles' => ['super_admin', 'publisher']]);

        $this->assertIsArray($item->required_roles);
        $this->assertContains('super_admin', $item->required_roles);
    }

    public function test_active_scope(): void
    {
        $menu = Menu::factory()->create();
        MenuItem::factory()->forMenu($menu)->create(['is_active' => true]);
        MenuItem::factory()->forMenu($menu)->inactive()->create();

        $this->assertCount(1, MenuItem::active()->get());
    }

    public function test_cascade_delete_with_menu(): void
    {
        $menu = Menu::factory()->create();
        MenuItem::factory()->forMenu($menu)->count(3)->create();

        $menu->delete();

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_cascade_delete_parent_removes_children(): void
    {
        $parent = MenuItem::factory()->create();
        MenuItem::factory()->withParent($parent)->count(2)->create();

        $parent->delete();

        $this->assertDatabaseCount('menu_items', 0);
    }
}
