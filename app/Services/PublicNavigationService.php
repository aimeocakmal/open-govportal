<?php

namespace App\Services;

use App\Models\FooterSetting;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicNavigationService
{
    private const CACHE_TTL = 3600;

    /**
     * Get header navigation items from the public_header menu.
     *
     * Each item: ['label_ms' => ..., 'label_en' => ..., 'url' => 'siaran', 'children' => [...]]
     * The url is the path segment without locale prefix (e.g. 'siaran', not '/ms/siaran').
     *
     * @return list<array{label_ms: string, label_en: string, url: string, children: list<array{label_ms: string, label_en: string, url: string}>}>
     */
    public function getHeaderItems(): array
    {
        return Cache::remember('public_nav:header', self::CACHE_TTL, function (): array {
            try {
                $menu = Menu::where('name', 'public_header')->active()->first();
            } catch (\Throwable) {
                return $this->fallbackHeaderItems();
            }

            if (! $menu) {
                return $this->fallbackHeaderItems();
            }

            $activeChildren = fn ($q) => $q->active()->orderBy('sort_order');

            $items = $menu->rootItems()
                ->active()
                ->with([
                    'children' => $activeChildren,
                    'children.children' => $activeChildren,
                    'children.children.children' => $activeChildren,
                ])
                ->get();

            if ($items->isEmpty()) {
                return $this->fallbackHeaderItems();
            }

            return $items->map(fn ($item): array => $this->mapMenuItem($item))->values()->all();
        });
    }

    /**
     * Get footer menu columns from the public_footer menu.
     *
     * Each root item becomes a column header; its children are the links.
     *
     * @return Collection<int, MenuItem>
     */
    public function getFooterMenuItems(): Collection
    {
        return Cache::remember('public_nav:footer_menu', self::CACHE_TTL, function (): Collection {
            try {
                $menu = Menu::where('name', 'public_footer')->active()->first();
            } catch (\Throwable) {
                return collect();
            }

            if (! $menu) {
                return collect();
            }

            return $menu->rootItems()
                ->active()
                ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
                ->get();
        });
    }

    /**
     * Get footer branding block items (logo, heading, text, subheading).
     *
     * @return Collection<int, FooterSetting>
     */
    public function getFooterBranding(): Collection
    {
        return Cache::remember('public_nav:footer_branding', self::CACHE_TTL, function (): Collection {
            try {
                return FooterSetting::active()->where('section', 'branding')->orderBy('sort_order')->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * Get social links from footer_settings table.
     *
     * @return Collection<int, FooterSetting>
     */
    public function getFooterSocialLinks(): Collection
    {
        return Cache::remember('public_nav:footer_social', self::CACHE_TTL, function (): Collection {
            try {
                return FooterSetting::active()->where('section', 'social')->orderBy('sort_order')->get();
            } catch (\Throwable) {
                return collect();
            }
        });
    }

    /**
     * Get social media URLs from settings table.
     *
     * @return array{facebook_url: string, twitter_url: string, instagram_url: string, youtube_url: string}
     */
    public function getSocialUrls(): array
    {
        return [
            'facebook_url' => Setting::get('facebook_url', ''),
            'twitter_url' => Setting::get('twitter_url', ''),
            'instagram_url' => Setting::get('instagram_url', ''),
            'youtube_url' => Setting::get('youtube_url', ''),
        ];
    }

    /**
     * Flush public navigation cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('public_nav:header');
        Cache::forget('public_nav:footer_menu');
        Cache::forget('public_nav:footer_branding');
        Cache::forget('public_nav:footer_social');
    }

    /**
     * Recursively map a MenuItem to an array with nested children.
     *
     * @return array{label_ms: string, label_en: string, url: string, children: list<array>}
     */
    private function mapMenuItem(MenuItem $item): array
    {
        return [
            'label_ms' => $item->label_ms,
            'label_en' => $item->label_en,
            'url' => $this->stripLocalePrefix($item->url),
            'children' => $item->children->map(fn ($child): array => $this->mapMenuItem($child))->values()->all(),
        ];
    }

    /**
     * Strip locale prefix from a stored URL (e.g. '/ms/siaran' → 'siaran').
     */
    private function stripLocalePrefix(?string $url): string
    {
        if ($url === null || $url === '') {
            return '';
        }

        return preg_replace('#^/?(ms|en)/#', '', ltrim($url, '/'));
    }

    /**
     * @return list<array{label_ms: string, label_en: string, url: string, children: list<array{label_ms: string, label_en: string, url: string}>}>
     */
    private function fallbackHeaderItems(): array
    {
        return [
            ['label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'url' => 'siaran', 'children' => []],
            ['label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'url' => 'pencapaian', 'children' => []],
            ['label_ms' => 'Statistik', 'label_en' => 'Statistics', 'url' => 'statistik', 'children' => []],
            ['label_ms' => 'Direktori', 'label_en' => 'Directory', 'url' => 'direktori', 'children' => []],
            ['label_ms' => 'Dasar', 'label_en' => 'Policy', 'url' => 'dasar', 'children' => []],
            ['label_ms' => 'Profil Kementerian', 'label_en' => 'Ministry Profile', 'url' => 'profil-kementerian', 'children' => []],
            ['label_ms' => 'Hubungi Kami', 'label_en' => 'Contact Us', 'url' => 'hubungi-kami', 'children' => []],
        ];
    }
}
