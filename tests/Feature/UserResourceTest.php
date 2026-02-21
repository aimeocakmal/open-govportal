<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
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

    public function test_user_list_page_accessible_by_admin(): void
    {
        $this->actingAs($this->getAdmin())
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_user_list_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_user_create_page_accessible_by_admin(): void
    {
        $this->actingAs($this->getAdmin())
            ->get('/admin/users/create')
            ->assertOk();
    }

    public function test_user_edit_page_accessible_by_admin(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->getAdmin())
            ->get("/admin/users/{$target->id}/edit")
            ->assertOk();
    }

    public function test_user_create_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->get('/admin/users/create')
            ->assertForbidden();
    }

    public function test_user_edit_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        $target = User::factory()->create();

        $this->actingAs($viewer)
            ->get("/admin/users/{$target->id}/edit")
            ->assertForbidden();
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
        $this->assertTrue($user->is_active);
        $this->assertEquals('ms', $user->preferred_locale);
    }

    public function test_inactive_user_cannot_access_admin(): void
    {
        $user = User::factory()->inactive()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }
}
