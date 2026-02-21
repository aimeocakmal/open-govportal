<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
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
        ]);
    }
}
