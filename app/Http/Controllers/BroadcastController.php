<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BroadcastController extends Controller
{
    public function index(string $locale): View
    {
        return view('siaran.index');
    }

    public function show(string $locale, string $slug): Response
    {
        $cacheKey = "page:/{$locale}/siaran/{$slug}";

        return Cache::remember($cacheKey, 7200, function () use ($slug) {
            $broadcast = Broadcast::published()
                ->where('slug', $slug)
                ->firstOrFail();

            $related = Broadcast::published()
                ->where('type', $broadcast->type)
                ->where('id', '!=', $broadcast->id)
                ->latest('published_at')
                ->limit(3)
                ->get();

            return response(
                view('siaran.show', [
                    'broadcast' => $broadcast,
                    'related' => $related,
                ])->render()
            );
        });
    }
}
