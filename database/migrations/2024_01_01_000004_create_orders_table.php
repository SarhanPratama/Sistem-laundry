<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('rack_id')->nullable()->constrained('racks');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil', 'selesai'])->default('diterima');
            $table->enum('payment_status', ['belum_bayar', 'dp', 'lunas'])->default('belum_bayar');
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('received_date');
            $table->date('estimated_done_date');
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
