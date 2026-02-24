<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PurgeOldActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_archives_and_purges_old_activity_logs(): void
    {
        // Old log (400 days ago)
        $old = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => 'App\\Models\\Broadcast',
            'subject_id' => 1,
            'properties' => ['attributes' => ['title' => 'Test']],
        ]);
        Activity::query()->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);

        // Recent log
        $recent = Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
            'subject_type' => 'App\\Models\\Broadcast',
            'subject_id' => 1,
        ]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $this->assertDatabaseMissing('activity_log', ['id' => $old->id]);
        $this->assertDatabaseHas('activity_log', ['id' => $recent->id]);

        // Verify archive ZIP was created
        $files = Storage::disk('public')->allFiles('archives/activity-log-purge');
        $this->assertCount(1, $files);
        $this->assertStringEndsWith('.zip', $files[0]);
    }

    public function test_archive_contains_csv_with_data(): void
    {
        $old = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => 'App\\Models\\Broadcast',
            'subject_id' => 42,
            'properties' => ['attributes' => ['title' => 'Archive Test']],
        ]);
        Activity::query()->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $files = Storage::disk('public')->allFiles('archives/activity-log-purge');
        $this->assertCount(1, $files);

        $zipContent = Storage::disk('public')->get($files[0]);
        $tempZip = tempnam(sys_get_temp_dir(), 'purge_test_');
        file_put_contents($tempZip, $zipContent);

        $zip = new \ZipArchive;
        $zip->open($tempZip);

        $csvNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $csvNames[] = $zip->getNameIndex($i);
        }

        $this->assertContains('activity_log.csv', $csvNames);

        $csv = $zip->getFromName('activity_log.csv');
        $lines = array_filter(explode("\n", trim($csv)));
        $this->assertCount(2, $lines); // header + 1 data row
        $this->assertStringContainsString('Archive Test', $csv);

        $zip->close();
        unlink($tempZip);
    }

    public function test_respects_custom_retention_setting(): void
    {
        Setting::set('activity_log_retention_days', '60');

        // 70 days old — should be purged
        $old = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
        ]);
        Activity::query()->where('id', $old->id)->update(['created_at' => now()->subDays(70)]);

        // 30 days old — should be kept
        $recent = Activity::create([
            'log_name' => 'default',
            'description' => 'updated',
        ]);
        Activity::query()->where('id', $recent->id)->update(['created_at' => now()->subDays(30)]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $this->assertDatabaseCount('activity_log', 1);
        $this->assertDatabaseHas('activity_log', ['id' => $recent->id]);
    }

    public function test_minimum_retention_is_thirty_days(): void
    {
        Setting::set('activity_log_retention_days', '5');

        // 25 days old — should NOT be purged (min retention is 30)
        $log = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
        ]);
        Activity::query()->where('id', $log->id)->update(['created_at' => now()->subDays(25)]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $this->assertDatabaseCount('activity_log', 1);
    }

    public function test_dry_run_does_not_delete_or_archive(): void
    {
        $old = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
        ]);
        Activity::query()->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);

        $this->artisan('activity-log:purge', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('activity_log', 1);

        $files = Storage::disk('public')->allFiles('archives/activity-log-purge');
        $this->assertCount(0, $files);
    }

    public function test_skips_when_nothing_to_purge(): void
    {
        // Recent log — not eligible
        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
        ]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $this->assertDatabaseCount('activity_log', 1);

        $files = Storage::disk('public')->allFiles('archives/activity-log-purge');
        $this->assertCount(0, $files);
    }

    public function test_archive_filename_includes_date(): void
    {
        $old = Activity::create([
            'log_name' => 'default',
            'description' => 'created',
        ]);
        Activity::query()->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);

        $this->artisan('activity-log:purge')
            ->assertSuccessful();

        $files = Storage::disk('public')->allFiles('archives/activity-log-purge');
        $this->assertCount(1, $files);

        $today = now()->format('Y-m-d');
        $this->assertStringContainsString($today, $files[0]);
        $this->assertStringContainsString('activity-log-purge-', $files[0]);
    }
}
