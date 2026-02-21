<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);

        /*
         * Share navigation items with all views that include the nav component.
         *
         * In Phase 2 (Week 4), replace this array with a DB query on `navigation_items`
         * ordered by sort_order, cached with tag `navigation`, TTL 24h.
         */
        View::share('navItems', [
            ['label_ms' => 'Siaran',             'label_en' => 'Broadcasts',       'url' => 'siaran'],
            ['label_ms' => 'Pencapaian',          'label_en' => 'Achievements',     'url' => 'pencapaian'],
            ['label_ms' => 'Statistik',           'label_en' => 'Statistics',       'url' => 'statistik'],
            ['label_ms' => 'Direktori',           'label_en' => 'Directory',        'url' => 'direktori'],
            ['label_ms' => 'Dasar',               'label_en' => 'Policy',           'url' => 'dasar'],
            ['label_ms' => 'Profil Kementerian',  'label_en' => 'Ministry Profile', 'url' => 'profil-kementerian'],
            ['label_ms' => 'Hubungi Kami',        'label_en' => 'Contact Us',       'url' => 'hubungi-kami'],
        ]);

        /*
         * Share footer data with all views that include the footer component.
         *
         * In Phase 2 (Week 4), replace with a query on `settings` and
         * `footer_settings` tables, cached with tag `footer`, TTL 24h.
         */
        View::share('footerData', [
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
        ]);
    }
}
