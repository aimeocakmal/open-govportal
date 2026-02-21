<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceTest extends TestCase
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

    public function test_role_list_page_accessible_by_admin(): void
    {
        $this->actingAs($this->getAdmin())
            ->get('/admin/roles')
            ->assertOk();
    }

    public function test_role_list_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_role_edit_page_accessible_by_admin(): void
    {
        $role = Role::findByName('content_editor');

        $this->actingAs($this->getAdmin())
            ->get("/admin/roles/{$role->id}/edit")
            ->assertOk();
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $admin = $this->getAdmin();
        $superAdminRole = Role::findByName('super_admin');

        $this->assertFalse($admin->can('delete', $superAdminRole));
    }

    public function test_seeded_roles_exist(): void
    {
        $expectedRoles = [
            'super_admin', 'department_admin', 'content_editor',
            'content_author', 'publisher', 'viewer',
        ];

        foreach ($expectedRoles as $roleName) {
            $this->assertDatabaseHas('roles', ['name' => $roleName]);
        }
    }

    public function test_role_policy_requires_manage_roles_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->assertFalse($viewer->can('viewAny', Role::class));
        $this->assertFalse($viewer->can('create', Role::class));
    }

    public function test_admin_can_manage_roles(): void
    {
        $admin = $this->getAdmin();

        $this->assertTrue($admin->can('viewAny', Role::class));
        $this->assertTrue($admin->can('create', Role::class));
    }
}
