<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPublicHeader();
        $this->seedPublicFooter();
        $this->seedAdminSidebar();
    }

    private function seedPublicHeader(): void
    {
        $header = Menu::firstOrCreate(
            ['name' => 'public_header'],
            ['label_ms' => 'Menu Utama', 'label_en' => 'Main Menu', 'is_active' => true],
        );

        $items = [
            ['label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'url' => '/ms/siaran', 'sort_order' => 1],
            ['label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'url' => '/ms/pencapaian', 'sort_order' => 2],
            ['label_ms' => 'Statistik', 'label_en' => 'Statistics', 'url' => '/ms/statistik', 'sort_order' => 3],
            ['label_ms' => 'Direktori', 'label_en' => 'Directory', 'url' => '/ms/direktori', 'sort_order' => 4],
            ['label_ms' => 'Dasar', 'label_en' => 'Policy', 'url' => '/ms/dasar', 'sort_order' => 5],
            ['label_ms' => 'Profil Kementerian', 'label_en' => 'Ministry Profile', 'url' => '/ms/profil-kementerian', 'sort_order' => 6],
            ['label_ms' => 'Hubungi Kami', 'label_en' => 'Contact Us', 'url' => '/ms/hubungi-kami', 'sort_order' => 7],
        ];

        foreach ($items as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $header->id, 'label_ms' => $item['label_ms']],
                array_merge($item, ['menu_id' => $header->id, 'is_active' => true, 'is_system' => true]),
            );
        }
    }

    private function seedPublicFooter(): void
    {
        Menu::firstOrCreate(
            ['name' => 'public_footer'],
            ['label_ms' => 'Menu Footer', 'label_en' => 'Footer Menu', 'is_active' => true],
        );
    }

    private function seedAdminSidebar(): void
    {
        $sidebar = Menu::firstOrCreate(
            ['name' => 'admin_sidebar'],
            ['label_ms' => 'Menu Admin', 'label_en' => 'Admin Menu', 'is_active' => true],
        );

        // ── Navigation groups (root items) ────────────────────────────
        $groups = [
            ['route_name' => 'content', 'label_ms' => 'Kandungan', 'label_en' => 'Content', 'icon' => 'heroicon-o-document-text', 'sort_order' => 0],
            ['route_name' => 'homepage', 'label_ms' => 'Laman Utama', 'label_en' => 'Homepage', 'icon' => 'heroicon-o-home', 'sort_order' => 1],
            ['route_name' => 'user_management', 'label_ms' => 'Pengurusan Pengguna', 'label_en' => 'User Management', 'icon' => 'heroicon-o-user-group', 'sort_order' => 2],
            ['route_name' => 'settings', 'label_ms' => 'Tetapan', 'label_en' => 'Settings', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 3],
        ];

        $groupIds = [];
        foreach ($groups as $group) {
            $item = MenuItem::firstOrCreate(
                ['menu_id' => $sidebar->id, 'route_name' => $group['route_name'], 'parent_id' => null],
                array_merge($group, ['menu_id' => $sidebar->id, 'is_active' => true, 'is_system' => true]),
            );
            $groupIds[$group['route_name']] = $item->id;
        }

        // ── Content group items ───────────────────────────────────────
        $contentItems = [
            ['route_name' => 'broadcasts', 'label_ms' => 'Siaran', 'label_en' => 'Broadcasts', 'icon' => 'heroicon-o-megaphone', 'sort_order' => 0],
            ['route_name' => 'achievements', 'label_ms' => 'Pencapaian', 'label_en' => 'Achievements', 'icon' => 'heroicon-o-trophy', 'sort_order' => 1],
            ['route_name' => 'celebrations', 'label_ms' => 'Sambutan', 'label_en' => 'Celebrations', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 2],
            ['route_name' => 'policies', 'label_ms' => 'Dasar', 'label_en' => 'Policies', 'icon' => 'heroicon-o-document-text', 'sort_order' => 3],
            ['route_name' => 'staff-directories', 'label_ms' => 'Direktori Kakitangan', 'label_en' => 'Staff Directories', 'icon' => 'heroicon-o-users', 'sort_order' => 4],
            ['route_name' => 'policy-files', 'label_ms' => 'Fail Dasar', 'label_en' => 'Policy Files', 'icon' => 'heroicon-o-paper-clip', 'sort_order' => 5],
            ['route_name' => 'media', 'label_ms' => 'Media', 'label_en' => 'Media', 'icon' => 'heroicon-o-photo', 'sort_order' => 6],
            ['route_name' => 'feedback', 'label_ms' => 'Maklum Balas', 'label_en' => 'Feedback', 'icon' => 'heroicon-o-chat-bubble-left-right', 'sort_order' => 7],
            ['route_name' => 'search-overrides', 'label_ms' => 'Pengatasan Carian', 'label_en' => 'Search Overrides', 'icon' => 'heroicon-o-magnifying-glass', 'sort_order' => 8],
            ['route_name' => 'page-categories', 'label_ms' => 'Kategori Halaman', 'label_en' => 'Page Categories', 'icon' => 'heroicon-o-folder', 'sort_order' => 9],
            ['route_name' => 'static-pages', 'label_ms' => 'Halaman Statik', 'label_en' => 'Static Pages', 'icon' => 'heroicon-o-document-text', 'sort_order' => 10],
        ];

        $this->seedGroupItems($sidebar->id, $groupIds['content'], $contentItems);

        // ── Homepage group items ─────────────────────────────────────
        $homepageItems = [
            ['route_name' => 'hero-banners', 'label_ms' => 'Sepanduk Utama', 'label_en' => 'Hero Banners', 'icon' => 'heroicon-o-photo', 'sort_order' => 0],
            ['route_name' => 'quick-links', 'label_ms' => 'Pautan Pantas', 'label_en' => 'Quick Links', 'icon' => 'heroicon-o-link', 'sort_order' => 1],
            ['route_name' => 'manage-homepage', 'label_ms' => 'Tetapan Laman Utama', 'label_en' => 'Homepage Settings', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 2],
        ];

        $this->seedGroupItems($sidebar->id, $groupIds['homepage'], $homepageItems);

        // ── User Management group items ──────────────────────────────
        $userItems = [
            ['route_name' => 'users', 'label_ms' => 'Pengguna', 'label_en' => 'Users', 'icon' => 'heroicon-o-user-group', 'sort_order' => 0],
            ['route_name' => 'roles', 'label_ms' => 'Peranan', 'label_en' => 'Roles', 'icon' => 'heroicon-o-shield-check', 'sort_order' => 1],
        ];

        $this->seedGroupItems($sidebar->id, $groupIds['user_management'], $userItems);

        // ── Settings group items ─────────────────────────────────────
        $settingsItems = [
            ['route_name' => 'manage-site-info', 'label_ms' => 'Maklumat Laman', 'label_en' => 'Site Info', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 0],
            ['route_name' => 'manage-email-settings', 'label_ms' => 'Tetapan Emel', 'label_en' => 'Email Settings', 'icon' => 'heroicon-o-envelope', 'sort_order' => 1],
            ['route_name' => 'manage-media-settings', 'label_ms' => 'Tetapan Media', 'label_en' => 'Media Settings', 'icon' => 'heroicon-o-cloud', 'sort_order' => 2],
            ['route_name' => 'menus', 'label_ms' => 'Menu', 'label_en' => 'Menus', 'icon' => 'heroicon-o-bars-3', 'sort_order' => 3],
            ['route_name' => 'manage-footer', 'label_ms' => 'Kaki Halaman', 'label_en' => 'Footer', 'icon' => 'heroicon-o-bars-3-bottom-left', 'sort_order' => 4],
            ['route_name' => 'manage-minister-profile', 'label_ms' => 'Profil Menteri', 'label_en' => 'Minister Profile', 'icon' => 'heroicon-o-user', 'sort_order' => 5],
            ['route_name' => 'manage-addresses', 'label_ms' => 'Alamat', 'label_en' => 'Addresses', 'icon' => 'heroicon-o-map-pin', 'sort_order' => 6],
            ['route_name' => 'manage-feedback-settings', 'label_ms' => 'Tetapan Maklum Balas', 'label_en' => 'Feedback Settings', 'icon' => 'heroicon-o-cog-6-tooth', 'sort_order' => 7],
        ];

        $this->seedGroupItems($sidebar->id, $groupIds['settings'], $settingsItems);
    }

    /**
     * @param  array<int, array{route_name: string, label_ms: string, label_en: string, icon: string, sort_order: int}>  $items
     */
    private function seedGroupItems(int $menuId, int $parentId, array $items): void
    {
        foreach ($items as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $menuId, 'route_name' => $item['route_name'], 'parent_id' => $parentId],
                array_merge($item, ['menu_id' => $menuId, 'parent_id' => $parentId, 'is_active' => true, 'is_system' => true]),
            );
        }
    }
}
