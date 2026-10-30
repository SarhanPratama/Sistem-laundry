<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles first (Spatie)
        $this->call(RoleSeeder::class);

        // 2. Users (admin + default kasir)
        $this->call(UserSeeder::class);

        // 3. Master data
        $this->call(ServiceCategorySeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(RackSeeder::class);
        $this->call(CustomerSeeder::class);
        // Remove EmployeeSeeder
    }
}
