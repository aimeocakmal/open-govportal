<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetAdminLocale;
use App\Services\AdminNavigationService;
use App\Services\MediaDiskService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /** Map admin_sidebar group route_name → i18n key used by resources. */
    private const GROUP_I18N_MAP = [
        'content' => 'filament.nav.content',
        'homepage' => 'filament.nav.homepage',
        'user_management' => 'filament.nav.user_management',
        'settings' => 'filament.nav.settings',
    ];

    /**
     * Build navigation groups ordered from the admin_sidebar menu.
     * Returns translated group labels in the same order as the DB sort_order,
     * matching what each resource's getNavigationGroup() returns.
     *
     * @return array<int, string>
     */
    private function buildNavigationGroups(): array
    {
        $service = app(AdminNavigationService::class);
        $groups = $service->getGroups();

        if ($groups->isEmpty()) {
            return array_map(fn (string $key) => __($key), array_values(self::GROUP_I18N_MAP));
        }

        return $groups
            ->filter(fn (array $group) => $group['is_active'])
            ->keys()
            ->map(fn (string $routeName) => __(self::GROUP_I18N_MAP[$routeName] ?? $routeName))
            ->values()
            ->all();
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups($this->buildNavigationGroups())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->bootUsing(function () {
                app(MediaDiskService::class)->apply();
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SetAdminLocale::class,
            ]);
    }
}
