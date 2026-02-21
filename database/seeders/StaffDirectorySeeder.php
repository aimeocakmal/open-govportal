<?php

namespace Database\Seeders;

use App\Models\StaffDirectory;
use Illuminate\Database\Seeder;

class StaffDirectorySeeder extends Seeder
{
    public function run(): void
    {
        StaffDirectory::factory()->create([
            'name' => 'Dato\' Sri Ahmad bin Abdullah',
            'position_ms' => 'Ketua Setiausaha',
            'position_en' => 'Secretary General',
            'department_ms' => 'Pejabat Ketua Setiausaha',
            'department_en' => 'Office of the Secretary General',
            'division_ms' => null,
            'division_en' => null,
            'email' => 'ksu@digital.gov.my',
            'phone' => '+603-8000 8000',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        StaffDirectory::factory()->create([
            'name' => 'Puan Siti Aminah binti Mohd Yusof',
            'position_ms' => 'Timbalan Ketua Setiausaha (Dasar)',
            'position_en' => 'Deputy Secretary General (Policy)',
            'department_ms' => 'Bahagian Dasar Digital',
            'department_en' => 'Digital Policy Division',
            'division_ms' => null,
            'division_en' => null,
            'email' => 'tksu.dasar@digital.gov.my',
            'phone' => '+603-8000 8001',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        StaffDirectory::factory()->create([
            'name' => 'Encik Mohd Razif bin Hassan',
            'position_ms' => 'Pengarah',
            'position_en' => 'Director',
            'department_ms' => 'Bahagian Teknologi Maklumat',
            'department_en' => 'Information Technology Division',
            'division_ms' => 'Unit Pembangunan Sistem',
            'division_en' => 'System Development Unit',
            'email' => 'razif@digital.gov.my',
            'phone' => '+603-8000 8010',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
