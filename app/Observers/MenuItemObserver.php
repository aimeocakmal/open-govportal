<?php

namespace App\Observers;

use App\Models\MenuItem;
use App\Services\AdminNavigationService;

class MenuItemObserver
{
    public function saved(MenuItem $menuItem): void
    {
        $this->clearAdminSidebarCache($menuItem);
    }

    public function deleted(MenuItem $menuItem): void
    {
        $this->clearAdminSidebarCache($menuItem);
    }

    private function clearAdminSidebarCache(MenuItem $menuItem): void
    {
        // Only flush when the item belongs to the admin_sidebar menu
        if ($menuItem->menu && $menuItem->menu->name === 'admin_sidebar') {
            AdminNavigationService::clearCache();
        }
    }
}
