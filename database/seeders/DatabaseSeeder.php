<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles first (Spatie)
        $this->call(RoleSeeder::class);

        // 2. SaaS Plans
        $this->call(PlanSeeder::class);

        // 3. Users (admin + default kasir + demo tenant)
        $this->call(UserSeeder::class);

        // Set context to demo tenant for remaining seeders
        $demoTenant = \App\Models\Tenant::where('slug', 'laundry-mawar')->first();
        if ($demoTenant) {
            \App\Services\TenantContext::setTenant($demoTenant);
        }

        // 4. Master data
        $this->call(ServiceCategorySeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(RackSeeder::class);
        $this->call(CustomerSeeder::class);

        \App\Services\TenantContext::clear();
        // Remove EmployeeSeeder
    }
}
