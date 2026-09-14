<?php

namespace Database\Seeders;

use App\Models\Celebration;
use App\Models\User;
use Illuminate\Database\Seeder;

class CelebrationSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            Celebration::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['image' => null, 'created_by' => $adminId]),
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'slug' => 'hari-kesedaran-kebolehcapaian-global-2026',
                'title_ms' => 'Hari Kesedaran Kebolehcapaian Global 2026',
                'title_en' => 'Global Accessibility Awareness Day 2026',
                'description_ms' => 'Sesi terbuka mengenai navigasi papan kekunci dan pembaca skrin, bersama demonstrasi menu kebolehcapaian portal.',
                'description_en' => 'An open session on keyboard navigation and screen readers, with a demonstration of the portal accessibility menu.',
                'event_date' => '2026-05-21',
                'status' => 'published',
                'published_at' => '2026-05-01 09:00:00',
            ],
            [
                'slug' => 'perjumpaan-komuniti-sumber-terbuka-2026',
                'title_ms' => 'Perjumpaan komuniti sumber terbuka 2026',
                'title_en' => 'Open-source community meetup 2026',
                'description_ms' => 'Perjumpaan hibrid pertama untuk penyumbang, pengguna dan agensi yang berminat dengan portal ini.',
                'description_en' => 'The first hybrid meetup for contributors, users and agencies interested in the portal.',
                'event_date' => '2026-05-05',
                'status' => 'published',
                'published_at' => '2026-04-14 09:00:00',
            ],
            [
                'slug' => 'sambutan-pelancaran-versi-1-0',
                'title_ms' => 'Sambutan pelancaran versi 1.0',
                'title_en' => 'Version 1.0 launch celebration',
                'description_ms' => 'Draf. Sambutan keluaran stabil pertama dirancang pada Disember 2026.',
                'description_en' => 'Draft. A celebration of the first stable release is planned for December 2026.',
                'event_date' => '2026-12-01',
                'status' => 'draft',
                'published_at' => null,
            ],
        ];
    }
}
