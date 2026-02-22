<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\HeroBanner;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_activity_log_list_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/admin/activity-logs');

        $response->assertOk();
    }

    public function test_activity_log_list_page_denied_for_viewer(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole('viewer');

        $response = $this->actingAs($viewer)->get('/admin/activity-logs');

        $response->assertForbidden();
    }

    public function test_activity_log_shows_create_event(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'Test Banner',
            'is_active' => true,
        ]);

        $activity = Activity::where('subject_type', HeroBanner::class)
            ->where('subject_id', $banner->id)
            ->where('description', 'created')
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_activity_log_shows_update_with_changes(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'Original Title',
            'is_active' => true,
        ]);

        $banner->update(['title_ms' => 'Updated Title']);

        $activity = Activity::where('subject_type', HeroBanner::class)
            ->where('subject_id', $banner->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('Original Title', $activity->properties['old']['title_ms']);
        $this->assertEquals('Updated Title', $activity->properties['attributes']['title_ms']);
    }

    public function test_activity_log_shows_delete_event(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'To Delete',
            'is_active' => true,
        ]);

        $bannerId = $banner->id;
        $banner->delete();

        $activity = Activity::where('subject_type', HeroBanner::class)
            ->where('subject_id', $bannerId)
            ->where('description', 'deleted')
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_user_changes_logged_without_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'is_active' => true,
        ]);

        $user->update([
            'name' => 'New Name',
            'password' => bcrypt('newpassword'),
        ]);

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('name', $activity->properties['attributes']);
        $this->assertArrayNotHasKey('password', $activity->properties['attributes']);
    }

    public function test_module_name_mapping_returns_friendly_names(): void
    {
        $this->assertNotEquals(
            'Unknown',
            ActivityLogResource::getModuleName('App\Models\Broadcast'),
        );
        $this->assertNotEquals(
            'Unknown',
            ActivityLogResource::getModuleName('App\Models\User'),
        );
        $this->assertNotEquals(
            'Unknown',
            ActivityLogResource::getModuleName('App\Models\HeroBanner'),
        );
    }

    public function test_module_name_for_unknown_class_returns_basename(): void
    {
        $result = ActivityLogResource::getModuleName('App\Models\SomethingNotMapped');

        $this->assertEquals('SomethingNotMapped', $result);
    }
}
