<?php

namespace Tests\Feature;

use App\Models\Policy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_policy_via_factory(): void
    {
        $policy = Policy::factory()->create();

        $this->assertDatabaseHas('policies', [
            'id' => $policy->id,
            'slug' => $policy->slug,
        ]);
    }

    public function test_published_scope_returns_only_published_records(): void
    {
        Policy::factory()->create(['status' => 'draft']);
        Policy::factory()->published()->create();
        Policy::factory()->published()->create();

        $published = Policy::published()->get();

        $this->assertCount(2, $published);
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Policy::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        Policy::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->assertCount(1, Policy::published()->get());
    }

    public function test_published_scope_includes_null_published_at(): void
    {
        Policy::factory()->create([
            'status' => 'published',
            'published_at' => null,
        ]);

        $this->assertCount(1, Policy::published()->get());
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $policy = Policy::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $policy->creator);
        $this->assertEquals($user->id, $policy->creator->id);
    }

    public function test_file_size_is_cast_to_integer(): void
    {
        $policy = Policy::factory()->create(['file_size' => 1024]);

        $this->assertIsInt($policy->file_size);
    }

    public function test_download_count_is_cast_to_integer(): void
    {
        $policy = Policy::factory()->create(['download_count' => 42]);

        $this->assertIsInt($policy->download_count);
    }

    public function test_slug_is_unique(): void
    {
        Policy::factory()->create(['slug' => 'unique-policy']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Policy::factory()->create(['slug' => 'unique-policy']);
    }

    public function test_category_accepts_valid_values(): void
    {
        $categories = ['keselamatan', 'data', 'digital', 'ict', 'perkhidmatan'];

        foreach ($categories as $category) {
            $policy = Policy::factory()->create(['category' => $category]);
            $this->assertEquals($category, $policy->category);
        }
    }
}
