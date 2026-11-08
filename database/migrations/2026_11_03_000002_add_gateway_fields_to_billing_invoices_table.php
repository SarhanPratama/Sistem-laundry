<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billing_invoices')) {
            Schema::table('billing_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('billing_invoices', 'snap_token')) {
                    $table->string('snap_token')->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('billing_invoices', 'payment_url')) {
                    $table->string('payment_url')->nullable()->after('snap_token');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billing_invoices')) {
            Schema::table('billing_invoices', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('billing_invoices', 'snap_token')) {
                    $cols[] = 'snap_token';
                }
                if (Schema::hasColumn('billing_invoices', 'payment_url')) {
                    $cols[] = 'payment_url';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

