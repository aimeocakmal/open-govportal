<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Content types that have CRUD permissions
        $contentTypes = [
            'broadcasts',
            'achievements',
            'celebrations',
            'staff_directories',
            'policies',
            'files',
            'hero_banners',
            'quick_links',
            'media',
            'feedbacks',
            'search_overrides',
            'static_pages',
            'page_categories',
        ];

        // Create content permissions
        $operations = ['view', 'create', 'edit', 'delete', 'publish'];

        foreach ($contentTypes as $type) {
            foreach ($operations as $op) {
                Permission::firstOrCreate([
                    'name' => "{$op}_{$type}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Additional system permissions
        $systemPermissions = [
            'manage_users',
            'manage_roles',
            'manage_settings',
            'manage_email_settings',
            'manage_ai_settings',
        ];

        foreach ($systemPermissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        // Create all 6 roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $departmentAdmin = Role::firstOrCreate(['name' => 'department_admin', 'guard_name' => 'web']);
        $contentEditor = Role::firstOrCreate(['name' => 'content_editor', 'guard_name' => 'web']);
        $contentAuthor = Role::firstOrCreate(['name' => 'content_author', 'guard_name' => 'web']);
        $publisher = Role::firstOrCreate(['name' => 'publisher', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // super_admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());

        // department_admin: all content operations for their department
        $departmentAdmin->syncPermissions(
            Permission::whereIn('name', collect($contentTypes)->flatMap(
                fn ($type) => collect($operations)->map(fn ($op) => "{$op}_{$type}")
            )->all())->get()
        );

        // content_editor: view, create, edit (all content — cannot publish or delete)
        $contentEditor->syncPermissions(
            Permission::whereIn('name', collect($contentTypes)->flatMap(
                fn ($type) => ["view_{$type}", "create_{$type}", "edit_{$type}"]
            )->all())->get()
        );

        // content_author: view, create (own content only — enforced at app layer)
        $contentAuthor->syncPermissions(
            Permission::whereIn('name', collect($contentTypes)->flatMap(
                fn ($type) => ["view_{$type}", "create_{$type}"]
            )->all())->get()
        );

        // publisher: view + publish (review and publish content from editors/authors)
        $publisher->syncPermissions(
            Permission::whereIn('name', collect($contentTypes)->flatMap(
                fn ($type) => ["view_{$type}", "publish_{$type}"]
            )->all())->get()
        );

        // viewer: read-only
        $viewer->syncPermissions(
            Permission::whereIn('name', collect($contentTypes)->map(
                fn ($type) => "view_{$type}"
            )->all())->get()
        );
    }
}
