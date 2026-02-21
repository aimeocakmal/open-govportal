<?php

namespace Tests\Feature;

use App\Models\Celebration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CelebrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_celebration_via_factory(): void
    {
        $celebration = Celebration::factory()->create();

        $this->assertDatabaseHas('celebrations', [
            'id' => $celebration->id,
        ]);
    }

    public function test_published_scope_returns_only_published_records(): void
    {
        Celebration::factory()->create(['status' => 'draft']);
        Celebration::factory()->published()->create();
        Celebration::factory()->published()->create();

        $published = Celebration::published()->get();

        $this->assertCount(2, $published);
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Celebration::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        Celebration::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->assertCount(1, Celebration::published()->get());
    }

    public function test_published_scope_includes_null_published_at(): void
    {
        Celebration::factory()->create([
            'status' => 'published',
            'published_at' => null,
        ]);

        $this->assertCount(1, Celebration::published()->get());
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $celebration = Celebration::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $celebration->creator);
        $this->assertEquals($user->id, $celebration->creator->id);
    }

    public function test_event_date_is_cast_to_carbon(): void
    {
        $celebration = Celebration::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $celebration->event_date);
    }

    public function test_slug_can_be_nullable(): void
    {
        $celebration = Celebration::factory()->create(['slug' => null]);

        $this->assertNull($celebration->slug);
        $this->assertDatabaseHas('celebrations', [
            'id' => $celebration->id,
            'slug' => null,
        ]);
    }
}
