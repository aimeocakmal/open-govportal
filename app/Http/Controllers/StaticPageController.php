<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $cacheKey = "page:/{$locale}/static/{$slug}";

        $page = Cache::remember($cacheKey, 7200, function () use ($slug) {
            return StaticPage::published()
                ->where('slug', $slug)
                ->firstOrFail();
        });

        return view('static.show', [
            'page' => $page,
        ]);
    }
}
