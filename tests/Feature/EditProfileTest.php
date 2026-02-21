<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_profile_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('content_editor');

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertOk();
    }

    public function test_profile_page_requires_authentication(): void
    {
        $this->get('/admin/profile')
            ->assertRedirect();
    }

    public function test_inactive_user_cannot_access_profile(): void
    {
        $user = User::factory()->inactive()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertForbidden();
    }
}
