<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function dashboardUser(string $role): User
{
    Role::findOrCreate($role, 'web');

    $user = User::factory()->create(['role' => $role]);
    $user->assignRole($role);

    return $user;
}

/**
 * Ambil anchor yang membungkus card dengan label tertentu, supaya assertion
 * tidak ikut cocok dengan kartu lain atau navigasi lain di halaman.
 */
function cardAnchorWithLabel(string $html, string $label): ?string
{
    // Cocokkan <a ...> ... <p ...>LABEL</p> ... </a> (non-greedy)
    $pattern = '#<a\b[^>]*>(?:(?!</a>).)*?' . preg_quote($label, '#') . '.*?</a>#s';

    return preg_match($pattern, $html, $m) ? $m[0] : null;
}

test('kasir: tiga card statistik tertaut ke halaman order', function () {
    $html = test()
        ->actingAs(dashboardUser('kasir'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    foreach (['Total Pendapatan Hari Ini', 'Order Aktif', 'Siap Diambil'] as $label) {
        $anchor = cardAnchorWithLabel($html, $label);

        expect($anchor)->not->toBeNull("Card \"{$label}\" tidak ditemukan sebagai tautan");
        expect($anchor)->toContain('href="' . route('orders.index') . '"');
    }
});

test('kasir: card statistik tidak menuju halaman dengan filter status', function () {
    $html = test()
        ->actingAs(dashboardUser('kasir'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    foreach (['Total Pendapatan Hari Ini', 'Order Aktif', 'Siap Diambil'] as $label) {
        $anchor = cardAnchorWithLabel($html, $label);

        // "halaman order saja" — tanpa query string filter
        expect($anchor)->not->toContain('?status=');
        expect($anchor)->not->toContain('href="' . route('orders.index') . '?');
    }
});

test('kasir: label card tetap terbaca setelah jadi tautan', function () {
    $html = test()
        ->actingAs(dashboardUser('kasir'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    foreach (['Total Pendapatan Hari Ini', 'Order Aktif', 'Siap Diambil'] as $label) {
        expect(cardAnchorWithLabel($html, $label))->toContain($label);
    }
});

test('operator: card status tetap menuju halaman order terfilter', function () {
    // Guard: perubahan card kasir tidak boleh merusak card operator yang sudah terfilter
    $html = test()
        ->actingAs(dashboardUser('operator'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    $anchor = cardAnchorWithLabel($html, 'Siap Diambil');

    expect($anchor)->not->toBeNull();
    expect($anchor)->toContain('href="' . route('orders.index', ['status' => 'siap_diambil']) . '"');
});

test('tautan polos ke halaman order hanya untuk kasir', function () {
    // Guard: card admin & operator tidak boleh ikut dapat tautan polos orders.index
    $plainHref = 'href="' . route('orders.index') . '"';

    // Kasir: 3 card statistik + 1 tautan "Order" di sidebar
    $kasirHtml = test()
        ->actingAs(dashboardUser('kasir'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect(substr_count($kasirHtml, $plainHref))->toBe(4);

    // Operator: card status-nya terfilter, tidak ada satu pun card yang pakai tautan polos
    $operatorHtml = test()
        ->actingAs(dashboardUser('operator'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    foreach (['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika', 'Siap Diambil'] as $label) {
        expect(cardAnchorWithLabel($operatorHtml, $label))
            ->not->toBeNull()
            ->and(cardAnchorWithLabel($operatorHtml, $label))->not->toContain($plainHref);
    }

    // Sisa 1 tautan polos milik operator adalah quick action, bukan card
    expect(substr_count($operatorHtml, $plainHref))->toBe(1);

    // Admin: card statistik tidak jadi tautan sama sekali
    $adminHtml = test()
        ->actingAs(dashboardUser('admin'))
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    foreach (['Order Aktif', 'Siap Diambil', 'Selesai Hari Ini'] as $label) {
        expect(cardAnchorWithLabel($adminHtml, $label))
            ->toBeNull("Card \"{$label}\" admin seharusnya bukan tautan");
    }
});

test('admin: dashboard tetap merender tanpa error', function () {
    test()
        ->actingAs(dashboardUser('admin'))
        ->get(route('dashboard'))
        ->assertOk();
});
