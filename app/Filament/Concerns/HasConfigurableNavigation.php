<?php

namespace App\Filament\Concerns;

use App\Services\AdminNavigationService;

/**
 * Allows a Filament resource or page to read its navigation sort order
 * from the `admin_sidebar` menu items, making the sidebar order editable
 * from the Settings → Menus admin page.
 *
 * Each class using this trait must define a `$sidebarKey` property —
 * a unique string that matches the `route_name` column of the corresponding
 * MenuItem in the `admin_sidebar` menu.
 */
trait HasConfigurableNavigation
{
    /**
     * Override Filament's getNavigationSort() to read from the admin_sidebar menu.
     * Falls back to the static $navigationSort property if no matching menu item exists.
     */
    public static function getNavigationSort(): ?int
    {
        if (! property_exists(static::class, 'sidebarKey')) {
            return static::$navigationSort ?? null;
        }

        $service = app(AdminNavigationService::class);
        $sort = $service->getSort(static::$sidebarKey);

        return $sort ?? static::$navigationSort ?? null;
    }

    /**
     * Override Filament's shouldRegisterNavigation() to respect is_active.
     */
    public static function shouldRegisterNavigation(): bool
    {
        if (! property_exists(static::class, 'sidebarKey')) {
            return true;
        }

        $service = app(AdminNavigationService::class);

        return $service->isVisible(static::$sidebarKey);
    }
}
