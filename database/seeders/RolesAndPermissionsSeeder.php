<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
       |----------------------------------------------------------------------
       | Reset cached roles and permissions
       |----------------------------------------------------------------------
       | Spatie caches everything in memory. We reset it first so this seeder
       | always works cleanly whether you're running it fresh or re-running it.
       */
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |----------------------------------------------------------------------
        | Define all permissions
        |----------------------------------------------------------------------
        | Permission::firstOrCreate() means: create if not exists, skip if it
        | already exists. Safe to re-run the seeder without duplicates.
        */
        $permissions = [
            // Post permissions
            'view posts',
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',

            // Category permissions
            'view categories',
            'manage categories',

            // Comment permissions
            'view comments',
            'create comments',
            'delete comments',

            // User & role management (admin only)
            'manage users',
            'manage roles',

            // Panel access
            'access admin panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |----------------------------------------------------------------------
        | Create Roles and assign permissions
        |----------------------------------------------------------------------
        | Role::firstOrCreate() — safe to re-run.
        | givePermissionTo() — assigns listed permissions to the role.
        */

        // ADMIN — full control over everything
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all()); // admin gets every permission

        // EDITOR — manages all content but cannot manage users or roles
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'view posts',
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',
            'view categories',
            'manage categories',
            'view comments',
            'create comments',
            'delete comments',
            'access admin panel',
        ]);

        // AUTHOR — creates and manages only their own content
        $author = Role::firstOrCreate(['name' => 'author']);
        $author->syncPermissions([
            'view posts',
            'create posts',
            'edit own posts',
            'delete own posts',
            'view categories',
            'view comments',
            'create comments',
            'access admin panel',
        ]);

        // READER — read-only, cannot access admin panel
        $reader = Role::firstOrCreate(['name' => 'reader']);
        $reader->syncPermissions([
            'view posts',
            'view categories',
            'view comments',
            'create comments',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
