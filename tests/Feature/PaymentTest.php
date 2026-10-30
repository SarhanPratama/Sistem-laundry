<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeOrder(float $totalCost): Order
{
    $customer = Customer::create([
        'full_name'    => 'Budi Santoso',
        'phone_number' => '0811' . random_int(10000000, 99999999),
        'is_active'    => true,
    ]);
    $service  = Service::create([
        'name'           => 'Cuci Kiloan',
        'unit'           => 'kg',
        'price_per_unit' => $totalCost,
        'is_active'      => true,
    ]);

    $order = Order::create([
        'order_number'        => 'LDR-TEST-0001',
        'customer_id'         => $customer->id,
        'created_by'          => User::factory()->create()->id,
        'status'              => 'diterima',
        'payment_status'      => 'belum_bayar',
        'total_cost'          => $totalCost,
        'amount_paid'         => 0,
        'received_date'       => today(),
        'estimated_done_date' => today()->addDay(),
        'rack_id'             => null,
    ]);

    $order->items()->create([
        'service_id' => $service->id,
        'quantity'   => 1,
        'unit_price' => $totalCost,
        'subtotal'   => $totalCost,
    ]);

    return $order->fresh();
}

// ─── Kembalian hanya untuk tunai ─────────────────────────────────────────────

test('pembayaran tunai melebihi tagihan menyimpan kembalian dan tidak menambah amount_paid penuh', function () {
    $kasir  = User::factory()->create();
    $order  = makeOrder(80000);
    $this->actingAs($kasir);

    $payment = app(PaymentService::class)->recordPayment($order, [
        'method' => 'tunai',
        'amount' => 100000,
    ]);

    // Uang diterima pelanggan & kembalian tersimpan
    expect((float) $payment->amount)->toBe(100000.0)
        ->and((float) $payment->change_amount)->toBe(20000.0)
        ->and((int) $payment->recorded_by)->toBe($kasir->id);

    // amount_paid hanya bertambah sebesar nominal bersih
    $order->refresh();
    expect((float) $order->amount_paid)->toBe(80000.0)
        ->and($order->payment_status)->toBe('lunas');
});

test('pembayaran transfer melebihi tagihan tidak menghasilkan kembalian', function () {
    $this->actingAs(User::factory()->create());
    $order = makeOrder(80000);

    $payment = app(PaymentService::class)->recordPayment($order, [
        'method'   => 'transfer',
        'amount'   => 100000,
        'is_dp'    => false,
        'confirmed' => true,
    ]);

    expect((float) $payment->change_amount)->toBe(0.0);

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(100000.0)
        ->and($order->payment_status)->toBe('lunas');
});

test('pembayaran tepat tanpa kembalian', function () {
    $this->actingAs(User::factory()->create());
    $order = makeOrder(50000);

    $payment = app(PaymentService::class)->recordPayment($order, [
        'method' => 'tunai',
        'amount' => 50000,
    ]);

    expect((float) $payment->change_amount)->toBe(0.0);

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(50000.0)
        ->and($order->payment_status)->toBe('lunas');
});

test('kembalian pada pembayaran kedua dihitung dari sisa tagihan saja', function () {
    $this->actingAs(User::factory()->create());
    $order = makeOrder(100000);
    $service = app(PaymentService::class);

    // DP 30.000 tunai, tanpa kembalian
    $service->recordPayment($order, ['method' => 'tunai', 'amount' => 30000, 'is_dp' => true]);

    // Pelanggan menyetor 100.000 untuk sisa 70.000 -> kembalian 30.000
    $payment = $service->recordPayment($order->fresh(), ['method' => 'tunai', 'amount' => 100000]);

    expect((float) $payment->change_amount)->toBe(30000.0);

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(100000.0)
        ->and($order->payment_status)->toBe('lunas')
        ->and((float) $order->total_tendered)->toBe(130000.0)
        ->and((float) $order->total_change)->toBe(30000.0)
        ->and((float) $order->remaining_amount)->toBe(0.0);
});

test('pembayaran sebagian tidak menghasilkan kembalian dan status jadi dp', function () {
    $this->actingAs(User::factory()->create());
    $order = makeOrder(80000);

    $payment = app(PaymentService::class)->recordPayment($order, [
        'method' => 'tunai',
        'amount' => 30000,
        'is_dp'  => true,
    ]);

    expect((float) $payment->change_amount)->toBe(0.0);

    $order->refresh();
    expect((float) $order->amount_paid)->toBe(30000.0)
        ->and($order->payment_status)->toBe('dp')
        ->and((float) $order->remaining_amount)->toBe(50000.0);
});

// ─── Agregat di model Order ───────────────────────────────────────────────────

test('order tanpa pembayaran memiliki agregat nol', function () {
    $order = makeOrder(75000)->load('payments');

    expect((float) $order->total_tendered)->toBe(0.0)
        ->and((float) $order->total_change)->toBe(0.0)
        ->and((float) $order->remaining_amount)->toBe(75000.0);
});

test('total kembalian menjumlah seluruh transaksi', function () {
    $this->actingAs(User::factory()->create());
    $order  = makeOrder(100000);
    $service = app(PaymentService::class);

    $service->recordPayment($order, ['method' => 'tunai', 'amount' => 60000]);
    $service->recordPayment($order->fresh(), ['method' => 'tunai', 'amount' => 60000]);

    expect(Payment::where('order_id', $order->id)->count())->toBe(2);

    $order->refresh()->load('payments');
    expect((float) $order->total_tendered)->toBe(120000.0)
        ->and((float) $order->total_change)->toBe(20000.0)
        ->and((float) $order->amount_paid)->toBe(100000.0);
});

// ─── Tampilan di detail order ────────────────────────────────────────────────

test('halaman detail order menampilkan jumlah bayar dan kembalian', function () {
    $kasir = User::factory()->create();
    $order = makeOrder(80000);

    $this->actingAs($kasir);

    app(PaymentService::class)->recordPayment($order, [
        'method' => 'tunai',
        'amount' => 100000,
    ]);

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Jumlah Bayar')
        ->assertSee('Kembalian')
        ->assertSee('Diterapkan')
        ->assertSee('100.000') // jumlah bayar (uang diterima)
        ->assertSee('20.000')  // kembalian
        ->assertSee('80.000'); // total tagihan / nilai diterapkan
});

test('halaman detail pembayaran menampilkan kembalian per transaksi', function () {
    $kasir = User::factory()->create();
    $order = makeOrder(80000);

    $this->actingAs($kasir);

    app(PaymentService::class)->recordPayment($order, [
        'method' => 'tunai',
        'amount' => 100000,
    ]);

    $this->get(route('payments.index', $order))
        ->assertOk()
        ->assertSee('Kembalian: Rp 20.000');
});
