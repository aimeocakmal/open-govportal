<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StatistikController extends Controller
{
    public function index(string $locale): View
    {
        $charts = Cache::tags(['statistik'])
            ->remember("statistik:charts:{$locale}", 21600, function () {
                $chartsJson = Setting::get('statistik_charts', '[]');

                return json_decode($chartsJson, true) ?: [];
            });

        return view('statistik.index', [
            'charts' => $charts,
        ]);
    }
}
