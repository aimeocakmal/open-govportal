<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function getAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_menu_list_page_accessible_by_admin(): void
    {
        $this->actingAs($this->getAdmin())
            ->get('/admin/menus')
            ->assertOk();
    }

    public function test_menu_list_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->get('/admin/menus')
            ->assertForbidden();
    }

    public function test_menu_edit_page_accessible_by_admin(): void
    {
        $menu = Menu::factory()->create();

        $this->actingAs($this->getAdmin())
            ->get("/admin/menus/{$menu->id}/edit")
            ->assertOk();
    }

    public function test_menu_cannot_be_created(): void
    {
        $admin = $this->getAdmin();

        $this->assertFalse($admin->can('create', Menu::class));
    }

    public function test_menu_cannot_be_deleted(): void
    {
        $admin = $this->getAdmin();
        $menu = Menu::factory()->create();

        $this->assertFalse($admin->can('delete', $menu));
    }
}
