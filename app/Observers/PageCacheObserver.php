<?php

namespace App\Observers;

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
use App\Services\PublicNavigationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates page-level caches when content models are saved or deleted.
 *
 * Model → Cache key mapping (see docs/pages-features.md lines 717–741):
 *   HeroBanner, QuickLink  → homepage keys (stub — HomeController not cached yet)
 *   Broadcast              → page:/{locale}/siaran/{slug}, sitemap:xml
 *   Achievement            → page:/{locale}/pencapaian/{slug}, sitemap:xml
 *   Policy                 → page:/{locale}/dasar, sitemap:xml
 *   MinisterProfile        → page:/{locale}/profil-kementerian
 *   Address                → page:/{locale}/hubungi-kami
 *   StaticPage             → page:/{locale}/static/{slug}, sitemap:xml
 *   PageCategory           → all static page keys, sitemap:xml
 *   FooterSetting          → delegates to PublicNavigationService::clearCache()
 */
class PageCacheObserver
{
    private const LOCALES = ['ms', 'en'];

    public function saved(Model $model): void
    {
        $this->forgetKeys($model);
    }

    public function deleted(Model $model): void
    {
        $this->forgetKeys($model);
    }

    private function forgetKeys(Model $model): void
    {
        $keys = $this->getCacheKeysForModel($model);

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @return array<int, string>
     */
    private function getCacheKeysForModel(Model $model): array
    {
        return match (true) {
            $model instanceof HeroBanner,
            $model instanceof QuickLink => $this->homepageKeys(),

            $model instanceof Broadcast => [
                ...$this->homepageKeys(),
                ...$this->slugBasedKeys('siaran', $model),
                'sitemap:xml',
            ],

            $model instanceof Achievement => [
                ...$this->homepageKeys(),
                ...$this->slugBasedKeys('pencapaian', $model),
                'sitemap:xml',
            ],

            $model instanceof Policy => [
                ...$this->localeKeys('dasar'),
                'sitemap:xml',
            ],

            $model instanceof MinisterProfile => $this->localeKeys('profil-kementerian'),

            $model instanceof Address => $this->localeKeys('hubungi-kami'),

            $model instanceof StaticPage => [
                ...$this->staticPageKeys($model),
                'sitemap:xml',
            ],

            $model instanceof PageCategory => [
                ...$this->allStaticPageKeys(),
                'sitemap:xml',
            ],

            $model instanceof FooterSetting => $this->handleFooterSetting(),

            default => [],
        };
    }

    /**
     * Homepage keys — stub for when HomeController caching is added.
     *
     * @return array<int, string>
     */
    private function homepageKeys(): array
    {
        return [];
    }

    /**
     * Generate cache keys for a slug-based detail page, handling slug changes.
     *
     * @return array<int, string>
     */
    private function slugBasedKeys(string $segment, Model $model): array
    {
        $slugs = [$model->slug];

        if ($model->wasChanged('slug') && $model->getOriginal('slug')) {
            $slugs[] = $model->getOriginal('slug');
        }

        $keys = [];
        foreach (self::LOCALES as $locale) {
            foreach ($slugs as $slug) {
                $keys[] = "page:/{$locale}/{$segment}/{$slug}";
            }
        }

        return $keys;
    }

    /**
     * Generate cache keys for a locale-prefixed index page.
     *
     * @return array<int, string>
     */
    private function localeKeys(string $segment): array
    {
        $keys = [];
        foreach (self::LOCALES as $locale) {
            $keys[] = "page:/{$locale}/{$segment}";
        }

        return $keys;
    }

    /**
     * Generate cache keys for a static page, handling slug changes.
     *
     * @return array<int, string>
     */
    private function staticPageKeys(Model $model): array
    {
        $slugs = [$model->slug];

        if ($model->wasChanged('slug') && $model->getOriginal('slug')) {
            $slugs[] = $model->getOriginal('slug');
        }

        $keys = [];
        foreach (self::LOCALES as $locale) {
            foreach ($slugs as $slug) {
                $keys[] = "page:/{$locale}/static/{$slug}";
            }
        }

        return $keys;
    }

    /**
     * Forget all static page cache keys (used when a PageCategory changes).
     *
     * @return array<int, string>
     */
    private function allStaticPageKeys(): array
    {
        $slugs = StaticPage::pluck('slug')->all();
        $keys = [];

        foreach (self::LOCALES as $locale) {
            foreach ($slugs as $slug) {
                $keys[] = "page:/{$locale}/static/{$slug}";
            }
        }

        return $keys;
    }

    /**
     * Delegate footer cache clearing to PublicNavigationService.
     *
     * @return array<int, string>
     */
    private function handleFooterSetting(): array
    {
        PublicNavigationService::clearCache();

        return [];
    }
}
