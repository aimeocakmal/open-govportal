<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_achievement_via_factory(): void
    {
        $achievement = Achievement::factory()->create();

        $this->assertDatabaseHas('achievements', [
            'id' => $achievement->id,
            'slug' => $achievement->slug,
        ]);
    }

    public function test_published_scope_returns_only_published_records(): void
    {
        Achievement::factory()->create(['status' => 'draft']);
        Achievement::factory()->published()->create();
        Achievement::factory()->published()->create();

        $published = Achievement::published()->get();

        $this->assertCount(2, $published);
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Achievement::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        Achievement::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->assertCount(1, Achievement::published()->get());
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $achievement->creator);
        $this->assertEquals($user->id, $achievement->creator->id);
    }

    public function test_date_is_cast_to_carbon(): void
    {
        $achievement = Achievement::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $achievement->date);
    }

    public function test_is_featured_is_cast_to_boolean(): void
    {
        $achievement = Achievement::factory()->featured()->create();

        $this->assertTrue($achievement->is_featured);
        $this->assertIsBool($achievement->is_featured);
    }

    public function test_featured_factory_state(): void
    {
        $achievement = Achievement::factory()->featured()->create();

        $this->assertTrue($achievement->is_featured);
    }

    public function test_slug_is_unique(): void
    {
        Achievement::factory()->create(['slug' => 'unique-slug']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Achievement::factory()->create(['slug' => 'unique-slug']);
    }
}
