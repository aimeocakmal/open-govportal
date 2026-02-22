<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiaranPageTest extends TestCase
{
    use RefreshDatabase;

    // ── Listing page routes ──────────────────────────────────────────

    public function test_siaran_listing_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms/siaran');

        $response->assertOk();
    }

    public function test_siaran_listing_returns_ok_in_en(): void
    {
        $response = $this->get('/en/siaran');

        $response->assertOk();
    }

    public function test_siaran_listing_shows_page_title_in_ms(): void
    {
        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertSee('Siaran');
    }

    public function test_siaran_listing_shows_page_title_in_en(): void
    {
        $response = $this->get('/en/siaran');

        $response->assertOk();
        $response->assertSee('Broadcasts');
    }

    public function test_siaran_listing_has_breadcrumb(): void
    {
        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Siaran');
    }

    // ── Livewire SiaranList component ────────────────────────────────

    public function test_siaran_list_shows_published_broadcasts(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran Ujian Satu',
            'title_en' => 'Test Broadcast One',
        ]);

        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertSee('Siaran Ujian Satu');
    }

    public function test_siaran_list_shows_broadcasts_in_english(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran BM',
            'title_en' => 'Broadcast EN',
        ]);

        $response = $this->get('/en/siaran');

        $response->assertOk();
        $response->assertSee('Broadcast EN');
    }

    public function test_siaran_list_hides_draft_broadcasts(): void
    {
        Broadcast::factory()->create([
            'title_ms' => 'Draf Siaran Rahsia',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertDontSee('Draf Siaran Rahsia');
    }

    public function test_siaran_list_shows_no_results_message(): void
    {
        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertSee('Tiada siaran dijumpai.');
    }

    public function test_siaran_list_filters_by_type(): void
    {
        Broadcast::factory()->published()->announcement()->create([
            'title_ms' => 'Pengumuman Khas',
        ]);
        Broadcast::factory()->published()->news()->create([
            'title_ms' => 'Berita Terkini',
        ]);

        Livewire::test(\App\Livewire\SiaranList::class)
            ->set('type', 'announcement')
            ->assertSee('Pengumuman Khas')
            ->assertDontSee('Berita Terkini');
    }

    public function test_siaran_list_shows_all_types_when_no_filter(): void
    {
        Broadcast::factory()->published()->announcement()->create([
            'title_ms' => 'Pengumuman Khas',
        ]);
        Broadcast::factory()->published()->news()->create([
            'title_ms' => 'Berita Terkini',
        ]);

        Livewire::test(\App\Livewire\SiaranList::class)
            ->assertSee('Pengumuman Khas')
            ->assertSee('Berita Terkini');
    }

    public function test_siaran_list_resets_page_on_type_change(): void
    {
        Broadcast::factory()->published()->announcement()->count(20)->create();

        Livewire::test(\App\Livewire\SiaranList::class)
            ->set('type', 'announcement')
            ->assertSet('paginators.page', 1);
    }

    public function test_siaran_list_paginates_at_15_per_page(): void
    {
        Broadcast::factory()->published()->count(20)->create();

        Livewire::test(\App\Livewire\SiaranList::class)
            ->assertViewHas('broadcasts', function ($broadcasts) {
                return $broadcasts->perPage() === 15;
            });
    }

    public function test_siaran_list_shows_type_badges(): void
    {
        Broadcast::factory()->published()->pressRelease()->create([
            'title_ms' => 'Media Rasmi',
        ]);

        $response = $this->get('/ms/siaran');

        $response->assertOk();
        $response->assertSee('Siaran Media');
    }

    // ── Detail page ──────────────────────────────────────────────────

    public function test_siaran_detail_returns_ok_in_ms(): void
    {
        $broadcast = Broadcast::factory()->published()->create([
            'slug' => 'siaran-ujian',
        ]);

        $response = $this->get('/ms/siaran/siaran-ujian');

        $response->assertOk();
    }

    public function test_siaran_detail_returns_ok_in_en(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'test-broadcast',
        ]);

        $response = $this->get('/en/siaran/test-broadcast');

        $response->assertOk();
    }

    public function test_siaran_detail_shows_title_in_correct_locale(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'dwibahasa',
            'title_ms' => 'Tajuk Bahasa Melayu',
            'title_en' => 'English Title',
        ]);

        $msResponse = $this->get('/ms/siaran/dwibahasa');
        $msResponse->assertOk();
        $msResponse->assertSee('Tajuk Bahasa Melayu');

        $enResponse = $this->get('/en/siaran/dwibahasa');
        $enResponse->assertOk();
        $enResponse->assertSee('English Title');
    }

    public function test_siaran_detail_shows_content(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'siaran-kandungan',
            'content_ms' => '<p>Kandungan ujian siaran.</p>',
        ]);

        $response = $this->get('/ms/siaran/siaran-kandungan');

        $response->assertOk();
        $response->assertSee('Kandungan ujian siaran.');
    }

    public function test_siaran_detail_shows_breadcrumb(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'siaran-breadcrumb',
            'title_ms' => 'Tajuk Siaran BC',
        ]);

        $response = $this->get('/ms/siaran/siaran-breadcrumb');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Siaran', false);
        $response->assertSee('Tajuk Siaran BC');
    }

    public function test_siaran_detail_has_seo_meta_tags(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'siaran-seo',
            'title_ms' => 'SEO Siaran Tajuk',
            'excerpt_ms' => 'Penerangan ringkas untuk SEO.',
        ]);

        $response = $this->get('/ms/siaran/siaran-seo');

        $response->assertOk();
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:type', false);
    }

    public function test_siaran_detail_shows_related_broadcasts(): void
    {
        $main = Broadcast::factory()->published()->announcement()->create([
            'slug' => 'siaran-utama',
            'title_ms' => 'Siaran Utama',
        ]);

        $related = Broadcast::factory()->published()->announcement()->create([
            'title_ms' => 'Siaran Berkaitan',
        ]);

        $unrelated = Broadcast::factory()->published()->news()->create([
            'title_ms' => 'Siaran Tidak Berkaitan',
        ]);

        $response = $this->get('/ms/siaran/siaran-utama');

        $response->assertOk();
        $response->assertSee('Siaran Berkaitan');
        $response->assertDontSee('Siaran Tidak Berkaitan');
    }

    public function test_siaran_detail_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/ms/siaran/tidak-wujud');

        $response->assertNotFound();
    }

    public function test_siaran_detail_returns_404_for_draft(): void
    {
        Broadcast::factory()->create([
            'slug' => 'draf-siaran',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms/siaran/draf-siaran');

        $response->assertNotFound();
    }

    public function test_siaran_detail_shows_type_badge(): void
    {
        Broadcast::factory()->published()->pressRelease()->create([
            'slug' => 'siaran-media-badge',
        ]);

        $response = $this->get('/ms/siaran/siaran-media-badge');

        $response->assertOk();
        $response->assertSee('Siaran Media');
    }

    public function test_siaran_detail_shows_published_date(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'siaran-tarikh',
            'published_at' => '2025-06-15 10:00:00',
        ]);

        $response = $this->get('/ms/siaran/siaran-tarikh');

        $response->assertOk();
        $response->assertSee('2025');
    }
}
