<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DasarController extends Controller
{
    public function index(string $locale): View
    {
        $cacheKey = "page:/{$locale}/dasar";

        $policies = Cache::remember($cacheKey, 7200, function () {
            return Policy::published()
                ->latest('published_at')
                ->get();
        });

        return view('dasar.index', [
            'policies' => $policies,
        ]);
    }

    public function download(string $locale, int $id): Response
    {
        $policy = Policy::published()->findOrFail($id);

        if (! $policy->file_url) {
            abort(404);
        }

        $policy->increment('download_count');

        return redirect($policy->file_url);
    }
}
