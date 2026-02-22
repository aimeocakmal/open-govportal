<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HubungiKamiController extends Controller
{
    public function index(string $locale): View
    {
        $cacheKey = "page:/{$locale}/hubungi-kami";

        $addresses = Cache::remember($cacheKey, 7200, function () {
            return Address::active()->get();
        });

        return view('hubungi-kami.index', [
            'addresses' => $addresses,
        ]);
    }
}
