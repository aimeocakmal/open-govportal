<?php

namespace App\Observers;

use App\Models\MenuItem;
use App\Services\AdminNavigationService;
use App\Services\PublicNavigationService;

class MenuItemObserver
{
    public function saved(MenuItem $menuItem): void
    {
        $this->clearCaches($menuItem);
    }

    public function deleted(MenuItem $menuItem): void
    {
        $this->clearCaches($menuItem);
    }

    private function clearCaches(MenuItem $menuItem): void
    {
        $menuName = $menuItem->menu?->name;

        if ($menuName === 'admin_sidebar') {
            AdminNavigationService::clearCache();
        }

        if ($menuName === 'public_header' || $menuName === 'public_footer') {
            PublicNavigationService::clearCache();
        }
    }
}
