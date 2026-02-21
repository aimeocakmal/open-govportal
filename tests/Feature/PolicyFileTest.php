<?php

namespace Tests\Feature;

use App\Models\PolicyFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_policy_file_via_factory(): void
    {
        $file = PolicyFile::factory()->create();

        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'filename' => $file->filename,
        ]);
    }

    public function test_public_scope_returns_only_public_records(): void
    {
        PolicyFile::factory()->create(['is_public' => true]);
        PolicyFile::factory()->create(['is_public' => true]);
        PolicyFile::factory()->private()->create();

        $public = PolicyFile::public()->get();

        $this->assertCount(2, $public);
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $file = PolicyFile::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $file->creator);
        $this->assertEquals($user->id, $file->creator->id);
    }

    public function test_is_public_is_cast_to_boolean(): void
    {
        $file = PolicyFile::factory()->create(['is_public' => true]);

        $this->assertIsBool($file->is_public);
        $this->assertTrue($file->is_public);
    }

    public function test_file_size_is_cast_to_integer(): void
    {
        $file = PolicyFile::factory()->create(['file_size' => 1048576]);

        $this->assertIsInt($file->file_size);
        $this->assertEquals(1048576, $file->file_size);
    }

    public function test_private_factory_state(): void
    {
        $file = PolicyFile::factory()->private()->create();

        $this->assertFalse($file->is_public);
    }

    public function test_uses_files_table(): void
    {
        $file = PolicyFile::factory()->create();

        $this->assertEquals('files', $file->getTable());
    }
}
