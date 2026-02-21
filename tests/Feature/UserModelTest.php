<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_via_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_factory_sets_default_values(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
        $this->assertEquals('ms', $user->preferred_locale);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_inactive_factory_state(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->is_active);
    }

    public function test_with_department_factory_state(): void
    {
        $user = User::factory()->withDepartment('Bahagian Teknologi')->create();

        $this->assertEquals('Bahagian Teknologi', $user->department);
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    public function test_last_login_at_cast_to_datetime(): void
    {
        $user = User::factory()->create(['last_login_at' => now()]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret123', $user->password));
    }

    public function test_active_user_can_access_panel(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $panel = app(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::factory()->inactive()->create();
        $panel = app(Panel::class);

        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_user_can_have_roles(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('content_editor');

        $this->assertTrue($user->hasRole('content_editor'));
    }

    public function test_preferred_locale_defaults_to_ms(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('ms', $user->preferred_locale);
    }
}
