<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        /*
       |----------------------------------------------------------------------
       | ORDER MATTERS here.
       |----------------------------------------------------------------------
       | RolesAndPermissionsSeeder must run before AdminUserSeeder because
       | AdminUserSeeder assigns the admin role — which must already exist.
       */
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
