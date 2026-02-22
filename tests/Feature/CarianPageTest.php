<?php

namespace Tests\Feature;

use App\Models\SearchableContent;
use App\Models\SearchOverride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CarianPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_carian_page_returns_200_ms(): void
    {
        $this->get('/ms/carian')->assertOk();
    }

    public function test_carian_page_returns_200_en(): void
    {
        $this->get('/en/carian')->assertOk();
    }

    public function test_carian_page_displays_title_ms(): void
    {
        $this->get('/ms/carian')->assertSee('Carian');
    }

    public function test_carian_page_displays_title_en(): void
    {
        $this->get('/en/carian')->assertSee('Search');
    }

    public function test_search_results_returns_matching_content(): void
    {
        SearchableContent::create([
            'searchable_type' => 'App\\Models\\Broadcast',
            'searchable_id' => 1,
            'title_ms' => 'Transformasi Digital Negara',
            'title_en' => 'National Digital Transformation',
            'content_ms' => 'Kerajaan melaksanakan program transformasi digital.',
            'content_en' => 'Government implements digital transformation program.',
            'url_ms' => '/ms/siaran/transformasi',
            'url_en' => '/en/siaran/transformasi',
            'priority' => 50,
        ]);

        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'transformasi')
            ->assertSee('Transformasi Digital Negara');
    }

    public function test_search_results_case_insensitive(): void
    {
        SearchableContent::create([
            'searchable_type' => 'App\\Models\\Broadcast',
            'searchable_id' => 1,
            'title_ms' => 'Dasar ICT Kebangsaan',
            'title_en' => 'National ICT Policy',
            'content_ms' => 'Dasar ICT untuk negara.',
            'content_en' => 'ICT Policy for the nation.',
            'url_ms' => '/ms/dasar',
            'url_en' => '/en/dasar',
            'priority' => 40,
        ]);

        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'dasar ict')
            ->assertSee('Dasar ICT Kebangsaan');
    }

    public function test_search_no_results_message(): void
    {
        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'xyznonexistent')
            ->assertSee('Tiada hasil carian dijumpai.');
    }

    public function test_search_requires_minimum_query_length(): void
    {
        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'a')
            ->assertDontSee('Tiada hasil carian dijumpai.');
    }

    public function test_search_overrides_shown_first(): void
    {
        SearchOverride::factory()->create([
            'query' => 'digital',
            'title_ms' => 'Halaman Utama Digital',
            'title_en' => 'Digital Homepage',
            'url' => '/ms/',
            'is_active' => true,
            'priority' => 100,
        ]);

        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'digital')
            ->assertSee('Halaman Utama Digital');
    }

    public function test_search_override_inactive_not_shown(): void
    {
        SearchOverride::factory()->create([
            'query' => 'hidden',
            'title_ms' => 'Hidden Override',
            'url' => '/ms/',
            'is_active' => false,
        ]);

        Livewire::test(\App\Livewire\SearchResults::class)
            ->set('query', 'hidden')
            ->assertDontSee('Hidden Override');
    }

    public function test_carian_page_has_breadcrumb(): void
    {
        $this->get('/ms/carian')->assertSee('Laman Utama');
    }
}
