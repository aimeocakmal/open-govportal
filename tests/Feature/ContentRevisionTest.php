<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_revision_created_on_model_update(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Original Title',
            'status' => 'published',
            'slug' => 'original',
        ]);

        $broadcast->update(['title_ms' => 'Updated Title']);

        $this->assertDatabaseHas('content_revisions', [
            'revisionable_type' => Broadcast::class,
            'revisionable_id' => $broadcast->id,
        ]);
    }

    public function test_revision_stores_previous_state(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Before Update',
            'status' => 'published',
            'slug' => 'before-update',
        ]);

        $broadcast->update(['title_ms' => 'After Update']);

        $revision = ContentRevision::where('revisionable_id', $broadcast->id)->first();

        $this->assertNotNull($revision);
        $this->assertEquals('Before Update', $revision->content['title_ms']);
    }

    public function test_restore_revision_reverts_model(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Version 1',
            'status' => 'published',
            'slug' => 'restore-test',
        ]);

        $broadcast->update(['title_ms' => 'Version 2']);

        $revision = ContentRevision::where('revisionable_id', $broadcast->id)->first();

        $broadcast->restoreRevision($revision);

        $this->assertEquals('Version 1', $broadcast->fresh()->title_ms);
    }

    public function test_no_revision_on_create(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'New Broadcast',
            'status' => 'draft',
            'slug' => 'new-broadcast',
        ]);

        $this->assertDatabaseMissing('content_revisions', [
            'revisionable_type' => Broadcast::class,
            'revisionable_id' => $broadcast->id,
        ]);
    }

    public function test_revision_tracks_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Track User',
            'status' => 'published',
            'slug' => 'track-user',
        ]);

        $broadcast->update(['title_ms' => 'Updated by User']);

        $revision = ContentRevision::where('revisionable_id', $broadcast->id)->first();

        $this->assertEquals($user->id, $revision->user_id);
    }

    public function test_latest_revisions_returns_limited_results(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Many Revisions',
            'status' => 'published',
            'slug' => 'many-revisions',
        ]);

        for ($i = 1; $i <= 15; $i++) {
            $broadcast->update(['title_ms' => "Revision {$i}"]);
        }

        $revisions = $broadcast->latestRevisions(10);

        $this->assertCount(10, $revisions);
    }
}
