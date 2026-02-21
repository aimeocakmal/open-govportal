<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@digital.gov.my',
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Content Editor',
                'email' => 'editor@digital.gov.my',
                'password' => Hash::make('password'),
                'department' => 'Bahagian Teknologi',
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'content_editor',
            ],
            [
                'name' => 'Publisher',
                'email' => 'publisher@digital.gov.my',
                'password' => Hash::make('password'),
                'is_active' => true,
                'preferred_locale' => 'ms',
                'email_verified_at' => now(),
                'role' => 'publisher',
            ],
            [
                'name' => 'Viewer',
                'email' => 'viewer@digital.gov.my',
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
