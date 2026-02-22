<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\StaffDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
