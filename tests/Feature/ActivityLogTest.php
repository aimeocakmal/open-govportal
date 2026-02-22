<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\HeroBanner;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\StaffDirectory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_broadcast_create_is_logged(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Logged Broadcast',
            'status' => 'draft',
            'slug' => 'logged',
        ]);

        $activity = Activity::where('subject_type', Broadcast::class)
            ->where('subject_id', $broadcast->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->description);
    }

    public function test_broadcast_update_logs_changed_fields(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Original',
            'status' => 'draft',
            'slug' => 'update-log',
        ]);

        $broadcast->update(['status' => 'published']);

        $activity = Activity::where('subject_type', Broadcast::class)
            ->where('subject_id', $broadcast->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('status', $activity->properties['attributes'] ?? []);
    }

    public function test_staff_directory_changes_are_logged(): void
    {
        $staff = StaffDirectory::factory()->create([
            'name' => 'Ahmad',
            'is_active' => true,
        ]);

        $staff->update(['name' => 'Ahmad bin Ali']);

        $activity = Activity::where('subject_type', StaffDirectory::class)
            ->where('subject_id', $staff->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_hero_banner_create_is_logged(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'New Banner',
            'is_active' => true,
        ]);

        $activity = Activity::where('subject_type', HeroBanner::class)
            ->where('subject_id', $banner->id)
            ->where('description', 'created')
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_menu_item_update_is_logged(): void
    {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label_ms' => 'Original',
            'is_active' => true,
        ]);

        $item->update(['label_ms' => 'Updated Label']);

        $activity = Activity::where('subject_type', MenuItem::class)
            ->where('subject_id', $item->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_user_update_logs_safe_fields_only(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'is_active' => true,
        ]);

        $user->update([
            'name' => 'Updated User',
            'password' => bcrypt('secret123'),
        ]);

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('description', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('name', $activity->properties['attributes']);
        $this->assertArrayNotHasKey('password', $activity->properties['attributes']);
    }

    public function test_login_activity_is_logged(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        Auth::login($user);

        $activity = Activity::where('log_name', 'auth')
            ->where('description', 'logged_in')
            ->where('causer_id', $user->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($user->id, $activity->causer_id);
    }

    public function test_activity_captures_causer_when_authenticated(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Causer Test',
            'status' => 'draft',
            'slug' => 'causer-test',
        ]);

        $activity = Activity::where('subject_type', Broadcast::class)
            ->where('subject_id', $broadcast->id)
            ->where('description', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($user->id, $activity->causer_id);
        $this->assertEquals(User::class, $activity->causer_type);
    }
}
