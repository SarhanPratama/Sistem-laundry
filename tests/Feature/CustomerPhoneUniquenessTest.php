<?php

/**
 * Property Test: Properti 5 — Keunikan Nomor Telepon Pelanggan
 *
 * Untuk sembarang nomor telepon yang sudah terdaftar dalam sistem,
 * mencoba mendaftarkan pelanggan baru dengan nomor telepon yang sama
 * harus selalu gagal dengan pesan error yang sesuai, dan tidak ada
 * pelanggan baru yang tersimpan.
 *
 * Validates: Requirements 2.2
 */

use App\Models\Customer;
use App\Models\User;
use Eris\Generators;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class, Eris\TestTrait::class);

/**
 * Properti 5: Keunikan Nomor Telepon Pelanggan
 *
 * **Validates: Requirements 2.2**
 */
test('Properti 5: mendaftarkan pelanggan dengan nomor telepon yang sudah ada harus selalu gagal', function () {
    $user = User::factory()->create();

    $this->limitTo(100);

    // Generate nomor telepon: prefix "08" + 8 digit angka (total 10 karakter, valid & unik)
    $this->forAll(
        Generators::map(
            fn (int $n) => '08' . str_pad((string) abs($n % 100000000), 8, '0', STR_PAD_LEFT),
            Generators::int()
        )
    )->then(function (string $phoneNumber) use ($user) {
        // Buat pelanggan pertama dengan nomor telepon ini
        Customer::create([
            'full_name'    => 'Pelanggan Pertama',
            'phone_number' => $phoneNumber,
            'address'      => 'Jl. Contoh No. 1',
            'is_active'    => true,
        ]);

        $countSebelum = Customer::count();

        // Coba daftarkan pelanggan kedua dengan nomor telepon yang sama
        $response = $this->actingAs($user)->post('/customers', [
            'full_name'    => 'Pelanggan Kedua',
            'phone_number' => $phoneNumber,
            'address'      => 'Jl. Contoh No. 2',
        ]);

        // Harus ada error validasi untuk phone_number
        $response->assertSessionHasErrors('phone_number');

        // Jumlah pelanggan tidak boleh bertambah
        $countSesudah = Customer::count();
        $this->assertEquals(
            $countSebelum,
            $countSesudah,
            "Pelanggan duplikat tersimpan untuk nomor telepon: {$phoneNumber}"
        );

        // Bersihkan untuk iterasi berikutnya
        Customer::where('phone_number', $phoneNumber)->delete();
    });
});
