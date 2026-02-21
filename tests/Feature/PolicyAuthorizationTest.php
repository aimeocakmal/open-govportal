<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\Celebration;
use App\Models\Feedback;
use App\Models\HeroBanner;
use App\Models\Media;
use App\Models\Policy;
use App\Models\PolicyFile;
use App\Models\QuickLink;
use App\Models\SearchOverride;
use App\Models\StaffDirectory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_view_any_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->can('viewAny', Broadcast::class));
    }

    public function test_content_editor_can_create_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('content_editor');

        $this->assertTrue($user->can('create', Broadcast::class));
    }

    public function test_viewer_cannot_create_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $this->assertFalse($user->can('create', Broadcast::class));
    }

    public function test_viewer_can_view_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $this->assertTrue($user->can('viewAny', Broadcast::class));
    }

    public function test_content_editor_cannot_delete_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('content_editor');

        $broadcast = Broadcast::factory()->create();

        $this->assertFalse($user->can('delete', $broadcast));
    }

    public function test_super_admin_can_delete_broadcasts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $broadcast = Broadcast::factory()->create();

        $this->assertTrue($user->can('delete', $broadcast));
    }

    #[DataProvider('contentModelProvider')]
    public function test_super_admin_has_full_access_to_content_models(string $modelClass): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->can('viewAny', $modelClass));
        $this->assertTrue($user->can('create', $modelClass));

        $record = $modelClass::factory()->create();
        $this->assertTrue($user->can('update', $record));
        $this->assertTrue($user->can('delete', $record));
    }

    #[DataProvider('contentModelProvider')]
    public function test_viewer_can_only_view_content_models(string $modelClass): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $this->assertTrue($user->can('viewAny', $modelClass));
        $this->assertFalse($user->can('create', $modelClass));

        $record = $modelClass::factory()->create();
        $this->assertFalse($user->can('update', $record));
        $this->assertFalse($user->can('delete', $record));
    }

    public static function contentModelProvider(): array
    {
        return [
            'Broadcast' => [Broadcast::class],
            'Achievement' => [Achievement::class],
            'Celebration' => [Celebration::class],
            'Policy' => [Policy::class],
            'StaffDirectory' => [StaffDirectory::class],
            'PolicyFile' => [PolicyFile::class],
            'HeroBanner' => [HeroBanner::class],
            'QuickLink' => [QuickLink::class],
            'Media' => [Media::class],
            'Feedback' => [Feedback::class],
            'SearchOverride' => [SearchOverride::class],
        ];
    }

    public function test_manage_users_permission_required_for_user_policy(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertTrue($admin->can('create', User::class));

        $this->assertFalse($viewer->can('viewAny', User::class));
        $this->assertFalse($viewer->can('create', User::class));
    }

    public function test_user_cannot_delete_self(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertFalse($admin->can('delete', $admin));
    }

    public function test_non_super_admin_cannot_delete_super_admin(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('content_editor');
        $editor->givePermissionTo('manage_users');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertFalse($editor->can('delete', $superAdmin));
    }

    public function test_super_admin_can_delete_other_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $target = User::factory()->create();
        $target->assignRole('content_editor');

        $this->assertTrue($admin->can('delete', $target));
    }

    public function test_settings_page_requires_manage_settings_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->assertTrue($admin->can('manage_settings'));
        $this->assertFalse($viewer->can('manage_settings'));
    }
}
