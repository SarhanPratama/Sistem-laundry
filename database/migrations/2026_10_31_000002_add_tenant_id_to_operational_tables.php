<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
            $table->string('name')->nullable()->after('username');
            $table->string('email')->nullable()->after('name');
            $table->boolean('is_superadmin')->default(false)->after('role');
        });

        // 2. customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 3. service_categories
        Schema::table('service_categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 4. services
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 5. racks
        Schema::table('racks', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 6. orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 7. order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 8. payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 9. order_status_logs
        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 10. activity_logs
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->index();
        });

        // 11. settings
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'name', 'email', 'is_superadmin']);
        });

        $tables = [
            'customers',
            'service_categories',
            'services',
            'racks',
            'orders',
            'order_items',
            'payments',
            'order_status_logs',
            'activity_logs',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('tenant_id');
                });
            }
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};

