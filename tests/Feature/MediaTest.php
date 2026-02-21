<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_media_via_factory(): void
    {
        $media = Media::factory()->create();

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'filename' => $media->filename,
        ]);
    }

    public function test_uploader_relationship(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['uploaded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $media->uploader);
        $this->assertEquals($user->id, $media->uploader->id);
    }

    public function test_file_size_is_cast_to_integer(): void
    {
        $media = Media::factory()->create(['file_size' => 2097152]);

        $this->assertIsInt($media->file_size);
        $this->assertEquals(2097152, $media->file_size);
    }

    public function test_width_and_height_are_cast_to_integer(): void
    {
        $media = Media::factory()->create(['width' => 1920, 'height' => 1080]);

        $this->assertIsInt($media->width);
        $this->assertIsInt($media->height);
        $this->assertEquals(1920, $media->width);
        $this->assertEquals(1080, $media->height);
    }

    public function test_bilingual_alt_text_is_stored(): void
    {
        $media = Media::factory()->create([
            'alt_ms' => 'Gambar ujian',
            'alt_en' => 'Test image',
        ]);

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'alt_ms' => 'Gambar ujian',
            'alt_en' => 'Test image',
        ]);
    }

    public function test_nullable_uploader(): void
    {
        $media = Media::factory()->create(['uploaded_by' => null]);

        $this->assertNull($media->uploader);
    }
}
