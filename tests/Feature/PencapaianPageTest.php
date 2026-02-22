<?php

namespace Tests\Feature;

use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PencapaianPageTest extends TestCase
{
    use RefreshDatabase;

    // ── Listing page routes ──────────────────────────────────────────

    public function test_pencapaian_listing_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
    }

    public function test_pencapaian_listing_returns_ok_in_en(): void
    {
        $response = $this->get('/en/pencapaian');

        $response->assertOk();
    }

    public function test_pencapaian_listing_shows_page_title_in_ms(): void
    {
        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertSee('Pencapaian');
    }

    public function test_pencapaian_listing_shows_page_title_in_en(): void
    {
        $response = $this->get('/en/pencapaian');

        $response->assertOk();
        $response->assertSee('Achievements');
    }

    public function test_pencapaian_listing_has_breadcrumb(): void
    {
        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Pencapaian');
    }

    // ── Livewire PencapaianList component ────────────────────────────

    public function test_pencapaian_list_shows_published_achievements(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian Ujian Satu',
            'title_en' => 'Test Achievement One',
        ]);

        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertSee('Pencapaian Ujian Satu');
    }

    public function test_pencapaian_list_shows_achievements_in_english(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian BM',
            'title_en' => 'Achievement EN',
        ]);

        $response = $this->get('/en/pencapaian');

        $response->assertOk();
        $response->assertSee('Achievement EN');
    }

    public function test_pencapaian_list_hides_draft_achievements(): void
    {
        Achievement::factory()->create([
            'title_ms' => 'Draf Pencapaian Rahsia',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertDontSee('Draf Pencapaian Rahsia');
    }

    public function test_pencapaian_list_shows_no_results_message(): void
    {
        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertSee('Tiada pencapaian dijumpai.');
    }

    public function test_pencapaian_list_filters_by_year(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian 2024',
            'date' => '2024-06-15',
        ]);
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian 2023',
            'date' => '2023-03-20',
        ]);

        Livewire::test(\App\Livewire\PencapaianList::class)
            ->set('year', '2024')
            ->assertSee('Pencapaian 2024')
            ->assertDontSee('Pencapaian 2023');
    }

    public function test_pencapaian_list_shows_all_years_when_no_filter(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian 2024',
            'date' => '2024-06-15',
        ]);
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian 2023',
            'date' => '2023-03-20',
        ]);

        Livewire::test(\App\Livewire\PencapaianList::class)
            ->assertSee('Pencapaian 2024')
            ->assertSee('Pencapaian 2023');
    }

    public function test_pencapaian_list_resets_page_on_year_change(): void
    {
        Achievement::factory()->published()->count(20)->create([
            'date' => '2024-01-01',
        ]);

        Livewire::test(\App\Livewire\PencapaianList::class)
            ->set('year', '2024')
            ->assertSet('paginators.page', 1);
    }

    public function test_pencapaian_list_paginates_at_15_per_page(): void
    {
        Achievement::factory()->published()->count(20)->create();

        Livewire::test(\App\Livewire\PencapaianList::class)
            ->assertViewHas('achievements', function ($achievements) {
                return $achievements->perPage() === 15;
            });
    }

    public function test_pencapaian_list_populates_year_dropdown(): void
    {
        Achievement::factory()->published()->create(['date' => '2024-06-15']);
        Achievement::factory()->published()->create(['date' => '2023-03-20']);

        Livewire::test(\App\Livewire\PencapaianList::class)
            ->assertViewHas('years', function ($years) {
                return $years->contains(2024) && $years->contains(2023);
            });
    }

    public function test_pencapaian_list_shows_featured_badge(): void
    {
        Achievement::factory()->published()->featured()->create([
            'title_ms' => 'Pencapaian Utama',
        ]);

        $response = $this->get('/ms/pencapaian');

        $response->assertOk();
        $response->assertSee('Utama');
    }

    // ── Detail page ──────────────────────────────────────────────────

    public function test_pencapaian_detail_returns_ok_in_ms(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-ujian',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-ujian');

        $response->assertOk();
    }

    public function test_pencapaian_detail_returns_ok_in_en(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'test-achievement',
        ]);

        $response = $this->get('/en/pencapaian/test-achievement');

        $response->assertOk();
    }

    public function test_pencapaian_detail_shows_title_in_correct_locale(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'dwibahasa',
            'title_ms' => 'Tajuk Bahasa Melayu',
            'title_en' => 'English Title',
        ]);

        $msResponse = $this->get('/ms/pencapaian/dwibahasa');
        $msResponse->assertOk();
        $msResponse->assertSee('Tajuk Bahasa Melayu');

        $enResponse = $this->get('/en/pencapaian/dwibahasa');
        $enResponse->assertOk();
        $enResponse->assertSee('English Title');
    }

    public function test_pencapaian_detail_shows_description(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-kandungan',
            'description_ms' => '<p>Kandungan ujian pencapaian.</p>',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-kandungan');

        $response->assertOk();
        $response->assertSee('Kandungan ujian pencapaian.');
    }

    public function test_pencapaian_detail_shows_breadcrumb(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-breadcrumb',
            'title_ms' => 'Tajuk Pencapaian BC',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-breadcrumb');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Pencapaian', false);
        $response->assertSee('Tajuk Pencapaian BC');
    }

    public function test_pencapaian_detail_has_seo_meta_tags(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-seo',
            'title_ms' => 'SEO Pencapaian Tajuk',
            'description_ms' => 'Penerangan ringkas untuk SEO.',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-seo');

        $response->assertOk();
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:type', false);
    }

    public function test_pencapaian_detail_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/ms/pencapaian/tidak-wujud');

        $response->assertNotFound();
    }

    public function test_pencapaian_detail_returns_404_for_draft(): void
    {
        Achievement::factory()->create([
            'slug' => 'draf-pencapaian',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms/pencapaian/draf-pencapaian');

        $response->assertNotFound();
    }

    public function test_pencapaian_detail_shows_featured_badge(): void
    {
        Achievement::factory()->published()->featured()->create([
            'slug' => 'pencapaian-utama',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-utama');

        $response->assertOk();
        $response->assertSee('Utama');
    }

    public function test_pencapaian_detail_shows_date(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-tarikh',
            'date' => '2024-06-15',
        ]);

        $response = $this->get('/ms/pencapaian/pencapaian-tarikh');

        $response->assertOk();
        $response->assertSee('2024');
    }
}
