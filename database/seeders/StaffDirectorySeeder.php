<?php

namespace Database\Seeders;

use App\Models\StaffDirectory;
use Illuminate\Database\Seeder;

class StaffDirectorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $index => $row) {
            [$departmentMs, $departmentEn] = self::DEPARTMENTS[$row['department']];

            StaffDirectory::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'position_ms' => $row['position_ms'],
                    'position_en' => $row['position_en'],
                    'department_ms' => $departmentMs,
                    'department_en' => $departmentEn,
                    'division_ms' => $row['division_ms'] ?? null,
                    'division_en' => $row['division_en'] ?? null,
                    'phone' => sprintf('+603-0000 %d', 1001 + $index),
                    'fax' => null,
                    'photo' => null,
                    'sort_order' => $index + 1,
                    'is_active' => $row['is_active'] ?? true,
                ],
            );
        }
    }

    /**
     * Department pairs must be identical within a group so the Direktori
     * filter groups staff correctly in both locales.
     */
    public const DEPARTMENTS = [
        'admin' => ['Pentadbiran Portal', 'Portal Administration'],
        'content' => ['Kandungan & Editorial', 'Content & Editorial'],
        'tech' => ['Teknologi & Platform', 'Technology & Platform'],
        'design' => ['Reka Bentuk & Kebolehcapaian', 'Design & Accessibility'],
        'support' => ['Sokongan & Latihan', 'Support & Training'],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            ['name' => 'Amirul Hakim bin Roslan', 'email' => 'amirul@opengovportal.example', 'department' => 'admin', 'position_ms' => 'Pengarah Portal', 'position_en' => 'Portal Director'],
            ['name' => 'Chong Wei Lin', 'email' => 'weilin@opengovportal.example', 'department' => 'admin', 'position_ms' => 'Timbalan Pengarah', 'position_en' => 'Deputy Director'],
            ['name' => 'Saraswathy a/p Muniandy', 'email' => 'saraswathy@opengovportal.example', 'department' => 'admin', 'position_ms' => 'Pegawai Tadbir', 'position_en' => 'Administrative Officer', 'division_ms' => 'Unit Pentadbiran', 'division_en' => 'Administration Unit'],
            ['name' => 'Farah Nadia binti Ismail', 'email' => 'farah@opengovportal.example', 'department' => 'content', 'position_ms' => 'Ketua Editor', 'position_en' => 'Head of Content'],
            ['name' => 'Daniel Lim Jun Hao', 'email' => 'daniel@opengovportal.example', 'department' => 'content', 'position_ms' => 'Editor Kanan', 'position_en' => 'Senior Editor', 'division_ms' => 'Unit Siaran', 'division_en' => 'Broadcasts Unit'],
            ['name' => 'Priya Nair', 'email' => 'priya@opengovportal.example', 'department' => 'content', 'position_ms' => 'Penulis Kandungan', 'position_en' => 'Content Writer', 'division_ms' => 'Unit Terjemahan', 'division_en' => 'Translation Unit'],
            ['name' => 'Hafiz bin Kamaruddin', 'email' => 'hafiz@opengovportal.example', 'department' => 'tech', 'position_ms' => 'Ketua Kejuruteraan', 'position_en' => 'Head of Engineering'],
            ['name' => 'Tan Mei Xin', 'email' => 'meixin@opengovportal.example', 'department' => 'tech', 'position_ms' => 'Jurutera Perisian Kanan', 'position_en' => 'Senior Software Engineer', 'division_ms' => 'Unit Platform', 'division_en' => 'Platform Unit'],
            ['name' => 'Rajesh Kumar a/l Suppiah', 'email' => 'rajesh@opengovportal.example', 'department' => 'tech', 'position_ms' => 'Jurutera DevOps', 'position_en' => 'DevOps Engineer', 'division_ms' => 'Unit Infrastruktur', 'division_en' => 'Infrastructure Unit'],
            ['name' => 'Aina Sofea binti Zulkifli', 'email' => 'aina@opengovportal.example', 'department' => 'design', 'position_ms' => 'Ketua Reka Bentuk', 'position_en' => 'Head of Design'],
            ['name' => 'Marcus Wong Kah Wai', 'email' => 'marcus@opengovportal.example', 'department' => 'design', 'position_ms' => 'Pereka Pengalaman Pengguna', 'position_en' => 'UX Designer'],
            ['name' => 'Siti Aisyah binti Halim', 'email' => 'aisyah@opengovportal.example', 'department' => 'design', 'position_ms' => 'Pakar Kebolehcapaian', 'position_en' => 'Accessibility Specialist'],
            ['name' => 'Kavitha a/p Raman', 'email' => 'kavitha@opengovportal.example', 'department' => 'support', 'position_ms' => 'Ketua Sokongan', 'position_en' => 'Head of Support'],
            ['name' => 'Ahmad Firdaus bin Yusof', 'email' => 'firdaus@opengovportal.example', 'department' => 'support', 'position_ms' => 'Pegawai Latihan', 'position_en' => 'Training Officer', 'division_ms' => 'Unit Latihan', 'division_en' => 'Training Unit'],
            ['name' => 'Lee Wei Sheng', 'email' => 'weisheng@opengovportal.example', 'department' => 'support', 'position_ms' => 'Pegawai Sokongan', 'position_en' => 'Support Officer', 'is_active' => false],
        ];
    }
}
