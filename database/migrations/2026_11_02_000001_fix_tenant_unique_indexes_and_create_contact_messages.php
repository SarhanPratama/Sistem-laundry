<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix settings unique index from global key to (tenant_id, key)
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['key']);
                $table->unique(['tenant_id', 'key']);
            });
        }

        // 2. Fix customers unique index from global phone_number to (tenant_id, phone_number)
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique(['phone_number']);
                $table->unique(['tenant_id', 'phone_number']);
            });
        }

        // 3. Create contact_messages table for marketing leads
        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact');
                $table->string('business_name')->nullable();
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'phone_number']);
                $table->unique(['phone_number']);
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'key']);
                $table->unique(['key']);
            });
        }
    }
};

