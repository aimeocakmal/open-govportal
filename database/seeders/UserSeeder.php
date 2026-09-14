<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const SUPER_ADMIN_EMAIL = 'admin@opengovportal.example';

    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => self::SUPER_ADMIN_EMAIL,
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Content Editor',
                'email' => 'editor@opengovportal.example',
                'password' => Hash::make('password'),
                'department' => 'Kandungan & Editorial',
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'content_editor',
            ],
            [
                'name' => 'Publisher',
                'email' => 'publisher@opengovportal.example',
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'publisher',
            ],
            [
                'name' => 'Viewer',
                'email' => 'viewer@opengovportal.example',
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'en',
                'email_verified_at' => now(),
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData,
            );

            $user->assignRole($role);
        }
    }
}
