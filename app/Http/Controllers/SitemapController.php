<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\StaticPage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:xml', 3600, function () {
            $urls = collect();

            // Static public pages
            $staticRoutes = ['/', '/siaran', '/pencapaian', '/statistik', '/direktori', '/dasar', '/profil-kementerian', '/hubungi-kami'];

            foreach (['ms', 'en'] as $locale) {
                foreach ($staticRoutes as $route) {
                    $urls->push([
                        'loc' => url("/{$locale}{$route}"),
                        'changefreq' => $route === '/' ? 'daily' : 'weekly',
                        'priority' => $route === '/' ? '1.0' : '0.8',
                    ]);
                }
            }

            // Broadcasts
            Broadcast::published()->select('slug', 'updated_at')->each(function ($item) use ($urls) {
                foreach (['ms', 'en'] as $locale) {
                    $urls->push([
                        'loc' => url("/{$locale}/siaran/{$item->slug}"),
                        'lastmod' => $item->updated_at->toW3cString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ]);
                }
            });

            // Achievements
            Achievement::published()->select('slug', 'updated_at')->each(function ($item) use ($urls) {
                foreach (['ms', 'en'] as $locale) {
                    $urls->push([
                        'loc' => url("/{$locale}/pencapaian/{$item->slug}"),
                        'lastmod' => $item->updated_at->toW3cString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ]);
                }
            });

            // Static pages in sitemap
            StaticPage::published()->where('is_in_sitemap', true)->select('slug', 'updated_at')->each(function ($page) use ($urls) {
                foreach (['ms', 'en'] as $locale) {
                    $urls->push([
                        'loc' => url("/{$locale}/{$page->slug}"),
                        'lastmod' => $page->updated_at->toW3cString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.5',
                    ]);
                }
            });

            return view('sitemap.index', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
