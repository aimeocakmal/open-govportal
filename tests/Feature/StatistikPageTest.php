<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistikPageTest extends TestCase
{
    use RefreshDatabase;

    // ── Page routes ──────────────────────────────────────────────────

    public function test_statistik_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
    }

    public function test_statistik_returns_ok_in_en(): void
    {
        $response = $this->get('/en/statistik');

        $response->assertOk();
    }

    public function test_statistik_shows_page_title_in_ms(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Statistik');
    }

    public function test_statistik_shows_page_title_in_en(): void
    {
        $response = $this->get('/en/statistik');

        $response->assertOk();
        $response->assertSee('Statistics');
    }

    public function test_statistik_shows_description_in_ms(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Statistik dan petunjuk prestasi utama (KPI) Kementerian Digital Malaysia.');
    }

    public function test_statistik_shows_description_in_en(): void
    {
        $response = $this->get('/en/statistik');

        $response->assertOk();
        $response->assertSee('Statistics and key performance indicators (KPIs) of the Ministry of Digital Malaysia.');
    }

    public function test_statistik_has_breadcrumb(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Laman Utama');
        $response->assertSee('Statistik');
    }

    // ── No data state ────────────────────────────────────────────────

    public function test_statistik_shows_no_data_message_when_empty(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Tiada data statistik tersedia buat masa ini.');
    }

    public function test_statistik_shows_no_data_message_in_en_when_empty(): void
    {
        $response = $this->get('/en/statistik');

        $response->assertOk();
        $response->assertSee('No statistics data available at this time.');
    }

    // ── Charts rendering ─────────────────────────────────────────────

    public function test_statistik_shows_charts_when_data_exists(): void
    {
        $charts = [
            [
                'title_ms' => 'Pelawat Laman Web',
                'title_en' => 'Website Visitors',
                'description_ms' => 'Bilangan pelawat bulanan',
                'description_en' => 'Monthly visitor count',
                'type' => 'line',
                'data' => [
                    'labels' => ['Jan', 'Feb', 'Mac'],
                    'datasets' => [
                        [
                            'label' => 'Pelawat',
                            'data' => [12000, 15000, 13500],
                            'color' => '#2563EB',
                        ],
                    ],
                ],
            ],
        ];

        Setting::set('statistik_charts', json_encode($charts), 'json');

        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Pelawat Laman Web');
        $response->assertSee('Bilangan pelawat bulanan');
    }

    public function test_statistik_shows_charts_in_en(): void
    {
        $charts = [
            [
                'title_ms' => 'Pelawat Laman Web',
                'title_en' => 'Website Visitors',
                'description_ms' => 'Bilangan pelawat bulanan',
                'description_en' => 'Monthly visitor count',
                'type' => 'bar',
                'data' => [
                    'labels' => ['Jan', 'Feb'],
                    'datasets' => [
                        ['label' => 'Visitors', 'data' => [100, 200], 'color' => '#2563EB'],
                    ],
                ],
            ],
        ];

        Setting::set('statistik_charts', json_encode($charts), 'json');

        $response = $this->get('/en/statistik');

        $response->assertOk();
        $response->assertSee('Website Visitors');
        $response->assertSee('Monthly visitor count');
    }

    public function test_statistik_does_not_show_no_data_when_charts_exist(): void
    {
        $charts = [
            [
                'title_ms' => 'Carta Ujian',
                'title_en' => 'Test Chart',
                'type' => 'bar',
                'data' => ['labels' => ['A'], 'datasets' => [['label' => 'X', 'data' => [1]]]],
            ],
        ];

        Setting::set('statistik_charts', json_encode($charts), 'json');

        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertDontSee('Tiada data statistik tersedia buat masa ini.');
    }

    public function test_statistik_includes_chartjs_script_when_charts_exist(): void
    {
        $charts = [
            [
                'title_ms' => 'Carta',
                'type' => 'bar',
                'data' => ['labels' => ['A'], 'datasets' => [['label' => 'X', 'data' => [1]]]],
            ],
        ];

        Setting::set('statistik_charts', json_encode($charts), 'json');

        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('chart.js@4', false);
    }

    public function test_statistik_does_not_include_chartjs_script_when_empty(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertDontSee('chart.js@4', false);
    }

    // ── SEO meta tags ────────────────────────────────────────────────

    public function test_statistik_has_seo_meta_tags(): void
    {
        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
    }

    public function test_statistik_shows_multiple_charts(): void
    {
        $charts = [
            [
                'title_ms' => 'Carta Pertama',
                'type' => 'bar',
                'data' => ['labels' => ['A'], 'datasets' => [['label' => 'X', 'data' => [1]]]],
            ],
            [
                'title_ms' => 'Carta Kedua',
                'type' => 'line',
                'data' => ['labels' => ['B'], 'datasets' => [['label' => 'Y', 'data' => [2]]]],
            ],
        ];

        Setting::set('statistik_charts', json_encode($charts), 'json');

        $response = $this->get('/ms/statistik');

        $response->assertOk();
        $response->assertSee('Carta Pertama');
        $response->assertSee('Carta Kedua');
    }
}
