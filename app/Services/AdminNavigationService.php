<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminNavigationService
{
    /** Cache key prefix for the admin sidebar navigation lookup. */
    private const CACHE_PREFIX = 'admin_sidebar_nav';

    /** Cache TTL in seconds (1 hour). */
    private const CACHE_TTL = 3600;

    /**
     * Get the sort order for a navigation item by its sidebar key.
     *
     * @return int|null The sort_order from the admin_sidebar menu, or null if not found.
     */
    public function getSort(string $sidebarKey): ?int
    {
        $items = $this->getItems();

        return $items->get($sidebarKey)?->sort_order;
    }

    /**
     * Check whether a navigation item is visible (active) by its sidebar key.
     */
    public function isVisible(string $sidebarKey): bool
    {
        $items = $this->getItems();
        $item = $items->get($sidebarKey);

        // If no item configured, show by default (auto-discovered resource)
        if (! $item) {
            return true;
        }

        return $item->is_active;
    }

    /**
     * Get ordered navigation group names (root items of admin_sidebar).
     *
     * @return Collection<string, array{label_ms: string, label_en: string, sort_order: int, is_active: bool, icon: string|null}>
     */
    public function getGroups(): Collection
    {
        return Cache::remember(
            self::CACHE_PREFIX.':groups',
            self::CACHE_TTL,
            function () {
                try {
                    $menu = Menu::where('name', 'admin_sidebar')->first();
                } catch (\Throwable) {
                    return collect();
                }

                if (! $menu) {
                    return collect();
                }

                return $menu->rootItems()
                    ->orderBy('sort_order')
                    ->get()
                    ->mapWithKeys(fn (MenuItem $item) => [
                        $item->route_name => [
                            'label_ms' => $item->label_ms,
                            'label_en' => $item->label_en,
                            'sort_order' => $item->sort_order,
                            'is_active' => $item->is_active,
                            'icon' => $item->icon,
                        ],
                    ]);
            }
        );
    }

    /**
     * Get group sort order by the group's route_name (e.g., 'content', 'settings').
     */
    public function getGroupSort(string $groupKey): ?int
    {
        $groups = $this->getGroups();

        return $groups->get($groupKey)['sort_order'] ?? null;
    }

    /**
     * Flush the cached navigation data.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX.':groups');
        Cache::forget(self::CACHE_PREFIX.':items');
    }

    /**
     * Get all child menu items keyed by route_name (sidebar key).
     *
     * @return Collection<string, MenuItem>
     */
    private function getItems(): Collection
    {
        return Cache::remember(
            self::CACHE_PREFIX.':items',
            self::CACHE_TTL,
            function () {
                try {
                    $menu = Menu::where('name', 'admin_sidebar')->first();
                } catch (\Throwable) {
                    return collect();
                }

                if (! $menu) {
                    return collect();
                }

                return $menu->items()
                    ->whereNotNull('parent_id')
                    ->get()
                    ->keyBy('route_name');
            }
        );
    }
}
