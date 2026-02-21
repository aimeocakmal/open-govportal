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
        ]);
    }
}
