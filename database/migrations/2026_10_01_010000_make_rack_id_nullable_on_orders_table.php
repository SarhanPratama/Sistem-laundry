<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rak baru diisi saat order berstatus "siap_diambil" dan dikosongkan lagi
     * saat order "selesai", jadi kolom ini harus nullable.
     */
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'rack_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('rack_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'rack_id')) {
            return;
        }

        if (DB::table('orders')->whereNull('rack_id')->exists()) {
            throw new RuntimeException('Ada order tanpa rak, tidak bisa dikembalikan ke NOT NULL.');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('rack_id')->nullable(false)->change();
        });
    }
};
