<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_broadcast_via_factory(): void
    {
        $broadcast = Broadcast::factory()->create();

        $this->assertDatabaseHas('broadcasts', [
            'id' => $broadcast->id,
            'slug' => $broadcast->slug,
        ]);
    }

    public function test_published_scope_returns_only_published_records(): void
    {
        Broadcast::factory()->create(['status' => 'draft']);
        Broadcast::factory()->published()->create();
        Broadcast::factory()->published()->create();

        $published = Broadcast::published()->get();

        $this->assertCount(2, $published);
        $published->each(fn (Broadcast $b) => $this->assertEquals('published', $b->status));
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Broadcast::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        Broadcast::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $published = Broadcast::published()->get();

        $this->assertCount(1, $published);
    }

    public function test_published_scope_includes_null_published_at(): void
    {
        Broadcast::factory()->create([
            'status' => 'published',
            'published_at' => null,
        ]);

        $published = Broadcast::published()->get();

        $this->assertCount(1, $published);
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $broadcast = Broadcast::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $broadcast->creator);
        $this->assertEquals($user->id, $broadcast->creator->id);
    }

    public function test_published_at_is_cast_to_datetime(): void
    {
        $broadcast = Broadcast::factory()->published()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $broadcast->published_at);
    }

    public function test_factory_states_set_correct_type(): void
    {
        $announcement = Broadcast::factory()->announcement()->create();
        $pressRelease = Broadcast::factory()->pressRelease()->create();
        $news = Broadcast::factory()->news()->create();

        $this->assertEquals('announcement', $announcement->type);
        $this->assertEquals('press_release', $pressRelease->type);
        $this->assertEquals('news', $news->type);
    }

    public function test_slug_is_unique(): void
    {
        Broadcast::factory()->create(['slug' => 'test-slug']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Broadcast::factory()->create(['slug' => 'test-slug']);
    }
}
