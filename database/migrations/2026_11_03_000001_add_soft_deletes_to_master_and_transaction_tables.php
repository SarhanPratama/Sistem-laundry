<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'customers',
            'services',
            'service_categories',
            'racks',
            'users',
            'orders',
            'payments',
            'tenants',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        // Replace strict DB-level unique constraints on customers & racks with regular indexes
        // so soft-deleted records do not block recreating active records with the same phone/name.
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'phone_number']);
                $table->index(['tenant_id', 'phone_number']);
            });
        }

        if (Schema::hasTable('racks')) {
            Schema::table('racks', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->index(['tenant_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('racks')) {
            Schema::table('racks', function (Blueprint $table) {
                $table->dropIndex(['tenant_id', 'name']);
                $table->unique(['name']);
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['tenant_id', 'phone_number']);
                $table->unique(['tenant_id', 'phone_number']);
            });
        }

        $tables = [
            'tenants',
            'payments',
            'orders',
            'users',
            'racks',
            'service_categories',
            'services',
            'customers',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};

