<?php

namespace App\Http\Controllers;

use App\Models\MinisterProfile;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProfilKementerianController extends Controller
{
    public function index(string $locale): View
    {
        $cacheKey = "page:/{$locale}/profil-kementerian";

        $data = Cache::remember($cacheKey, 7200, function () use ($locale) {
            return [
                'minister' => MinisterProfile::current()->first(),
                'vision' => Setting::get("vision_{$locale}"),
                'mission' => Setting::get("mission_{$locale}"),
                'about' => Setting::get("about_{$locale}"),
            ];
        });

        return view('profil-kementerian.index', $data);
    }
}
