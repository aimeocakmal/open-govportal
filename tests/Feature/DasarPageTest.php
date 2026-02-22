<?php

namespace Tests\Feature;

use App\Models\Policy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DasarPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dasar_page_returns_200_ms(): void
    {
        $this->get('/ms/dasar')->assertOk();
    }

    public function test_dasar_page_returns_200_en(): void
    {
        $this->get('/en/dasar')->assertOk();
    }

    public function test_dasar_page_displays_title_ms(): void
    {
        $this->get('/ms/dasar')->assertSee('Dasar');
    }

    public function test_dasar_page_displays_title_en(): void
    {
        $this->get('/en/dasar')->assertSee('Policy');
    }

    public function test_dasar_page_shows_published_policies(): void
    {
        $policy = Policy::factory()->published()->create([
            'title_ms' => 'Dasar Keselamatan Siber',
            'category' => 'keselamatan',
        ]);

        $this->get('/ms/dasar')->assertSee('Dasar Keselamatan Siber');
    }

    public function test_dasar_page_hides_draft_policies(): void
    {
        $policy = Policy::factory()->create([
            'title_ms' => 'Draft Policy Hidden',
            'status' => 'draft',
        ]);

        $this->get('/ms/dasar')->assertDontSee('Draft Policy Hidden');
    }

    public function test_dasar_page_shows_en_title_for_en_locale(): void
    {
        Policy::factory()->published()->create([
            'title_en' => 'National Cybersecurity Policy',
            'category' => 'keselamatan',
        ]);

        $this->get('/en/dasar')->assertSee('National Cybersecurity Policy');
    }

    public function test_dasar_page_shows_category_labels(): void
    {
        Policy::factory()->published()->create(['category' => 'data']);

        $this->get('/ms/dasar')->assertSee('Data');
    }

    public function test_dasar_page_shows_no_results_when_empty(): void
    {
        $this->get('/ms/dasar')->assertSee('Tiada dasar dijumpai.');
    }

    public function test_dasar_download_redirects_to_file(): void
    {
        $policy = Policy::factory()->published()->create([
            'file_url' => 'https://example.com/policy.pdf',
        ]);

        $this->get("/ms/dasar/{$policy->id}/muat-turun")
            ->assertRedirect('https://example.com/policy.pdf');
    }

    public function test_dasar_download_increments_count(): void
    {
        $policy = Policy::factory()->published()->create([
            'file_url' => 'https://example.com/policy.pdf',
            'download_count' => 5,
        ]);

        $this->get("/ms/dasar/{$policy->id}/muat-turun");

        $this->assertEquals(6, $policy->fresh()->download_count);
    }

    public function test_dasar_download_404_when_no_file(): void
    {
        $policy = Policy::factory()->published()->create([
            'file_url' => null,
        ]);

        $this->get("/ms/dasar/{$policy->id}/muat-turun")->assertNotFound();
    }

    public function test_dasar_download_404_for_draft(): void
    {
        $policy = Policy::factory()->create([
            'status' => 'draft',
            'file_url' => 'https://example.com/policy.pdf',
        ]);

        $this->get("/ms/dasar/{$policy->id}/muat-turun")->assertNotFound();
    }

    public function test_dasar_page_has_breadcrumb(): void
    {
        $this->get('/ms/dasar')->assertSee('Laman Utama');
    }
}
