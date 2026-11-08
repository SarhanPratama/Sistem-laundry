<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Rack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function userWithRole(string $role): User
{
    Role::findOrCreate($role, 'web');

    $user = User::factory()->create(['role' => $role]);
    $user->assignRole($role);

    return $user;
}

function orderAtStatus(string $status, string $paymentStatus = 'belum_bayar', ?Rack $rack = null): Order
{
    $customer = Customer::create([
        'full_name'    => 'Siti Rahayu',
        'phone_number' => '0812' . random_int(10000000, 99999999),
        'is_active'    => true,
    ]);

    return Order::create([
        'order_number'        => 'LDR-TEST-' . strtoupper(substr(md5($status . microtime(true)), 0, 6)),
        'customer_id'         => $customer->id,
        'created_by'          => userWithRole('kasir')->id,
        'status'              => $status,
        'payment_status'      => $paymentStatus,
        'total_cost'          => 50000,
        'amount_paid'         => $paymentStatus === 'lunas' ? 50000 : 0,
        'received_date'       => today(),
        'estimated_done_date' => today()->addDay(),
        'rack_id'             => $rack?->id,
        'ready_at'            => $status === 'siap_diambil' ? now() : null,
    ]);
}

// ─── Produksi tetap milik operator ───────────────────────────────────────────

test('operator bisa Advance status produksi', function () {
    $order = orderAtStatus('dicuci');
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);

    $this->actingAs(userWithRole('operator'))
        ->patch(route('orders.status.update', $order), [
            'status'  => 'dikeringkan',
            'rack_id' => $rack->id,
        ])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('dikeringkan');
});

test('kasir tidak bisa Advance status produksi', function () {
    $order = orderAtStatus('dicuci');

    $this->actingAs(userWithRole('kasir'))
        ->patch(route('orders.status.update', $order), ['status' => 'dikeringkan'])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('dicuci');
});

// ─── Pengambilan barang hanya milik kasir ────────────────────────────────────

test('kasir bisa menutup order dari siap_diambil ke selesai', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);
    $kasir = userWithRole('kasir');

    $this->actingAs($kasir)
        ->patch(route('orders.status.update', $order), ['status' => 'selesai'])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('selesai')
        ->and($order->rack_id)->toBeNull()
        ->and((int) $order->completed_by)->toBe($kasir->id)
        ->and($order->completed_at)->not->toBeNull();

    expect($order->statusLogs->pluck('status')->all())->toContain('selesai');
});

test('operator ditolak saat mencoba menutup order menjadi selesai', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);

    $this->actingAs(userWithRole('operator'))
        ->patch(route('orders.status.update', $order), ['status' => 'selesai'])
        ->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('siap_diambil')
        ->and($order->rack_id)->toBe($rack->id)
        ->and($order->completed_at)->toBeNull();
});

test('admin dapat menutup order menjadi selesai', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);

    $this->actingAs(userWithRole('admin'))
        ->patch(route('orders.status.update', $order), ['status' => 'selesai'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('selesai');
});

test('operator tidak bisa melompati tahap produksi', function () {
    $order = orderAtStatus('diterima');

    $this->actingAs(userWithRole('operator'))
        ->from(route('orders.status.update', $order))
        ->patch(route('orders.status.update', $order), ['status' => 'disetrika'])
        ->assertRedirect(route('orders.status.update', $order))
        ->assertSessionHas('error');

    expect($order->fresh()->status)->toBe('diterima');
});

test('kasir tidak bisa menutup order yang belum lunas', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'dp', $rack);

    $this->actingAs(userWithRole('kasir'))
        ->patch(route('orders.status.update', $order), ['status' => 'selesai'])
        ->assertRedirect(route('payments.index', $order))
        ->assertSessionHas('error');

    expect($order->fresh()->status)->toBe('siap_diambil');
});

// ─── Tampilan tombol aksi ────────────────────────────────────────────────────

test('operator tidak melihat tombol Update Status saat order siap diambil', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);

    $this->actingAs(userWithRole('operator'))
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertDontSee('Barang Diambil & Selesaikan')
        ->assertSee('dilakukan kasir saat pelanggan');
});

test('kasir melihat tombol Barang Diambil saat order siap diambil', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);

    $this->actingAs(userWithRole('kasir'))
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Barang Diambil & Selesaikan')
        ->assertDontSee('Update Status');
});

test('kasir melihat peringatan lunas sebelum menutup order', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'dp', $rack);

    $this->actingAs(userWithRole('kasir'))
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Pembayaran belum lunas');
});

// ─── Admin memantau histori, read-only ───────────────────────────────────────

/**
 * Ambil isi <nav> sidebar saja, supaya assertion tidak ikut cocok dengan
 * link di body halaman (mis. KPI card dashboard) atau form filter.
 */
function sidebarFor(User $user): string
{
    $html = test()
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    preg_match('/<nav\b.*?<\/nav>/s', $html, $matches);

    return $matches[0] ?? '';
}

test('admin dapat membuka halaman pelacakan status', function () {
    $this->actingAs(userWithRole('admin'))
        ->get(route('orders.status'))
        ->assertOk();
});

test('admin melihat link pelacakan status di sidebar', function () {
    $sidebar = sidebarFor(userWithRole('admin'));

    expect($sidebar)
        ->toContain('href="' . route('orders.status') . '"')
        ->toContain('Pelacakan Status');
});

test('operator dan kasir tetap melihat link pelacakan status di sidebar', function () {
    foreach (['kasir', 'operator'] as $role) {
        expect(sidebarFor(userWithRole($role)))
            ->toContain('href="' . route('orders.status') . '"');
    }
});

test('sidebar admin menampilkan tautan manajemen dan dashboard', function () {
    $sidebar = sidebarFor(userWithRole('admin'));

    expect($sidebar)
        ->toContain('href="' . route('dashboard') . '"')
        ->toContain('href="' . route('service-categories.index') . '"');
});

test('sidebar admin memuat tautan order di dalam navigasi', function () {
    $sidebar = sidebarFor(userWithRole('admin'));

    expect($sidebar)
        ->toContain('<nav')
        ->toContain('href="' . route('orders.index') . '"');
});

test('admin dapat melihat histori order yang sudah selesai', function () {
    $order = orderAtStatus('selesai', 'lunas');

    $this->actingAs(userWithRole('admin'))
        ->get(route('orders.status', ['status' => 'selesai']))
        ->assertOk()
        ->assertSee($order->order_number);
});

test('operator dan kasir dapat melihat histori order selesai di tabel pelacakan', function () {
    $order = orderAtStatus('selesai', 'lunas');

    $this->actingAs(userWithRole('kasir'))
        ->get(route('orders.status'))
        ->assertOk()
        ->assertSee($order->order_number);
});

test('halaman pelacakan tidak menampilkan banner read-only untuk admin', function () {
    $this->actingAs(userWithRole('admin'))
        ->get(route('orders.status'))
        ->assertOk()
        ->assertDontSee('Mode pemantauan');
});

test('halaman pelacakan tidak menampilkan banner read-only untuk kasir', function () {
    $this->actingAs(userWithRole('kasir'))
        ->get(route('orders.status'))
        ->assertOk()
        ->assertDontSee('Mode pemantauan');
});

test('halaman pelacakan menampilkan tombol aksi untuk admin', function () {
    $rack  = Rack::create(['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas', 'is_active' => true]);
    $order = orderAtStatus('siap_diambil', 'lunas', $rack);

    $html = $this->actingAs(userWithRole('admin'))
        ->get(route('orders.status'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain(route('orders.status.update', $order));
});

test('admin dapat mengubah tahap produksi', function () {
    $order = orderAtStatus('dicuci');

    $this->actingAs(userWithRole('admin'))
        ->patch(route('orders.status.update', $order), ['status' => 'dikeringkan'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('dikeringkan');
});

test('informasi lokasi rak selalu tampil jelas di detail order dan pelacakan status serta dapat diubah saat siap diambil', function () {
    $rackA = Rack::create(['name' => 'A1', 'location_description' => 'Rak Depan Atas', 'is_active' => true]);
    $rackB = Rack::create(['name' => 'B2', 'location_description' => 'Rak Tengah Bawah', 'is_active' => true]);

    $orderProcessing = orderAtStatus('disetrika');
    $orderReady = orderAtStatus('siap_diambil', 'lunas', $rackA);

    $operator = userWithRole('operator');

    // Detail order yang masih diproses tetap menampilkan status lokasi rak
    $this->actingAs($operator)
        ->get(route('orders.show', $orderProcessing))
        ->assertOk()
        ->assertSee('Lokasi Rak: Belum ditempatkan');

    // Halaman pelacakan status menampilkan dropdown pilih rak untuk order yang berstatus disetrika
    $this->actingAs($operator)
        ->get(route('orders.status'))
        ->assertOk()
        ->assertSee('Pilih Rak')
        ->assertSee('Rak A1');

    // Pindah / ubah lokasi rak saat order berstatus siap_diambil
    $this->actingAs($operator)
        ->patch(route('orders.rack.update', $orderReady), [
            'rack_id' => $rackB->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($orderReady->fresh()->rack_id)->toBe($rackB->id);
});
