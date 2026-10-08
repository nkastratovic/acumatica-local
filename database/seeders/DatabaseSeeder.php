<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Users are not seeded; create the first administrator with
     *   php artisan app:create-user admin@example.com --role=admin
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
    }
}
