<?php

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ─── Login ───────────────────────────────────────────────────────────────────

test('pengguna dapat login dengan kredensial yang valid', function () {
    $user = User::factory()->create([
        'username' => 'kasir1',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'username' => 'kasir1',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('login gagal dengan password yang salah', function () {
    User::factory()->create([
        'username' => 'kasir1',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'username' => 'kasir1',
        'password' => 'salah',
    ]);

    $response->assertSessionHasErrors('credentials');
    $this->assertGuest();
});

test('login gagal dengan username yang tidak ada', function () {
    $response = $this->post('/login', [
        'username' => 'tidakada',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('credentials');
    $this->assertGuest();
});

test('login gagal jika akun tidak aktif', function () {
    User::factory()->inactive()->create([
        'username' => 'kasir_nonaktif',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'username' => 'kasir_nonaktif',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('credentials');
    $this->assertGuest();
});

test('login gagal jika akun terkunci', function () {
    User::factory()->locked()->create([
        'username' => 'kasir_terkunci',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'username' => 'kasir_terkunci',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('credentials');
    $this->assertGuest();
});

// ─── Account Locking ─────────────────────────────────────────────────────────

test('akun terkunci setelah 5 kali login gagal berturut-turut', function () {
    User::factory()->create([
        'username' => 'kasir2',
        'password' => Hash::make('password123'),
        'failed_login_count' => 0,
    ]);

    // 4 kali gagal — belum terkunci
    for ($i = 0; $i < 4; $i++) {
        $this->post('/login', ['username' => 'kasir2', 'password' => 'salah']);
    }

    $user = User::where('username', 'kasir2')->first();
    expect($user->failed_login_count)->toBe(4);
    expect($user->locked_until)->toBeNull();

    // Percobaan ke-5 — akun terkunci
    $this->post('/login', ['username' => 'kasir2', 'password' => 'salah']);

    $user->refresh();
    expect($user->locked_until)->not->toBeNull();
    expect($user->locked_until->isFuture())->toBeTrue();
    expect($user->failed_login_count)->toBe(0);
});

test('login berhasil mereset failed_login_count', function () {
    User::factory()->create([
        'username' => 'kasir3',
        'password' => Hash::make('password123'),
        'failed_login_count' => 3,
    ]);

    $this->post('/login', ['username' => 'kasir3', 'password' => 'password123']);

    $user = User::where('username', 'kasir3')->first();
    expect($user->failed_login_count)->toBe(0);
    expect($user->locked_until)->toBeNull();
});

// ─── Activity Log ─────────────────────────────────────────────────────────────

test('login berhasil mencatat log aktivitas', function () {
    $user = User::factory()->create([
        'username' => 'kasir4',
        'password' => Hash::make('password123'),
    ]);

    $this->post('/login', ['username' => 'kasir4', 'password' => 'password123']);

    $log = ActivityLog::where('user_id', $user->id)
        ->where('action', 'login')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->entity_type)->toBe('User');
    expect($log->entity_id)->toBe($user->id);
});

test('login berhasil mencatat last_login', function () {
    $user = User::factory()->create([
        'username' => 'kasir5',
        'password' => Hash::make('password123'),
        'last_login' => null,
    ]);

    $this->post('/login', ['username' => 'kasir5', 'password' => 'password123']);

    $user->refresh();
    expect($user->last_login)->not->toBeNull();
});

// ─── Logout ───────────────────────────────────────────────────────────────────

test('pengguna dapat logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

// ─── Protected Routes ─────────────────────────────────────────────────────────

test('pengguna yang belum login diarahkan ke halaman login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('pengguna yang sudah login dapat mengakses dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

// ─── RBAC ─────────────────────────────────────────────────────────────────────

test('kasir tidak dapat mengakses halaman manajemen pengguna', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $kasir = User::factory()->create(['role' => 'kasir']);
    $kasir->assignRole('kasir');

    $this->actingAs($kasir)
        ->get('/users')
        ->assertStatus(403);
});

test('admin dapat mengakses halaman manajemen pengguna', function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/users')
        ->assertStatus(200);
});
