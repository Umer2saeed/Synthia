<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
       |----------------------------------------------------------------------
       | Create admin user (firstOrCreate = safe to re-run)
       |----------------------------------------------------------------------
       */
        $admin = User::firstOrCreate(
            ['email' => 'admin@synthia.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        /*
        |----------------------------------------------------------------------
        | Assign the admin role
        |----------------------------------------------------------------------
        | assignRole() comes from the HasRoles trait you added to User.
        | syncRoles() would remove existing roles first — assignRole() only adds.
        */
        $admin->assignRole('admin');

        $this->command->info('Admin user created: admin@synthia.com / password');
    }
}
