<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\HeroBanner;
use App\Models\QuickLink;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(string $locale): View
    {
        $showHeroBanner = (bool) Setting::get('homepage_show_hero_banner', true);
        $showQuickLinks = (bool) Setting::get('homepage_show_quick_links', true);
        $showBroadcasts = (bool) Setting::get('homepage_show_broadcasts', true);
        $showAchievements = (bool) Setting::get('homepage_show_achievements', true);
        $broadcastsCount = (int) Setting::get('homepage_broadcasts_count', 6);
        $achievementsCount = (int) Setting::get('homepage_achievements_count', 7);
        $sectionOrder = json_decode(
            Setting::get('homepage_section_order', '["hero_banner","quick_links","broadcasts","achievements"]'),
            true
        ) ?: ['hero_banner', 'quick_links', 'broadcasts', 'achievements'];

        return view('home.index', [
            'heroBanners' => $showHeroBanner ? HeroBanner::active()->get() : collect(),
            'quickLinks' => $showQuickLinks ? QuickLink::active()->get() : collect(),
            'broadcasts' => $showBroadcasts
                ? Broadcast::published()->orderByDesc('published_at')->limit($broadcastsCount)->get()
                : collect(),
            'achievements' => $showAchievements
                ? Achievement::published()->orderByDesc('date')->limit($achievementsCount)->get()
                : collect(),
            'showHeroBanner' => $showHeroBanner,
            'showQuickLinks' => $showQuickLinks,
            'showBroadcasts' => $showBroadcasts,
            'showAchievements' => $showAchievements,
            'sectionOrder' => $sectionOrder,
        ]);
    }
}
