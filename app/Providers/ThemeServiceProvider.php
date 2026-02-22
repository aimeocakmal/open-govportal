<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ThemeService::class);
    }

    public function boot(): void
    {
        // Register default theme views so they resolve in artisan commands and tests
        // (where ApplyTheme middleware does not run)
        view()->getFinder()->addLocation(resource_path('themes/default/views'));
    }
}
