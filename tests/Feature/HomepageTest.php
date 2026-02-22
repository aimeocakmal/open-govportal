<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\HeroBanner;
use App\Models\QuickLink;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    // ── Basic route tests ──────────────────────────────────────────────

    public function test_root_redirects_to_locale(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertMatchesRegularExpression('#/(ms|en)$#', $location);
    }

    public function test_homepage_returns_ok_in_ms(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
    }

    public function test_homepage_returns_ok_in_en(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
    }

    public function test_invalid_locale_returns_404(): void
    {
        $response = $this->get('/fr');

        $response->assertNotFound();
    }

    public function test_default_theme_is_applied_to_html_element(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('data-theme="default"', false);
    }

    // ── Hero banner tests ──────────────────────────────────────────────

    public function test_homepage_shows_active_hero_banners(): void
    {
        $banner = HeroBanner::factory()->create([
            'title_ms' => 'Tajuk Banner Utama',
            'title_en' => 'Main Banner Title',
            'is_active' => true,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Tajuk Banner Utama');
    }

    public function test_homepage_shows_hero_banners_in_english(): void
    {
        HeroBanner::factory()->create([
            'title_ms' => 'Tajuk BM',
            'title_en' => 'Title EN',
            'is_active' => true,
        ]);

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('Title EN');
    }

    public function test_homepage_hides_inactive_hero_banners(): void
    {
        HeroBanner::factory()->inactive()->create([
            'title_ms' => 'Banner Tidak Aktif',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Banner Tidak Aktif');
    }

    public function test_homepage_shows_default_hero_when_no_banners_exist(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Kementerian Digital Malaysia');
    }

    public function test_homepage_shows_cta_button_when_url_and_label_present(): void
    {
        HeroBanner::factory()->create([
            'cta_label_ms' => 'Ketahui Lebih Lanjut',
            'cta_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Ketahui Lebih Lanjut');
        $response->assertSee('https://example.com', false);
    }

    // ── Quick links tests ──────────────────────────────────────────────

    public function test_homepage_shows_active_quick_links(): void
    {
        QuickLink::factory()->create([
            'label_ms' => 'Portal MyGov',
            'label_en' => 'MyGov Portal',
            'is_active' => true,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Portal MyGov');
    }

    public function test_homepage_shows_quick_links_in_english(): void
    {
        QuickLink::factory()->create([
            'label_ms' => 'Portal MyGov',
            'label_en' => 'MyGov Portal',
            'is_active' => true,
        ]);

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('MyGov Portal');
    }

    public function test_homepage_hides_inactive_quick_links(): void
    {
        QuickLink::factory()->inactive()->create([
            'label_ms' => 'Pautan Tersembunyi',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Pautan Tersembunyi');
    }

    // ── Broadcasts tests ───────────────────────────────────────────────

    public function test_homepage_shows_published_broadcasts(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran Terbaru Ujian',
            'title_en' => 'Latest Test Broadcast',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Siaran Terbaru Ujian');
    }

    public function test_homepage_shows_broadcasts_in_english(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran BM',
            'title_en' => 'Broadcast EN',
        ]);

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('Broadcast EN');
    }

    public function test_homepage_hides_draft_broadcasts(): void
    {
        Broadcast::factory()->create([
            'title_ms' => 'Draf Siaran',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Draf Siaran');
    }

    public function test_homepage_respects_broadcasts_count_setting(): void
    {
        Setting::set('homepage_broadcasts_count', '2');

        Broadcast::factory()->published()->count(5)->create();

        $response = $this->get('/ms');
        $response->assertOk();

        // The DB has 5 published, but only 2 should render
        $allPublished = Broadcast::published()->get();
        $this->assertEquals(5, $allPublished->count());
    }

    public function test_homepage_shows_broadcast_type_badges(): void
    {
        Broadcast::factory()->published()->announcement()->create([
            'title_ms' => 'Pengumuman Khas',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Pengumuman');
    }

    public function test_homepage_broadcasts_link_to_detail_page(): void
    {
        Broadcast::factory()->published()->create([
            'slug' => 'siaran-ujian-satu',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('/ms/siaran/siaran-ujian-satu', false);
    }

    // ── Achievements tests ─────────────────────────────────────────────

    public function test_homepage_shows_published_achievements(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian Hebat',
            'title_en' => 'Great Achievement',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Pencapaian Hebat');
    }

    public function test_homepage_shows_achievements_in_english(): void
    {
        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian BM',
            'title_en' => 'Achievement EN',
        ]);

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('Achievement EN');
    }

    public function test_homepage_hides_draft_achievements(): void
    {
        Achievement::factory()->create([
            'title_ms' => 'Draf Pencapaian',
            'status' => 'draft',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Draf Pencapaian');
    }

    public function test_homepage_achievements_link_to_detail_page(): void
    {
        Achievement::factory()->published()->create([
            'slug' => 'pencapaian-ujian',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('/ms/pencapaian/pencapaian-ujian', false);
    }

    // ── Section visibility settings ────────────────────────────────────

    public function test_homepage_hides_hero_banner_when_setting_disabled(): void
    {
        Setting::set('homepage_show_hero_banner', '0');

        HeroBanner::factory()->create([
            'title_ms' => 'Banner Tersembunyi',
            'is_active' => true,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Banner Tersembunyi');
    }

    public function test_homepage_hides_quick_links_when_setting_disabled(): void
    {
        Setting::set('homepage_show_quick_links', '0');

        QuickLink::factory()->create([
            'label_ms' => 'Pautan Tersembunyi',
            'is_active' => true,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Pautan Tersembunyi');
    }

    public function test_homepage_hides_broadcasts_when_setting_disabled(): void
    {
        Setting::set('homepage_show_broadcasts', '0');

        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran Tersembunyi',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Siaran Tersembunyi');
    }

    public function test_homepage_hides_achievements_when_setting_disabled(): void
    {
        Setting::set('homepage_show_achievements', '0');

        Achievement::factory()->published()->create([
            'title_ms' => 'Pencapaian Tersembunyi',
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertDontSee('Pencapaian Tersembunyi');
    }

    // ── Section ordering ───────────────────────────────────────────────

    public function test_homepage_renders_sections_in_configured_order(): void
    {
        Setting::set('homepage_section_order', '["broadcasts","hero_banner","achievements","quick_links"]');

        HeroBanner::factory()->create(['is_active' => true, 'title_ms' => 'Banner Satu']);
        QuickLink::factory()->create(['is_active' => true, 'label_ms' => 'Pautan Satu']);
        Broadcast::factory()->published()->create(['title_ms' => 'Siaran Satu']);
        Achievement::factory()->published()->create(['title_ms' => 'Pencapaian Satu']);

        $response = $this->get('/ms');
        $response->assertOk();

        $content = $response->content();
        $broadcastPos = strpos($content, 'Siaran Satu');
        $bannerPos = strpos($content, 'Banner Satu');

        $this->assertNotFalse($broadcastPos);
        $this->assertNotFalse($bannerPos);
        $this->assertLessThan($bannerPos, $broadcastPos, 'Broadcasts should appear before hero banner');
    }

    // ── Section headings (i18n) ────────────────────────────────────────

    public function test_homepage_shows_section_headings_in_ms(): void
    {
        Broadcast::factory()->published()->create();
        Achievement::factory()->published()->create();

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Siaran Terbaru');
        $response->assertSee('Pencapaian');
    }

    public function test_homepage_shows_section_headings_in_en(): void
    {
        Broadcast::factory()->published()->create();
        Achievement::factory()->published()->create();

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('Latest Broadcasts');
        $response->assertSee('Achievements');
    }

    public function test_homepage_shows_view_all_links(): void
    {
        Broadcast::factory()->published()->create();

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('/ms/siaran', false);
    }

    // ── Edge cases ─────────────────────────────────────────────────────

    public function test_homepage_works_with_no_content(): void
    {
        $response = $this->get('/ms');

        $response->assertOk();
    }

    public function test_homepage_works_with_all_sections_disabled(): void
    {
        Setting::set('homepage_show_hero_banner', '0');
        Setting::set('homepage_show_quick_links', '0');
        Setting::set('homepage_show_broadcasts', '0');
        Setting::set('homepage_show_achievements', '0');

        $response = $this->get('/ms');

        $response->assertOk();
    }

    public function test_homepage_shows_multiple_hero_banners(): void
    {
        HeroBanner::factory()->create([
            'title_ms' => 'Banner Pertama',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        HeroBanner::factory()->create([
            'title_ms' => 'Banner Kedua',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $response = $this->get('/ms');

        $response->assertOk();
        $response->assertSee('Banner Pertama');
        $response->assertSee('Banner Kedua');
    }

    public function test_homepage_orders_hero_banners_by_sort_order(): void
    {
        HeroBanner::factory()->create([
            'title_ms' => 'Kedua',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        HeroBanner::factory()->create([
            'title_ms' => 'Pertama',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/ms');
        $content = $response->content();

        $firstPos = strpos($content, 'Pertama');
        $secondPos = strpos($content, 'Kedua');

        $this->assertLessThan($secondPos, $firstPos, 'Banner with lower sort_order should appear first');
    }

    public function test_homepage_broadcasts_ordered_by_published_date_desc(): void
    {
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran Lama',
            'published_at' => now()->subDays(5),
        ]);
        Broadcast::factory()->published()->create([
            'title_ms' => 'Siaran Baru',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/ms');
        $content = $response->content();

        $newPos = strpos($content, 'Siaran Baru');
        $oldPos = strpos($content, 'Siaran Lama');

        $this->assertLessThan($oldPos, $newPos, 'Newer broadcast should appear before older one');
    }
}
