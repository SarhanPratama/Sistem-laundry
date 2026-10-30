<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeReportOrder(array $attributes = []): Order
{
    $customer = Customer::create([
        'full_name' => 'Pelanggan Laporan',
        'phone_number' => '0822'.random_int(10000000, 99999999),
        'is_active' => true,
    ]);

    return Order::create(array_merge([
        'order_number' => 'LDR-RPT-'.random_int(100000, 999999),
        'customer_id' => $customer->id,
        'created_by' => User::factory()->create()->id,
        'status' => 'diterima',
        'payment_status' => 'belum_bayar',
        'total_cost' => 0,
        'amount_paid' => 0,
        'received_date' => '2026-10-28',
        'estimated_done_date' => '2026-10-29',
        'rack_id' => null,
    ], $attributes));
}

test('DP dan pelunasan tercatat sebagai kas pada tanggal pembayaran masing-masing', function () {
    $order = makeReportOrder([
        'total_cost' => 100000,
        'amount_paid' => 100000,
        'payment_status' => 'lunas',
        'received_date' => '2026-10-28',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'recorded_by' => User::factory()->create()->id,
        'method' => 'tunai',
        'amount' => 30000,
        'change_amount' => 0,
        'is_dp' => true,
        'paid_at' => '2026-10-28 10:00:00',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'recorded_by' => User::factory()->create()->id,
        'method' => 'tunai',
        'amount' => 70000,
        'change_amount' => 0,
        'is_dp' => false,
        'paid_at' => '2026-10-30 15:00:00',
    ]);

    $service = app(ReportService::class);

    $orderValue = $service->getOrderValueReport('daily', '2026-10-28', '2026-10-30');
    $cashFlow = $service->getCashFlowReport('daily', '2026-10-28', '2026-10-30');

    // Nilai order tetap satu nilai penuh pada tanggal order masuk.
    expect($orderValue['total'])->toBe(100000.0)
        ->and($orderValue['data'])->toBe(['2026-10-28' => 100000.0]);

    // Kas diterima mengikuti tanggal pembayaran, bukan tanggal order.
    expect($cashFlow['data'])->toBe([
        '2026-10-28' => 30000.0,
        '2026-10-30' => 70000.0,
    ])->and($cashFlow['total'])->toBe(100000.0);
});

test('piutang menghitung sisa tagihan order yang belum lunas', function () {
    makeReportOrder([
        'total_cost' => 50000,
        'amount_paid' => 20000,
        'payment_status' => 'dp',
        'received_date' => '2026-10-29',
    ]);

    makeReportOrder([
        'total_cost' => 80000,
        'amount_paid' => 80000,
        'payment_status' => 'lunas',
        'received_date' => '2026-10-29',
    ]);

    $receivables = app(ReportService::class)
        ->getReceivablesReport('2026-10-01', '2026-10-31');

    expect($receivables['total_billed'])->toBe(130000.0)
        ->and($receivables['total_paid'])->toBe(100000.0)
        ->and($receivables['outstanding'])->toBe(30000.0)
        ->and($receivables['orders_with_balance'])->toBe(1);
});

test('order yang dibatalkan tidak masuk nilai order, kas, maupun piutang', function () {
    $order = makeReportOrder([
        'total_cost' => 60000,
        'amount_paid' => 60000,
        'payment_status' => 'lunas',
        'is_cancelled' => true,
        'received_date' => '2026-10-29',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'recorded_by' => User::factory()->create()->id,
        'method' => 'tunai',
        'amount' => 60000,
        'change_amount' => 0,
        'is_dp' => false,
        'paid_at' => '2026-10-29 09:00:00',
    ]);

    $service = app(ReportService::class);

    expect($service->getOrderValueReport('daily', '2026-10-01', '2026-10-31')['total'])->toBe(0.0)
        ->and($service->getCashFlowReport('daily', '2026-10-01', '2026-10-31')['total'])->toBe(0.0)
        ->and($service->getReceivablesReport('2026-10-01', '2026-10-31')['outstanding'])->toBe(0.0);
});

test('halaman laporan menampilkan metrik nilai order, kas diterima, dan piutang', function () {
    Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('admin');

    test()
        ->actingAs($admin)
        ->get(route('reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]))
        ->assertOk()
        ->assertSee('Nilai Order')
        ->assertSee('Kas Diterima')
        ->assertSee('Piutang')
        ->assertDontSee('Total Pendapatan');
});
