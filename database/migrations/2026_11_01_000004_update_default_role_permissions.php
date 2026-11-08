<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenant_roles')->where('slug', 'kasir')->update([
            'permissions' => json_encode(['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'update-order-status', 'export-data']),
            'updated_at' => now(),
        ]);
        DB::table('tenant_roles')->where('slug', 'operator')->update([
            'permissions' => json_encode(['view-dashboard', 'manage-orders', 'update-order-status', 'manage-racks']),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        //
    }
};
