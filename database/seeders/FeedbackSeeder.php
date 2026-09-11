<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::SUPER_ADMIN_EMAIL)->value('id');

        foreach ($this->rows() as $row) {
            Feedback::query()->updateOrCreate(
                ['email' => $row['email'], 'subject' => $row['subject']],
                array_merge($row, [
                    'replied_by' => $row['status'] === 'replied' ? $adminId : null,
                ]),
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
                'name' => 'Nadia Rahman',
                'email' => 'nadia.rahman@example.com',
                'subject' => 'Cadangan penapis tahun pada halaman Siaran',
                'message' => 'Halaman Pencapaian ada penapis tahun tetapi halaman Siaran tidak. Boleh tambah penapis yang sama supaya mudah cari siaran lama?',
                'page_url' => '/ms/siaran',
                'rating' => 4,
                'status' => 'new',
                'ip_address' => '203.0.113.10',
                'reply' => null,
                'replied_at' => null,
            ],
            [
                'name' => 'Jonathan Teo',
                'email' => 'jonathan.teo@example.com',
                'subject' => 'Chart labels overflow on small screens',
                'message' => 'On the Statistics page the bar chart labels overlap each other on my phone in landscape mode. Portrait mode is fine.',
                'page_url' => '/en/statistik',
                'rating' => 3,
                'status' => 'read',
                'ip_address' => '203.0.113.11',
                'reply' => null,
                'replied_at' => null,
            ],
            [
                'name' => 'Meera Pillai',
                'email' => 'meera.pillai@example.com',
                'subject' => 'Thank you for the accessibility menu',
                'message' => 'The larger font option and the high-contrast background make the policy pages much easier for me to read. Please keep it.',
                'page_url' => '/en/dasar',
                'rating' => 5,
                'status' => 'replied',
                'ip_address' => '203.0.113.12',
                'reply' => 'Thank you for letting us know. The accessibility menu is a permanent feature and we plan to add a dark theme later this year.',
                'replied_at' => '2026-08-20 15:30:00',
            ],
        ];
    }
}
