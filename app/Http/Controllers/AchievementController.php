<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AchievementController extends Controller
{
    public function index(string $locale): View
    {
        return view('pencapaian.index');
    }

    public function show(string $locale, string $slug): Response
    {
        $cacheKey = "page:/{$locale}/pencapaian/{$slug}";

        return Cache::remember($cacheKey, 7200, function () use ($slug) {
            $achievement = Achievement::published()
                ->where('slug', $slug)
                ->firstOrFail();

            return response(
                view('pencapaian.show', [
                    'achievement' => $achievement,
                ])->render()
            );
        });
    }
}
