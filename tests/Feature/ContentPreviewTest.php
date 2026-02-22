<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ContentPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_with_valid_signed_url_shows_content(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Draft Siaran Preview',
            'status' => 'draft',
            'slug' => 'draft-preview',
        ]);

        $url = URL::temporarySignedRoute(
            'preview.show',
            now()->addHour(),
            ['model' => 'broadcast', 'id' => $broadcast->id]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Draft Siaran Preview');
    }

    public function test_preview_without_signature_returns_403(): void
    {
        $broadcast = Broadcast::factory()->create([
            'status' => 'draft',
            'slug' => 'no-sig',
        ]);

        $response = $this->get("/preview/broadcast/{$broadcast->id}");

        $response->assertStatus(403);
    }

    public function test_preview_with_invalid_signature_returns_403(): void
    {
        $broadcast = Broadcast::factory()->create([
            'status' => 'draft',
            'slug' => 'bad-sig',
        ]);

        $response = $this->get("/preview/broadcast/{$broadcast->id}?signature=invalid");

        $response->assertStatus(403);
    }

    public function test_preview_shows_preview_banner(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Banner Test',
            'status' => 'draft',
            'slug' => 'banner-test',
        ]);

        $url = URL::temporarySignedRoute(
            'preview.show',
            now()->addHour(),
            ['model' => 'broadcast', 'id' => $broadcast->id]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        // Default locale is ms, so banner renders in Malay
        $response->assertSee('Mod Pratonton', false);
    }

    public function test_preview_works_for_published_content(): void
    {
        $broadcast = Broadcast::factory()->create([
            'title_ms' => 'Published Preview',
            'status' => 'published',
            'slug' => 'published-preview',
        ]);

        $url = URL::temporarySignedRoute(
            'preview.show',
            now()->addHour(),
            ['model' => 'broadcast', 'id' => $broadcast->id]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Published Preview');
    }

    public function test_preview_returns_404_for_unknown_model(): void
    {
        $url = URL::temporarySignedRoute(
            'preview.show',
            now()->addHour(),
            ['model' => 'unknown', 'id' => 1]
        );

        $response = $this->get($url);

        $response->assertStatus(404);
    }
}
