<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\Facades\Activity;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Activity::withoutLogs(fn () => $this->seedAll());
    }

    private function seedAll(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SettingsSeeder::class,
            BroadcastSeeder::class,
            AchievementSeeder::class,
            CelebrationSeeder::class,
            HeroBannerSeeder::class,
            QuickLinkSeeder::class,
            PolicySeeder::class,
            StaffDirectorySeeder::class,
            PolicyFileSeeder::class,
            MediaSeeder::class,
            FeedbackSeeder::class,
            SearchOverrideSeeder::class,
            FooterSettingSeeder::class,
            MinisterProfileSeeder::class,
            AddressSeeder::class,
            FeedbackSettingSeeder::class,
            MenuSeeder::class,
            PageCategorySeeder::class,
            StaticPageSeeder::class,
        ]);
    }
}
