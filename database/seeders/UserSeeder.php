<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Http\Controllers\TenantRoleController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Demo Tenant (Laundry Mawar)
        $tenant = Tenant::firstOrCreate(['slug' => 'laundry-mawar'], [
            'name'          => 'Laundry Mawar',
            'phone'         => '081234567890',
            'address'       => 'Jl. Mawar No. 12, Jakarta',
            'status'        => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Attach Business Subscription
        $businessPlan = Plan::where('slug', 'business')->first();
        if ($businessPlan) {
            Subscription::firstOrCreate(['tenant_id' => $tenant->id], [
                'plan_id'       => $businessPlan->id,
                'billing_cycle' => 'monthly',
                'status'        => 'active',
                'starts_at'     => now(),
                'ends_at'       => now()->addDays(30),
            ]);
        }
        TenantRoleController::ensureDefaults($tenant->id);

        // 2. Platform Superadmin
        $superadmin = User::firstOrCreate(['username' => 'superadmin'], [
            'name'               => 'Super Admin SaaS',
            'email'              => 'superadmin@laundrysaas.test',
            'password'           => Hash::make('password'),
            'role'               => 'admin',
            'is_superadmin'      => true,
            'tenant_id'          => null,
            'is_active'          => true,
            'failed_login_count' => 0,
        ]);
        $superadmin->assignRole('admin');

        // 3. Laundry Staff Users
        $users = [
            [
                'username'           => 'admin',
                'name'               => 'Pemilik Laundry Mawar',
                'email'              => 'admin@mawar.test',
                'password'           => Hash::make('password'),
                'role'               => 'admin',
                'tenant_id'          => $tenant->id,
                'is_active'          => true,
                'failed_login_count' => 0,
                'role_name'          => 'admin',
            ],
            [
                'username'           => 'kasir',
                'name'               => 'Kasir Mawar',
                'email'              => 'kasir@mawar.test',
                'password'           => Hash::make('password'),
                'role'               => 'kasir',
                'tenant_id'          => $tenant->id,
                'is_active'          => true,
                'failed_login_count' => 0,
                'role_name'          => 'kasir',
            ],
            [
                'username'           => 'operator',
                'name'               => 'Operator Cuci Mawar',
                'email'              => 'operator@mawar.test',
                'password'           => Hash::make('password'),
                'role'               => 'operator',
                'tenant_id'          => $tenant->id,
                'is_active'          => true,
                'failed_login_count' => 0,
                'role_name'          => 'operator',
            ],
        ];

        foreach ($users as $data) {
            $roleName = $data['role_name'];
            unset($data['role_name']);

            $user = User::firstOrCreate(['username' => $data['username']], $data);
            $user->assignRole($roleName);

            if ($data['role'] === 'admin') {
                $tenant->update(['owner_id' => $user->id]);
            }
        }
    }
}
