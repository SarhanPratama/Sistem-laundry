<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman utama menampilkan company profile untuk tamu', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Kelola Laundry Lebih Mudah');
});

test('halaman login dapat diakses', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('halaman 404 menggunakan tampilan error Laundiqo', function () {
    $response = $this->get('/halaman-yang-tidak-tersedia');

    $response->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('Laundiqo');
});
