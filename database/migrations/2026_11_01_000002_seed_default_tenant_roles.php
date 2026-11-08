<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = [
            ['slug' => 'kasir', 'name' => 'Kasir', 'permissions' => json_encode(['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'export-data'])],
            ['slug' => 'operator', 'name' => 'Operator', 'permissions' => json_encode(['view-dashboard', 'update-order-status', 'manage-racks'])],
        ];

        foreach (DB::table('tenants')->pluck('id') as $tenantId) {
            foreach ($defaults as $role) {
                DB::table('tenant_roles')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'slug' => $role['slug']],
                    [
                        'name' => $role['name'],
                        'permissions' => $role['permissions'],
                        'is_system' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('tenant_roles')->whereIn('slug', ['kasir', 'operator'])->where('is_system', true)->delete();
    }
};
