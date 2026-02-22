<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Address;
use App\Models\Broadcast;
use App\Models\FooterSetting;
use App\Models\HeroBanner;
use App\Models\MinisterProfile;
use App\Models\PageCategory;
use App\Models\Policy;
use App\Models\QuickLink;
use App\Models\StaticPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PageCacheObserverTest extends TestCase
{
    use RefreshDatabase;

    // ── Broadcast ──

    public function test_creating_broadcast_forgets_sitemap_cache(): void
    {
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        Broadcast::factory()->published()->create(['slug' => 'new-post']);

        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_updating_broadcast_forgets_detail_page_cache_for_both_locales(): void
    {
        $broadcast = Broadcast::factory()->published()->create(['slug' => 'test-post']);

        Cache::put('page:/ms/siaran/test-post', 'cached-ms', 7200);
        Cache::put('page:/en/siaran/test-post', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $broadcast->update(['title_ms' => 'Updated Title']);

        $this->assertFalse(Cache::has('page:/ms/siaran/test-post'));
        $this->assertFalse(Cache::has('page:/en/siaran/test-post'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_updating_broadcast_slug_forgets_both_old_and_new_slug_caches(): void
    {
        $broadcast = Broadcast::factory()->published()->create(['slug' => 'old-slug']);

        Cache::put('page:/ms/siaran/old-slug', 'cached-old-ms', 7200);
        Cache::put('page:/en/siaran/old-slug', 'cached-old-en', 7200);
        Cache::put('page:/ms/siaran/new-slug', 'cached-new-ms', 7200);

        $broadcast->update(['slug' => 'new-slug']);

        $this->assertFalse(Cache::has('page:/ms/siaran/old-slug'));
        $this->assertFalse(Cache::has('page:/en/siaran/old-slug'));
        $this->assertFalse(Cache::has('page:/ms/siaran/new-slug'));
    }

    public function test_deleting_broadcast_forgets_detail_page_cache_and_sitemap(): void
    {
        $broadcast = Broadcast::factory()->published()->create(['slug' => 'to-delete']);

        Cache::put('page:/ms/siaran/to-delete', 'cached-ms', 7200);
        Cache::put('page:/en/siaran/to-delete', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $broadcast->delete();

        $this->assertFalse(Cache::has('page:/ms/siaran/to-delete'));
        $this->assertFalse(Cache::has('page:/en/siaran/to-delete'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    // ── Achievement ──

    public function test_updating_achievement_forgets_detail_page_cache_for_both_locales(): void
    {
        $achievement = Achievement::factory()->published()->create(['slug' => 'test-achievement']);

        Cache::put('page:/ms/pencapaian/test-achievement', 'cached-ms', 7200);
        Cache::put('page:/en/pencapaian/test-achievement', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $achievement->update(['title_ms' => 'Updated']);

        $this->assertFalse(Cache::has('page:/ms/pencapaian/test-achievement'));
        $this->assertFalse(Cache::has('page:/en/pencapaian/test-achievement'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_updating_achievement_slug_forgets_both_old_and_new_slug_caches(): void
    {
        $achievement = Achievement::factory()->published()->create(['slug' => 'old-achievement']);

        Cache::put('page:/ms/pencapaian/old-achievement', 'cached-ms', 7200);
        Cache::put('page:/en/pencapaian/old-achievement', 'cached-en', 7200);

        $achievement->update(['slug' => 'new-achievement']);

        $this->assertFalse(Cache::has('page:/ms/pencapaian/old-achievement'));
        $this->assertFalse(Cache::has('page:/en/pencapaian/old-achievement'));
        $this->assertFalse(Cache::has('page:/ms/pencapaian/new-achievement'));
    }

    public function test_deleting_achievement_forgets_cache_and_sitemap(): void
    {
        $achievement = Achievement::factory()->published()->create(['slug' => 'remove-me']);

        Cache::put('page:/ms/pencapaian/remove-me', 'cached-ms', 7200);
        Cache::put('page:/en/pencapaian/remove-me', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $achievement->delete();

        $this->assertFalse(Cache::has('page:/ms/pencapaian/remove-me'));
        $this->assertFalse(Cache::has('page:/en/pencapaian/remove-me'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    // ── Policy ──

    public function test_creating_policy_forgets_dasar_index_cache_for_both_locales(): void
    {
        Cache::put('page:/ms/dasar', 'cached-ms', 7200);
        Cache::put('page:/en/dasar', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        Policy::factory()->published()->create();

        $this->assertFalse(Cache::has('page:/ms/dasar'));
        $this->assertFalse(Cache::has('page:/en/dasar'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_updating_policy_forgets_dasar_index_and_sitemap(): void
    {
        $policy = Policy::factory()->published()->create();

        Cache::put('page:/ms/dasar', 'cached-ms', 7200);
        Cache::put('page:/en/dasar', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $policy->update(['title_ms' => 'Updated Policy']);

        $this->assertFalse(Cache::has('page:/ms/dasar'));
        $this->assertFalse(Cache::has('page:/en/dasar'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_deleting_policy_forgets_dasar_index_and_sitemap(): void
    {
        $policy = Policy::factory()->published()->create();

        Cache::put('page:/ms/dasar', 'cached-ms', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $policy->delete();

        $this->assertFalse(Cache::has('page:/ms/dasar'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    // ── HeroBanner ──

    public function test_saving_hero_banner_runs_without_error(): void
    {
        $banner = HeroBanner::factory()->create();
        $banner->update(['title_ms' => 'Updated Banner']);

        $this->assertTrue(true);
    }

    // ── QuickLink ──

    public function test_saving_quick_link_runs_without_error(): void
    {
        $link = QuickLink::factory()->create();
        $link->update(['label_ms' => 'Updated Link']);

        $this->assertTrue(true);
    }

    // ── MinisterProfile ──

    public function test_saving_minister_profile_forgets_profil_kementerian_cache(): void
    {
        Cache::put('page:/ms/profil-kementerian', 'cached-ms', 7200);
        Cache::put('page:/en/profil-kementerian', 'cached-en', 7200);

        MinisterProfile::factory()->create();

        $this->assertFalse(Cache::has('page:/ms/profil-kementerian'));
        $this->assertFalse(Cache::has('page:/en/profil-kementerian'));
    }

    public function test_deleting_minister_profile_forgets_profil_kementerian_cache(): void
    {
        $profile = MinisterProfile::factory()->create();

        Cache::put('page:/ms/profil-kementerian', 'cached-ms', 7200);
        Cache::put('page:/en/profil-kementerian', 'cached-en', 7200);

        $profile->delete();

        $this->assertFalse(Cache::has('page:/ms/profil-kementerian'));
        $this->assertFalse(Cache::has('page:/en/profil-kementerian'));
    }

    // ── Address ──

    public function test_saving_address_forgets_hubungi_kami_cache(): void
    {
        Cache::put('page:/ms/hubungi-kami', 'cached-ms', 7200);
        Cache::put('page:/en/hubungi-kami', 'cached-en', 7200);

        Address::factory()->create();

        $this->assertFalse(Cache::has('page:/ms/hubungi-kami'));
        $this->assertFalse(Cache::has('page:/en/hubungi-kami'));
    }

    public function test_deleting_address_forgets_hubungi_kami_cache(): void
    {
        $address = Address::factory()->create();

        Cache::put('page:/ms/hubungi-kami', 'cached-ms', 7200);
        Cache::put('page:/en/hubungi-kami', 'cached-en', 7200);

        $address->delete();

        $this->assertFalse(Cache::has('page:/ms/hubungi-kami'));
        $this->assertFalse(Cache::has('page:/en/hubungi-kami'));
    }

    // ── StaticPage ──

    public function test_creating_static_page_forgets_static_page_cache_and_sitemap(): void
    {
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $page = StaticPage::factory()->published()->create(['slug' => 'new-page']);

        $this->assertFalse(Cache::has('page:/ms/static/new-page'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    public function test_updating_static_page_slug_forgets_old_and_new_cache_keys(): void
    {
        $page = StaticPage::factory()->published()->create(['slug' => 'old-page']);

        Cache::put('page:/ms/static/old-page', 'cached-ms', 7200);
        Cache::put('page:/en/static/old-page', 'cached-en', 7200);

        $page->update(['slug' => 'new-page']);

        $this->assertFalse(Cache::has('page:/ms/static/old-page'));
        $this->assertFalse(Cache::has('page:/en/static/old-page'));
        $this->assertFalse(Cache::has('page:/ms/static/new-page'));
    }

    public function test_deleting_static_page_forgets_cache_and_sitemap(): void
    {
        $page = StaticPage::factory()->published()->create(['slug' => 'delete-me']);

        Cache::put('page:/ms/static/delete-me', 'cached-ms', 7200);
        Cache::put('page:/en/static/delete-me', 'cached-en', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        $page->delete();

        $this->assertFalse(Cache::has('page:/ms/static/delete-me'));
        $this->assertFalse(Cache::has('page:/en/static/delete-me'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    // ── PageCategory ──

    public function test_saving_page_category_forgets_all_static_page_caches(): void
    {
        $page1 = StaticPage::factory()->published()->create(['slug' => 'page-one']);
        $page2 = StaticPage::factory()->published()->create(['slug' => 'page-two']);

        Cache::put('page:/ms/static/page-one', 'cached-ms', 7200);
        Cache::put('page:/en/static/page-one', 'cached-en', 7200);
        Cache::put('page:/ms/static/page-two', 'cached-ms', 7200);
        Cache::put('sitemap:xml', '<xml>cached</xml>', 3600);

        PageCategory::factory()->create();

        $this->assertFalse(Cache::has('page:/ms/static/page-one'));
        $this->assertFalse(Cache::has('page:/en/static/page-one'));
        $this->assertFalse(Cache::has('page:/ms/static/page-two'));
        $this->assertFalse(Cache::has('sitemap:xml'));
    }

    // ── FooterSetting ──

    public function test_saving_footer_setting_clears_public_navigation_cache(): void
    {
        Cache::put('public_nav:header', 'cached-header', 3600);
        Cache::put('public_nav:footer_menu', 'cached-footer', 3600);
        Cache::put('public_nav:footer_branding', 'cached-branding', 3600);
        Cache::put('public_nav:footer_social', 'cached-social', 3600);

        FooterSetting::create([
            'section' => 'social',
            'type' => 'link',
            'label_ms' => 'Facebook',
            'label_en' => 'Facebook',
            'url' => 'https://facebook.com',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertFalse(Cache::has('public_nav:header'));
        $this->assertFalse(Cache::has('public_nav:footer_menu'));
        $this->assertFalse(Cache::has('public_nav:footer_branding'));
        $this->assertFalse(Cache::has('public_nav:footer_social'));
    }
}
