<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman utama mengarahkan ke login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('halaman login dapat diakses', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});
