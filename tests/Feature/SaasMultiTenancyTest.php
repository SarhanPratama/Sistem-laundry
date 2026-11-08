<?php

use App\Models\BillingInvoice;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Rack;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantRole;
use App\Models\User;
use App\Http\Controllers\TenantRoleController;
use App\Services\TenantContext;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
    $this->seed(RoleSeeder::class);
    $this->seed(PlanSeeder::class);
});

test('multi-tenant: data pelanggan dan order terisolasi antar-tenant', function () {
    // Tenant 1 (Laundry Mawar)
    $tenantA = Tenant::create([
        'name'          => 'Laundry Mawar',
        'slug'          => 'laundry-mawar',
        'status'        => 'active',
        'trial_ends_at' => now()->addDays(14),
    ]);
    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role'      => 'admin',
    ]);
    $userA->assignRole('admin');

    // Tenant 2 (Laundry Melati)
    $tenantB = Tenant::create([
        'name'          => 'Laundry Melati',
        'slug'          => 'laundry-melati',
        'status'        => 'active',
        'trial_ends_at' => now()->addDays(14),
    ]);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'role'      => 'admin',
    ]);
    $userB->assignRole('admin');

    // Create Customer under Tenant A
    TenantContext::setTenant($tenantA);
    $custA = Customer::create([
        'tenant_id'    => $tenantA->id,
        'full_name'    => 'Pelanggan Mawar',
        'phone_number' => '0811111111',
        'is_active'    => true,
    ]);

    // Create Customer under Tenant B
    TenantContext::setTenant($tenantB);
    $custB = Customer::create([
        'tenant_id'    => $tenantB->id,
        'full_name'    => 'Pelanggan Melati',
        'phone_number' => '0822222222',
        'is_active'    => true,
    ]);
    TenantContext::clear();

    // Acting as Tenant A: only see Pelanggan Mawar
    $responseA = $this->actingAs($userA)->get('/customers');
    $responseA->assertOk();
    $responseA->assertSee('Pelanggan Mawar');
    $responseA->assertDontSee('Pelanggan Melati');

    // Acting as Tenant B: only see Pelanggan Melati
    $responseB = $this->actingAs($userB)->get('/customers');
    $responseB->assertOk();
    $responseB->assertSee('Pelanggan Melati');
    $responseB->assertDontSee('Pelanggan Mawar');
});

test('pendaftaran usaha mandiri (onboarding) membuat tenant, trial 14 hari, dan data starter', function () {
    $this->get('/register')->assertOk()->assertSee('Daftarkan Usaha Laundry Anda');

    $response = $this->post('/register', [
        'laundry_name'          => 'Berkah Laundry Express',
        'laundry_address'       => 'Jl. Sukajadi No. 10, Bandung',
        'name'                  => 'Ahmad Sauqi',
        'username'              => 'ahmadsauqi',
        'email'                 => 'sauqi@berkahlaundry.test',
        'phone'                 => '081234567899',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    // Verify tenant created
    $tenant = Tenant::where('slug', 'berkah-laundry-express')->first();
    expect($tenant)->not->toBeNull();
    expect($tenant->status)->toBe('trial');
    expect($tenant->isInTrial())->toBeTrue();

    // Verify user created with admin role and owner
    $user = User::where('username', 'ahmadsauqi')->first();
    expect($user)->not->toBeNull();
    expect($user->tenant_id)->toBe($tenant->id);
    expect($tenant->owner_id)->toBe($user->id);
    expect($user->hasRole('admin'))->toBeTrue();

    // Verify subscription created
    $sub = $tenant->activeSubscription;
    expect($sub)->not->toBeNull();
    expect($sub->status)->toBe('trial');
    expect($sub->plan->slug)->toBe('business');

    // Verify starter master data generated for tenant
    TenantContext::setTenant($tenant);
    expect(ServiceCategory::where('tenant_id', $tenant->id)->count())->toBeGreaterThanOrEqual(2);
    expect(Service::where('tenant_id', $tenant->id)->count())->toBeGreaterThanOrEqual(2);
    expect(Rack::where('tenant_id', $tenant->id)->count())->toBeGreaterThanOrEqual(2);
    TenantContext::clear();
});

test('proses langganan: memilih paket, membuat invoice, dan konfirmasi pembayaran mengaktifkan langganan', function () {
    $tenant = Tenant::create([
        'name'          => 'Klin Laundry',
        'slug'          => 'klin-laundry',
        'status'        => 'trial',
        'trial_ends_at' => now()->addDays(5),
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role'      => 'admin',
    ]);
    $user->assignRole('admin');
    $tenant->update(['owner_id' => $user->id]);

    $businessPlan = Plan::where('slug', 'business')->first();

    // 1. Choose Plan (Monthly)
    $response = $this->actingAs($user)->post('/billing/plan', [
        'plan_id'       => $businessPlan->id,
        'billing_cycle' => 'monthly',
    ]);

    $invoice = BillingInvoice::where('tenant_id', $tenant->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe('unpaid');
    expect((float) $invoice->amount)->toBe(99000.0);

    $response->assertRedirect(route('billing.invoice.show', $invoice));

    // 2. Pay Invoice (Simulated Payment)
    $payResponse = $this->actingAs($user)->post(route('billing.invoice.pay', $invoice), [
        'payment_method' => 'qris',
    ]);

    $payResponse->assertRedirect(route('billing.index'));

    $invoice->refresh();
    $tenant->refresh();

    expect($invoice->status)->toBe('paid');
    expect($invoice->paid_at)->not->toBeNull();
    expect($tenant->status)->toBe('active');
    expect($tenant->hasActivePaidSubscription())->toBeTrue();
});

test('kebijakan langganan: mode read-only aktif jika masa aktif dan grace period berakhir', function () {
    // Tenant expired 10 days ago (past 7-day grace period)
    $tenant = Tenant::create([
        'name'          => 'Laundry Lama',
        'slug'          => 'laundry-lama',
        'status'        => 'past_due',
        'trial_ends_at' => now()->subDays(10),
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role'      => 'admin',
    ]);
    $user->assignRole('admin');

    // Tenant should be in read-only mode
    expect($tenant->isReadOnly())->toBeTrue();

    // GET request (viewing customer list) is allowed
    $getResponse = $this->actingAs($user)->get('/customers');
    $getResponse->assertOk();

    // POST request (trying to add a new customer) must be blocked and redirected to billing
    $postResponse = $this->actingAs($user)->post('/customers', [
        'full_name'    => 'Pelanggan Baru',
        'phone_number' => '0899999999',
    ]);

    $postResponse->assertRedirect(route('billing.index'));
    $postResponse->assertSessionHas('error');
});

test('ekspor data: pemilik laundry dapat mengunduh CSV pelanggan dan transaksi', function () {
    $tenant = Tenant::create([
        'name'          => 'Fresh Laundry',
        'slug'          => 'fresh-laundry',
        'status'        => 'active',
        'trial_ends_at' => now()->addDays(14),
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role'      => 'admin',
    ]);
    $user->assignRole('admin');

    TenantContext::setTenant($tenant);
    Customer::create([
        'tenant_id'    => $tenant->id,
        'full_name'    => 'Dewi Lestari',
        'phone_number' => '0877777777',
        'is_active'    => true,
    ]);
    TenantContext::clear();

    $response = $this->actingAs($user)->get('/export-data/customers');
    $response->assertOk();
    $response->assertHeader('Content-Disposition');
    expect($response->streamedContent())->toContain('Dewi Lestari');
});

test('saas platform superadmin: hanya superadmin yang dapat mengakses panel kontrol platform', function () {
    $superadmin = User::factory()->create([
        'role'          => 'admin',
        'is_superadmin' => true,
        'tenant_id'     => null,
    ]);
    $superadmin->assignRole('admin');

    $regularAdmin = User::factory()->create([
        'role'          => 'admin',
        'is_superadmin' => false,
    ]);
    $regularAdmin->assignRole('admin');

    // Regular admin blocked (403)
    $this->actingAs($regularAdmin)->get('/saas-admin')->assertForbidden();

    // Superadmin allowed (200)
    $this->actingAs($superadmin)->get('/saas-admin')
        ->assertOk()
        ->assertSee('Platform Superadmin Control Panel');
    $this->actingAs($superadmin)->get('/saas-admin/tenants')->assertOk();
    $this->actingAs($superadmin)->get('/saas-admin/plans')->assertOk();
    $this->actingAs($superadmin)->get('/saas-admin/messages')->assertOk();
    $this->actingAs($superadmin)->get('/saas-admin/cms')->assertOk();

    // Superadmin can update Web Profile CMS and it reflects on landing page
    $this->actingAs($superadmin)->put('/saas-admin/cms', [
        'hero_badge'       => 'PLATFORM LAUNDRY TERBAIK',
        'hero_title'       => 'Judul Hero Kustom Superadmin',
        'hero_subtitle'    => 'Subjudul kustom dari CMS Superadmin.',
        'trial_days'       => 21,
        'tagline'          => 'Tagline Kustom Laundiqo.',
        'contact_email'    => 'halo@laundiqo.id',
        'contact_whatsapp' => '081122334455',
        'contact_hours'    => 'Senin–Sabtu, 08.00–18.00 WIB',
        'contact_address'  => 'Bandung, Indonesia',
        'faq_questions'    => ['Apakah ada aplikasi Android?'],
        'faq_answers'      => ['Dapat diakses langsung melalui browser HP tanpa instalasi.'],
    ])->assertRedirect(route('saas-admin.cms'));

    $this->post('/logout');
    $this->get('/')
        ->assertOk()
        ->assertSee('Judul Hero Kustom Superadmin')
        ->assertSee('Apakah ada aplikasi Android?');

    // Tenant billing is not applicable to a platform superadmin.
    $this->actingAs($superadmin)->get('/billing')->assertRedirect(route('saas-admin.index'));
});

test('admin tenant hanya dapat mengelola pengguna di tenant sendiri', function () {
    $tenantA = Tenant::create([
        'name' => 'Laundry A',
        'slug' => 'laundry-a-users',
        'status' => 'active',
    ]);
    $tenantB = Tenant::create([
        'name' => 'Laundry B',
        'slug' => 'laundry-b-users',
        'status' => 'active',
    ]);
    Subscription::create([
        'tenant_id' => $tenantA->id,
        'plan_id' => Plan::where('slug', 'business')->value('id'),
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $adminA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role' => 'admin',
    ]);
    $adminA->assignRole('admin');

    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role' => 'kasir',
    ]);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'role' => 'kasir',
    ]);

    $this->actingAs($adminA)
        ->get('/users')
        ->assertOk()
        ->assertSee($userA->username)
        ->assertDontSee($userB->username);

    $this->actingAs($adminA)
        ->get(route('users.edit', $userB))
        ->assertNotFound();

    $this->actingAs($adminA)
        ->delete(route('users.destroy', $userB))
        ->assertNotFound();

    expect(User::find($userB->id))->not->toBeNull();
});

test('role custom tenant membatasi akses sesuai tugas yang dipilih admin', function () {
    $tenant = Tenant::create([
        'name' => 'Laundry Role',
        'slug' => 'laundry-role',
        'status' => 'active',
    ]);
    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => Plan::where('slug', 'business')->value('id'),
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);
    $role = TenantRole::create([
        'tenant_id' => $tenant->id,
        'name' => 'Front Desk',
        'slug' => 'front-desk',
        'permissions' => ['view-dashboard', 'manage-customers'],
    ]);
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => $role->slug,
    ]);

    $this->actingAs($user)->get('/customers')->assertOk();
    $this->actingAs($user)->get('/users')->assertForbidden();
    $this->actingAs($user)->get('/reports')->assertForbidden();
});

test('admin tenant dapat mengedit permission role bawaan', function () {
    $tenant = Tenant::create([
        'name' => 'Laundry Edit Role',
        'slug' => 'laundry-edit-role',
        'status' => 'active',
    ]);
    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => Plan::where('slug', 'business')->value('id'),
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);
    TenantRoleController::ensureDefaults($tenant->id);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $admin->assignRole('admin');
    $kasirRole = TenantRole::where('tenant_id', $tenant->id)->where('slug', 'kasir')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('tenant-roles.edit', $kasirRole))
        ->assertOk()
        ->assertSee('Simpan Role');

    $this->actingAs($admin)->put(route('tenant-roles.update', $kasirRole), [
        'name' => 'Kasir Front Desk',
        'permissions' => ['view-dashboard'],
    ])->assertRedirect(route('tenant-roles.index'));

    expect($kasirRole->refresh()->name)->toBe('Kasir Front Desk')
        ->and($kasirRole->permissions)->toBe(['view-dashboard']);
});

test('admin tenant dapat mengakses seluruh operasi tanpa role tambahan', function () {
    $tenant = Tenant::create([
        'name' => 'Laundry Mandiri',
        'slug' => 'laundry-mandiri',
        'status' => 'active',
    ]);
    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => Plan::where('slug', 'business')->value('id'),
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/customers')->assertOk();
    $this->actingAs($admin)->get('/orders')->assertOk();
    $this->actingAs($admin)->get('/payments')->assertOk();
    $this->actingAs($admin)->get('/racks')->assertOk();
    $this->actingAs($admin)->get('/service-categories')->assertOk();
    $this->actingAs($admin)->get('/services')->assertOk();
    $this->actingAs($admin)->get('/reports')->assertOk();
    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($admin)->get('/settings')->assertOk();
});

test('multi-tenant: pengaturan toko dan nomor telepon pelanggan yang sama dapat digunakan oleh dua tenant berbeda', function () {
    $tenantA = Tenant::create(['name' => 'Laundry Alpha', 'slug' => 'laundry-alpha', 'status' => 'trial', 'trial_ends_at' => now()->addDays(14)]);
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    $adminA->assignRole('admin');
    $tenantA->update(['owner_id' => $adminA->id]);

    $tenantB = Tenant::create(['name' => 'Laundry Beta', 'slug' => 'laundry-beta', 'status' => 'trial', 'trial_ends_at' => now()->addDays(14)]);
    $adminB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => 'admin']);
    $adminB->assignRole('admin');
    $tenantB->update(['owner_id' => $adminB->id]);

    // Both tenants update settings without colliding on settings.key
    $this->actingAs($adminA)->put('/settings', [
        'store_name' => 'Alpha Clean Laundry',
        'store_phone' => '0811000001',
        'store_address' => 'Jl. Alpha No. 1',
    ])->assertSessionHasNoErrors();

    $this->actingAs($adminB)->put('/settings', [
        'store_name' => 'Beta Fresh Laundry',
        'store_phone' => '0811000002',
        'store_address' => 'Jl. Beta No. 2',
    ])->assertSessionHasNoErrors();

    expect($tenantA->refresh()->name)->toBe('Alpha Clean Laundry');
    expect($tenantB->refresh()->name)->toBe('Beta Fresh Laundry');

    // Both tenants register a customer with the same phone number
    $this->actingAs($adminA)->post('/customers', [
        'full_name' => 'Budi Alpha',
        'phone_number' => '081299998888',
    ])->assertSessionHasNoErrors();

    $this->actingAs($adminB)->post('/customers', [
        'full_name' => 'Budi Beta',
        'phone_number' => '081299998888',
    ])->assertSessionHasNoErrors();

    expect(Customer::withoutGlobalScopes()->where('phone_number', '081299998888')->count())->toBe(2);
});

test('batas kuota paket: pembuatan akun staf ditolak jika melebihi max_staff paket dan ekspor laporan dibatasi pada paket starter', function () {
    $starterPlan = Plan::where('slug', 'starter')->firstOrFail(); // max_staff = 2

    $tenant = Tenant::create([
        'name' => 'Starter Laundry',
        'slug' => 'starter-laundry',
        'status' => 'active',
    ]);
    $owner = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);
    $owner->assignRole('admin');
    $tenant->update(['owner_id' => $owner->id]);
    TenantRoleController::ensureDefaults($tenant->id);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $starterPlan->id,
        'billing_cycle' => 'monthly',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    // Staff 1 & 2 allowed (max_staff = 2)
    $this->actingAs($owner)->post('/users', [
        'username' => 'staf_satu',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'kasir',
    ])->assertRedirect(route('users.index'));

    $this->actingAs($owner)->post('/users', [
        'username' => 'staf_dua',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'operator',
    ])->assertRedirect(route('users.index'));

    // Staff 3 blocked by plan quota
    $this->actingAs($owner)->post('/users', [
        'username' => 'staf_tiga',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'kasir',
    ])->assertSessionHas('error');

    expect(User::where('tenant_id', $tenant->id)->where('id', '!=', $owner->id)->count())->toBe(2);

    // Export PDF/Excel blocked on Starter plan
    $this->actingAs($owner)->get('/reports/export?format=pdf')
        ->assertSessionHas('error');
});

test('formulir kontak menyimpan pesan ke database dan tampil di dashboard superadmin', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $this->post('/kontak', [
        'name' => 'Rina Pemilik Laundry',
        'contact' => 'rina@laundrybersih.com',
        'business_name' => 'Rina Bersih Laundry',
        'message' => 'Saya tertarik menggunakan paket Business tahunan.',
    ])->assertRedirect(route('marketing.contact'));

    $msg = \App\Models\ContactMessage::first();
    expect($msg)->not->toBeNull()
        ->and($msg->name)->toBe('Rina Pemilik Laundry')
        ->and($msg->contact)->toBe('rina@laundrybersih.com')
        ->and($msg->is_read)->toBeFalse();

    $superadmin = User::factory()->create([
        'role' => 'admin',
        'is_superadmin' => true,
        'tenant_id' => null,
    ]);
    $superadmin->assignRole('admin');

    $this->actingAs($superadmin)->get('/saas-admin')
        ->assertOk()
        ->assertSee('Rina Pemilik Laundry')
        ->assertSee('Rina Bersih Laundry');

    // Superadmin membalas pesan via email
    $this->actingAs($superadmin)->post(route('saas-admin.messages.reply', $msg), [
        'reply_subject' => 'Re: Informasi Paket Business Tahunan — Laundiqo',
        'reply_message' => 'Halo Kak Rina, berikut informasi paket Business tahunan kami.',
    ])->assertRedirect();

    $msg->refresh();
    expect($msg->is_read)->toBeTrue()
        ->and($msg->isReplied())->toBeTrue()
        ->and($msg->reply_subject)->toBe('Re: Informasi Paket Business Tahunan — Laundiqo')
        ->and($msg->replied_by)->toBe($superadmin->id);
});

test('soft deletes: menghapus data master tidak merusak histori order lama dan mengizinkan pembuatan ulang data dengan nomor/nama yang sama', function () {
    $tenant = Tenant::create([
        'name' => 'SoftDelete Laundry',
        'slug' => 'softdelete-laundry',
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(14),
    ]);
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $admin->assignRole('admin');
    $kasir = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'kasir']);
    $kasir->assignRole('kasir');

    $category = ServiceCategory::create([
        'tenant_id' => $tenant->id,
        'name' => 'Cuci Kiloan',
        'is_active' => true,
    ]);
    $service = Service::create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'name' => 'Cuci Setrika Reguler',
        'unit' => 'kg',
        'price_per_unit' => 8000,
        'is_active' => true,
    ]);
    $rack = \App\Models\Rack::create([
        'tenant_id' => $tenant->id,
        'name' => 'Rak A1',
        'location_description' => 'Baris Depan',
        'is_active' => true,
    ]);
    $customer = Customer::create([
        'tenant_id' => $tenant->id,
        'full_name' => 'Andi Historis',
        'phone_number' => '081277776666',
        'is_active' => true,
    ]);

    // Buat order selesai yang berelasi ke customer, service, rack, dan kasir
    $order = Order::create([
        'tenant_id' => $tenant->id,
        'order_number' => 'LDR-20261103-0999',
        'customer_id' => $customer->id,
        'rack_id' => $rack->id,
        'created_by' => $kasir->id,
        'completed_by' => $kasir->id,
        'status' => 'selesai',
        'payment_status' => 'lunas',
        'total_cost' => 16000,
        'amount_paid' => 16000,
        'received_date' => today(),
        'estimated_done_date' => today()->addDay(),
        'completed_at' => now(),
    ]);
    $order->items()->create([
        'tenant_id' => $tenant->id,
        'service_id' => $service->id,
        'quantity' => 2,
        'unit_price' => 8000,
        'subtotal' => 16000,
    ]);

    // Hapus (Soft Delete) Customer, Service, Rack, dan Kasir melalui endpoint HTTP
    $this->actingAs($admin)->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));
    $this->actingAs($admin)->delete(route('services.destroy', $service))->assertRedirect(route('services.index'));
    $this->actingAs($admin)->delete(route('racks.destroy', $rack))->assertRedirect(route('racks.index'));
    $this->actingAs($admin)->delete(route('users.destroy', $kasir))->assertRedirect(route('users.index'));

    // Pastikan semuanya berstatus soft-deleted
    expect($customer->fresh()->trashed())->toBeTrue()
        ->and($service->fresh()->trashed())->toBeTrue()
        ->and($rack->fresh()->trashed())->toBeTrue()
        ->and($kasir->fresh()->trashed())->toBeTrue();

    // Histori Order tetap dapat membaca data master yang sudah di-soft-delete berkat ->withTrashed()
    $orderFresh = Order::with(['customer', 'items.service.category', 'rack', 'createdBy'])->findOrFail($order->id);
    expect($orderFresh->customer->full_name)->toBe('Andi Historis')
        ->and($orderFresh->items->first()->service->name)->toBe('Cuci Setrika Reguler')
        ->and($orderFresh->rack->name)->toBe('Rak A1')
        ->and($orderFresh->createdBy->id)->toBe($kasir->id);

    // Halaman detail order tetap tampil normal tanpa error 500
    $this->actingAs($admin)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Andi Historis')
        ->assertSee('Cuci Setrika Reguler');

    // Tab Terhapus (?trashed=1) menampilkan data yang telah dihapus
    $this->actingAs($admin)->get(route('customers.index', ['trashed' => 1]))
        ->assertOk()
        ->assertSee('Andi Historis')
        ->assertSee('Pulihkan');

    // Pulihkan (Restore) layanan & pengguna dari tab Terhapus
    $this->actingAs($admin)->patch(route('services.restore', $service->id))
        ->assertRedirect(route('services.index', ['trashed' => 1]));
    $this->actingAs($admin)->patch(route('users.restore', $kasir->id))
        ->assertRedirect(route('users.index', ['trashed' => 1]));

    expect($service->fresh()->trashed())->toBeFalse()
        ->and($kasir->fresh()->trashed())->toBeFalse();

    // Admin dapat membuat pelanggan baru & rak baru dengan nomor HP / nama rak yang sama selama yang lama masih terhapus
    $this->actingAs($admin)->post(route('customers.store'), [
        'full_name' => 'Andi Baru',
        'phone_number' => '081277776666',
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('racks.store'), [
        'name' => 'Rak A1',
        'location_description' => 'Baris Baru',
        'is_active' => 1,
    ])->assertSessionHasNoErrors();

    // Jika mencoba memulihkan pelanggan lama padahal nomor HP-nya sudah dipakai pelanggan aktif baru, sistem menolak dengan pesan jelas
    $this->actingAs($admin)->patch(route('customers.restore', $customer->id))
        ->assertSessionHas('error');
    expect($customer->fresh()->trashed())->toBeTrue();
});

test('payment gateway otomatis: registrasi direct_pay membuat invoice, midtrans snap token, verifikasi signature webhook, dan cek status otomatis', function () {
    config([
        'services.midtrans.server_key'    => 'SB-Mid-server-TestKey123',
        'services.midtrans.client_key'    => 'SB-Mid-client-TestKey123',
        'services.midtrans.is_production' => false,
    ]);

    \Illuminate\Support\Facades\Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => \Illuminate\Support\Facades\Http::response([
            'token'        => 'snap-token-xyz-999',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-xyz-999',
        ], 201),
    ]);

    // 1. Registrasi dengan mode direct_pay langsung membuat invoice + snap_token dan mengarahkan ke halaman invoice
    $response = $this->post('/register', [
        'laundry_name'          => 'Kilat Laundry Otomatis',
        'laundry_address'       => 'Jl. Sudirman No. 88',
        'plan'                  => 'pro',
        'billing_cycle'         => 'monthly',
        'subscription_mode'     => 'direct_pay',
        'name'                  => 'Deni Otomatis',
        'username'              => 'deniotomatis',
        'email'                 => 'deni@kilatlaundry.test',
        'phone'                 => '081288887777',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $tenant = Tenant::where('slug', 'kilat-laundry-otomatis')->firstOrFail();
    $invoice = BillingInvoice::where('tenant_id', $tenant->id)->firstOrFail();

    $response->assertRedirect(route('billing.invoice.show', $invoice));
    expect($invoice->snap_token)->toBe('snap-token-xyz-999')
        ->and($invoice->status)->toBe('unpaid')
        ->and($tenant->status)->toBe('past_due');

    // Sebelum bayar, mencoba akses /dashboard atau login ulang harus diarahkan ke halaman invoice pembayaran
    $this->get('/dashboard')->assertRedirect(route('billing.invoice.show', $invoice));
    $this->post('/logout');
    $this->post('/login', [
        'username' => 'deniotomatis',
        'password' => 'password123',
    ])->assertRedirect(route('billing.invoice.show', $invoice));

    // 2. Webhook dengan signature_key palsu harus ditolak (403)
    $this->postJson('/api/billing/webhook', [
        'order_id'           => $invoice->invoice_number,
        'status_code'        => '200',
        'gross_amount'       => (string) (int) round((float) $invoice->amount),
        'transaction_status' => 'settlement',
        'signature_key'      => 'invalid-signature',
    ])->assertStatus(403);

    expect($invoice->fresh()->status)->toBe('unpaid');

    // 3. Webhook dengan signature_key SHA-512 resmi Midtrans otomatis mengaktifkan langganan
    $grossAmount = (string) (int) round((float) $invoice->amount);
    $validSignature = hash('sha512', $invoice->invoice_number . '200' . $grossAmount . 'SB-Mid-server-TestKey123');

    $this->postJson('/api/billing/webhook', [
        'order_id'           => $invoice->invoice_number,
        'status_code'        => '200',
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'payment_type'       => 'qris',
        'transaction_id'     => 'MID-QRIS-001',
        'signature_key'      => $validSignature,
    ])->assertOk();

    expect($invoice->fresh()->status)->toBe('paid')
        ->and($tenant->fresh()->status)->toBe('active')
        ->and($tenant->fresh()->hasActivePaidSubscription())->toBeTrue();
});

test('notifikasi real-time: polling dan mark-all-read berfungsi untuk superadmin dan staf tenant', function () {
    $superadmin = User::factory()->create([
        'tenant_id' => null,
        'role' => 'superadmin',
        'is_superadmin' => true,
    ]);

    Tenant::create([
        'name' => 'Laundry Uji Notif',
        'slug' => 'laundry-uji-notif',
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(14),
    ]);

    \App\Models\ContactMessage::create([
        'name' => 'Rina Calon Mitra',
        'contact' => '08123456789',
        'business_name' => 'Rina Fresh Laundry',
        'message' => 'Halo, saya tertarik langganan paket Pro.',
        'is_read' => false,
    ]);

    $pollSuper = $this->actingAs($superadmin)
        ->getJson(route('notifications.poll'))
        ->assertOk()
        ->json();

    expect($pollSuper['unread_count'])->toBeGreaterThan(0)
        ->and($pollSuper['sidebar_badges']['unread_leads'])->toBe(1)
        ->and($pollSuper['state_signature'])->not->toBeEmpty();

    $initialSig = $pollSuper['state_signature'];

    \App\Models\ContactMessage::create([
        'name' => 'Budi Kedua',
        'contact' => '08129999000',
        'business_name' => 'Budi Laundry',
        'message' => 'Pesan kedua masuk.',
        'is_read' => false,
    ]);

    $pollAfterNewMsg = $this->actingAs($superadmin)
        ->getJson(route('notifications.poll'))
        ->assertOk()
        ->json();

    expect($pollAfterNewMsg['state_signature'])->not->toBe($initialSig);

    // Mark all read clears unread count
    $this->actingAs($superadmin)
        ->postJson(route('notifications.read-all'))
        ->assertOk()
        ->assertJson(['success' => true]);

    $pollAfterRead = $this->actingAs($superadmin)
        ->getJson(route('notifications.poll'))
        ->assertOk()
        ->json();

    expect($pollAfterRead['unread_count'])->toBe(0);
});

test('nomor transaksi (order_number) unik per tenant dan counter harian direset mandiri per tenant', function () {
    $tenantA = Tenant::create([
        'name'          => 'Laundry Mawar',
        'slug'          => 'laundry-mawar',
        'status'        => 'active',
        'phone'         => '081111222333',
        'address'       => 'Jl. Mawar No. 1',
        'trial_ends_at' => now()->addDays(14),
    ]);
    $tenantB = Tenant::create([
        'name'          => 'Berkah Laundry',
        'slug'          => 'berkah-laundry',
        'status'        => 'active',
        'phone'         => '082222333444',
        'address'       => 'Jl. Berkah No. 2',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $orderService = app(\App\Services\OrderService::class);
    $today = now()->format('Ymd');

    // Generate 1st order for Tenant A -> MAW{id}-YYYYMMDD-0001
    TenantContext::setTenant($tenantA);
    $numA1 = $orderService->generateOrderNumber();
    expect($numA1)->toBe($tenantA->order_prefix . '-' . $today . '-0001');

    $custA = Customer::create([
        'tenant_id'    => $tenantA->id,
        'full_name'    => 'Pelanggan Mawar',
        'phone_number' => '08123456001',
        'is_active'    => true,
    ]);
    $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    Order::create([
        'tenant_id'           => $tenantA->id,
        'order_number'        => $numA1,
        'customer_id'         => $custA->id,
        'created_by'          => $userA->id,
        'status'              => 'diterima',
        'payment_status'      => 'belum_bayar',
        'total_cost'          => 25000,
        'amount_paid'         => 0,
        'received_date'       => today(),
        'estimated_done_date' => today()->addDay(),
    ]);

    // 2nd order for Tenant A -> MAW{id}-YYYYMMDD-0002
    $numA2 = $orderService->generateOrderNumber();
    expect($numA2)->toBe($tenantA->order_prefix . '-' . $today . '-0002');

    // 1st order for Tenant B -> BER{id}-YYYYMMDD-0001 (starts from 0001 independently!)
    TenantContext::setTenant($tenantB);
    $numB1 = $orderService->generateOrderNumber();
    expect($numB1)->toBe($tenantB->order_prefix . '-' . $today . '-0001');
    TenantContext::clear();

    // Public tracking search works case-insensitively and shows Tenant A's identity
    $this->post(route('tracking.search'), ['order_number' => '  ' . strtolower($numA1) . '  '])
        ->assertRedirect(route('tracking.show', $numA1));

    $this->get(route('tracking.show', $numA1))
        ->assertOk()
        ->assertSee('Laundry Mawar')
        ->assertSee('Jl. Mawar No. 1')
        ->assertSee($numA1);
});
