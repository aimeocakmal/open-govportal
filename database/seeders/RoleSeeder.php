<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles that map to the Spatie permission roles used throughout the admin panel.
     *
     * super_admin  — full access (set on the admin user created in Week 1)
     * content_editor — can create/edit content; cannot publish
     * publisher    — can publish/unpublish content; cannot delete
     * viewer       — read-only access to Filament resources
     */
    public function run(): void
    {
        foreach (['super_admin', 'content_editor', 'publisher', 'viewer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
