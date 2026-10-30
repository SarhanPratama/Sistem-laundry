# Sistem Manajemen Laundry

## Glosarium
- Aplikasi
  - Sistem manajemen operasional laundry berbasis Laravel.
- Browser
  - Peramban web yang mengirim request HTTP dan menampilkan HTML hasil render Blade.
- Route
  - Definisi URL dan method HTTP di routes/web.php.
- Middleware
  - Filter sebelum controller dipanggil, misalnya auth dan role:admin.
- Controller
  - Kelas di app/Http/Controllers yang menerima request, mengelola alur bisnis, lalu mengembalikan response.
- Service
  - Lapisan logika bisnis reusable, seperti OrderService, PaymentService, ReportService.
- Model
  - Representasi tabel dan relasi database di app/Models.
- View
  - Template Blade di resources/views.
- Role
  - Peran pengguna: admin, kasir, operator.
- Admin
  - Peran manajemen: pengguna, laporan, kategori layanan, layanan, dan manajemen master lainnya via route admin.
  - Monitor read-only: dapat membuka halaman pelacakan status untuk melihat histori dan progres seluruh order laundry, termasuk order yang sudah selesai. Admin tidak dapat mengubah status order.
- Kasir
  - Peran transaksi: pelanggan, order, pembayaran, pelacakan status, dan penerimaan barang (penutupan order saat pelanggan ambil).
- Operator
  - Peran produksi: update status cucian sampai siap_diambil dan melihat lokasi rak.
- Dashboard
  - Halaman ringkasan role-based di /dashboard.
- Pelacakan Publik
  - Fitur tracking order tanpa login melalui /tracking.
- Order
  - Entitas utama pesanan laundry (status proses + status pembayaran).
- Order Item
  - Detail item layanan per order.
- Payment
  - Riwayat pembayaran order (tunai/transfer, DP/lunas).
- Rack
  - Lokasi rak penyimpanan order siap diambil.
- Status Order
  - Urutan: diterima → dicuci → dikeringkan → disetrika → siap_diambil → selesai.
  - Siapa boleh mengubah tiap tahap: operator untuk tahap produksi (diterima → siap_diambil), kasir untuk tahap pengambilan (siap_diambil → selesai).
- Payment Status
  - belum_bayar, dp, lunas.
- Activity Log
  - Catatan aktivitas user (misalnya login).
- Eager Loading
  - Pengambilan relasi sekaligus via with([...]) untuk menghindari N+1 query.
- Pagination
  - Pembagian data halaman via paginate(10).
- Resource Route
  - Route CRUD otomatis (index, create, store, show, edit, update, destroy).

## Ringkasan Proyek
Aplikasi ini mengelola alur operasional laundry dari penerimaan order, proses produksi, pembayaran, sampai pelaporan bisnis.

Arsitektur alur request utama:

Browser -> Route -> Middleware -> Controller -> Service/Model -> Database -> Controller -> View/Redirect -> Browser

## Instalasi dan Deployment
Panduan lengkap tersedia di [DEPLOYMENT.md](DEPLOYMENT.md), meliputi kebutuhan sistem, instalasi lokal, konfigurasi produksi, koneksi database MySQL, permission storage, queue worker, HTTPS, dan checklist keamanan.

Ringkasan singkat:
- Pengembangan lokal: salin `.env.development.example` menjadi `.env` (SQLite, `APP_DEBUG=true`).
- Produksi: salin `.env.example` menjadi `.env` (MySQL, `APP_DEBUG=false`, `APP_ENV=production`, `APP_TIMEZONE=Asia/Jakarta`), lalu jalankan `php artisan key:generate --force`, `php artisan migrate --force`, `php artisan db:seed --force`, dan `php artisan optimize`.
- Ganti password akun bawaan (`admin`, `kasir`, `operator`) sebelum aplikasi dipakai.

## Struktur File Utama
- routes/web.php -> definisi semua route publik dan route terproteksi.
- app/Http/Controllers/AuthController.php -> login/logout + lock account.
- app/Http/Controllers/DashboardController.php -> dashboard admin/kasir/operator.
- app/Http/Controllers/OrderController.php -> pembuatan dan detail order.
- app/Http/Controllers/OrderStatusController.php -> pelacakan internal + update status produksi.
- app/Http/Controllers/PaymentController.php -> daftar pembayaran dan pencatatan payment.
- app/Http/Controllers/ReportController.php -> laporan dan export PDF/Excel.
- app/Http/Controllers/TrackingController.php -> tracking publik tanpa login.
- app/Http/Controllers/UserController.php -> manajemen akun user.
- app/Services/OrderService.php -> generate nomor order + hitung total.
- app/Services/PaymentService.php -> pencatatan pembayaran + update payment_status.
- app/Services/ReportService.php -> agregasi laporan revenue, ringkasan order, layanan populer.

## Route Utama
### Publik
- GET /login -> halaman login.
- POST /login -> proses autentikasi.
- POST /logout -> logout user.
- GET /tracking -> form tracking publik.
- POST /tracking -> submit nomor order.
- GET /tracking/{order_number} -> detail status publik.

### Authenticated
- GET /dashboard -> dashboard sesuai role.
- GET/PATCH/PUT/DELETE /profile... -> edit profil, ubah password, hapus akun.

### Admin-only
- Resource service-categories + PATCH toggle.
- Resource services + PATCH deactivate.
- Resource racks (CRUD penuh).
- Resource users + PATCH deactivate.
- GET reports + GET/POST reports/export.

### Operasional
- Resource customers + PATCH deactivate.
- Resource orders + PATCH orders/{order}/cancel.
- GET orders/status + PATCH orders/{order}/status (operator untuk produksi, kasir untuk pengambilan barang).
- GET payments + GET/POST orders/{order}/payments.
- GET racks, GET racks/{rack} (admin/operator).

## Alur Coding Per Fitur (Dengan Contoh Kode Asli Proyek)

### 3.1 Contoh Nyata: User Login
Misalkan kasir ingin login ke sistem.

Langkah 1: User membuka URL /login.

Langkah 2: Laravel menjalankan route berikut:

```php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
```

Langkah 3: Saat submit, method AuthController@login memvalidasi input:

```php
$request->validate([
  'username' => 'required',
  'password' => 'required',
]);
```

Langkah 4: Sistem cek akun aktif, cek lock akun, lalu verifikasi password:

```php
if (!Hash::check($request->password, $user->password)) {
  $user->failed_login_count++;

  if ($user->failed_login_count >= 5) {
    $user->locked_until = now()->addMinutes(15);
    $user->failed_login_count = 0;
  }
  $user->save();
  return back()->withErrors(['credentials' => 'Kredensial tidak valid.']);
}
```

Langkah 5: Jika valid, user di-login dan diarahkan ke dashboard.

```php
Auth::login($user);
$request->session()->regenerate();
return redirect()->route('dashboard');
```

### 3.2 Contoh Nyata: Dashboard Role-based
Misalkan user admin membuka halaman dashboard.

Langkah 1: User akses URL /dashboard.

Langkah 2: Route yang digunakan:

```php
Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

Langkah 3: DashboardController@index melakukan percabangan per role:

```php
if ($user->hasRole('operator')) {
  // statistik produksi operator
}

if ($user->hasRole('admin')) {
  // statistik admin (pendapatan, order, pelanggan)
}

// fallback kasir
```

Langkah 4: Admin mendapatkan data seperti total pendapatan hari ini:

```php
$totalPendapatanHariIni = Order::where('payment_status', 'lunas')
  ->whereDate('completed_at', today())
  ->sum('total_cost');
```

### 3.3 Contoh Nyata: Tracking Publik Tanpa Login
Misalkan pelanggan ingin cek status dengan nomor order.

Langkah 1: Pelanggan buka /tracking, lalu submit form.

Langkah 2: Route publik:

```php
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/tracking', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{order_number}', [TrackingController::class, 'show'])->name('tracking.show');
```

Langkah 3: TrackingController@search memvalidasi dan mencari order:

```php
$request->validate(['order_number' => 'required|string']);
$order = Order::where('order_number', $request->order_number)->first();
```

Langkah 4: Jika ketemu, redirect ke halaman detail tracking.

```php
return redirect()->route('tracking.show', $order->order_number);
```

### 3.4 Contoh Nyata: Kasir Membuat Order Baru
Misalkan kasir membuat order baru untuk pelanggan.

Langkah 1: Kasir membuka /orders/create.

Langkah 2: Route resource order:

```php
Route::resource('orders', OrderController::class);
```

Langkah 3: Method create() mengotorisasi role dan mengambil data master:

```php
Gate::authorize('create', Order::class);

$customers  = Customer::where('is_active', true)->orderBy('full_name')->get();
$services   = Service::with('category')->where('is_active', true)->orderBy('name')->get();
$categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();
```

Langkah 4: Saat submit POST /orders, method store() membuat nomor order:

```php
$orderNumber = $this->orderService->generateOrderNumber();
```

Langkah 5: Lalu menyimpan order dan item-item layanan:

```php
$order = Order::create([
  'order_number'   => $orderNumber,
  'customer_id'    => $request->customer_id,
  'status'         => 'diterima',
  'payment_status' => 'belum_bayar',
]);
```

### 3.5 Contoh Nyata: Melihat Daftar Order + Filter
Misalkan kasir ingin melihat order status dicuci dengan kata kunci tertentu.

Langkah 1: Akses /orders?status=dicuci&search=nama.

Langkah 2: OrderController@index membangun query:

```php
$query = Order::with(['customer', 'items.service'])
  ->where('is_cancelled', false);

if ($activeStatus) {
  $query->where('status', $activeStatus);
}
```

Langkah 3: Search customer / nomor order:

```php
if ($search = $request->input('search')) {
  $query->where(function ($q) use ($search) {
    $q->where('order_number', 'LIKE', '%' . $search . '%')
      ->orWhereHas('customer', fn($c) => $c->where('full_name', 'LIKE', '%' . $search . '%'));
  });
}
```

Langkah 4: Hasil dipaginasi lalu dikirim ke view.

### 3.6 Contoh Nyata: Operator Update Status Produksi
Misalkan operator memindahkan status dari dicuci ke dikeringkan.

Langkah 1: Operator klik update status (PATCH /orders/{order}/status).

Langkah 2: Route update status dibuka untuk operator dan kasir, otorisasi per transisi dilakukan di controller:

```php
Route::patch('orders/{order}/status', [OrderStatusController::class, 'update'])
  ->middleware('role:operator|kasir')
  ->name('orders.status.update');
```

Langkah 3: Controller menentukan role yang boleh mengubah status target:

```php
// Produksi hanya operator, penutupan order (selesai) hanya kasir
$requiredRole = $this->requiredRoleFor($newStatus);

if (!auth()->user()->hasRole($requiredRole)) {
  abort(403, 'Order hanya dapat ditandai selesai oleh kasir ketika pelanggan mengambil barang.');
}
```

Langkah 4: Controller memaksa transisi berurutan:

```php
$currentIndex = array_search($order->status, self::STATUS_SEQUENCE);
$newIndex     = array_search($newStatus, self::STATUS_SEQUENCE);

if ($newIndex !== $currentIndex + 1) {
  return back()->with('error', 'Transisi status tidak berurutan.');
}
```

Langkah 5: Jika valid, status di-update dan dicatat di log.

```php
$order->update($updateData);

OrderStatusLog::create([
  'order_id'   => $order->id,
  'status'     => $newStatus,
  'changed_by' => auth()->id(),
  'changed_at' => now(),
]);
```

### 3.7 Contoh Nyata: Pembayaran Order
Misalkan kasir mencatat pembayaran tunai.

Langkah 1: Kasir submit POST /orders/{order}/payments.

Langkah 2: Route pembayaran:

```php
Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
```

Langkah 3: PaymentController@store memvalidasi input:

```php
$validated = $request->validate([
  'method' => ['required', 'in:tunai,transfer'],
  'amount' => ['required', 'numeric', 'min:0.01'],
  'is_dp'  => ['boolean'],
]);
```

Langkah 4: Proses pembayaran dipindahkan ke service:

```php
$this->paymentService->recordPayment($order, $validated);
```

Langkah 5: PaymentService akan update payment_status menjadi belum_bayar/dp/lunas:

```php
if ($newAmountPaid <= 0) {
  $paymentStatus = 'belum_bayar';
} elseif ($newAmountPaid < $totalCost) {
  $paymentStatus = 'dp';
} else {
  $paymentStatus = 'lunas';
}
```

### 3.8 Contoh Nyata: Manajemen Pelanggan
Misalkan kasir mencari pelanggan berdasarkan nama.

Langkah 1: Buka /customers?search=andi.

Langkah 2: CustomerController@index menjalankan query:

```php
$query = Customer::query();

if ($search = $request->input('search')) {
  $query->where(function ($q) use ($search) {
    $q->where('full_name', 'LIKE', '%' . $search . '%')
      ->orWhere('phone_number', 'LIKE', '%' . $search . '%');
  });
}

$customers = $query->withCount('orders')
  ->withSum('orders', 'total_cost')
  ->latest()
  ->paginate(10);
```

### 3.9 Contoh Nyata: Admin Kelola Kategori Layanan
Misalkan admin menambah kategori layanan.

Langkah 1: Admin membuka /service-categories/create lalu submit form.

Langkah 2: Validasi di ServiceCategoryController@store:

```php
$request->validate([
  'name'        => 'required|string|max:100|unique:service_categories,name',
  'description' => 'nullable|string|max:255',
]);
```

Langkah 3: Simpan data:

```php
ServiceCategory::create([
  'name'        => $request->name,
  'description' => $request->description,
  'is_active'   => true,
]);
```

### 3.10 Contoh Nyata: Admin Kelola Layanan
Misalkan admin menambah layanan cuci kiloan.

Langkah 1: Admin submit POST /services.

Langkah 2: ServiceController@store menyimpan data layanan:

```php
Service::create([
  'category_id'    => $request->category_id ?: null,
  'name'           => $request->name,
  'unit'           => $request->unit,
  'price_per_unit' => $request->price_per_unit,
  'is_active'      => true,
]);
```

### 3.11 Contoh Nyata: Admin Kelola Pengguna
Misalkan admin membuat akun kasir baru.

Langkah 1: Admin submit POST /users.

Langkah 2: UserController@store validasi + create user:

```php
$request->validate([
  'username' => 'required|unique:users',
  'password' => 'required|min:8|confirmed',
  'role'     => 'required|in:admin,kasir,operator',
]);

$user = User::create([
  'username' => $request->username,
  'password' => Hash::make($request->password),
  'role'     => $request->role,
]);

$user->assignRole($request->role);
```

Catatan: Nilai role harus selalu sinkron dengan enum `users.role` (`admin`, `kasir`, `operator`) dan tabel `roles` milik Spatie, karena `@role()` di view memakai nama role Spatie sementara kolom `users.role` menyimpan salinannya.

### 3.12 Contoh Nyata: Admin Lihat Laporan dan Export
Misalkan admin ingin export laporan PDF.

Langkah 1: Admin buka /reports dengan filter tanggal.

Langkah 2: ReportController@index memanggil service report:

```php
$revenueData     = $this->reportService->getRevenueReport($period, $startDate, $endDate);
$orderSummary    = $this->reportService->getOrderSummary($startDate, $endDate);
$popularServices = $this->reportService->getPopularServices($startDate, $endDate);
```

Langkah 3: Saat export, route yang dipakai:

```php
Route::match(['get', 'post'], 'reports/export', [ReportController::class, 'export'])->name('reports.export');
```

Langkah 4: Jika format pdf, file di-generate dari Blade:

```php
$pdf = Pdf::loadView('reports.pdf', compact(
  'revenueData',
  'orderSummary',
  'popularServices',
  'startDate',
  'endDate',
  'period'
));

return $pdf->download('laporan-' . $startDate . '-sd-' . $endDate . '.pdf');
```

### 3.13 Contoh Nyata: Profil Pengguna
Misalkan user mengganti username dan password.

Langkah 1: User buka /profile.

Langkah 2: Route profile:

```php
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
```

Langkah 3: Update username di ProfileController@update:

```php
$validated = $request->validate([
  'username' => [
    'required',
    'string',
    'max:255',
    Rule::unique('users')->ignore($user->id),
  ],
]);
```

Langkah 4: Update password di ProfileController@password:

```php
$validated = $request->validate([
  'current_password' => ['required', 'current_password'],
  'password' => ['required', Password::defaults(), 'confirmed'],
]);
```

### 3.14 Contoh Nyata: Logout
Misalkan user ingin keluar dari aplikasi.

Langkah 1: User klik tombol logout yang mengirim POST /logout.

Langkah 2: Route logout:

```php
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

Langkah 3: AuthController@logout menghapus sesi:

```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

return redirect()->route('login');
```

### 3.15 Contoh Nyata: Kasir Membatalkan Order
Misalkan kasir ingin membatalkan order yang baru diterima.

Langkah 1: Kasir klik aksi batal pada detail order.

Langkah 2: Route pembatalan:

```php
Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
```

Langkah 3: OrderController@cancel hanya mengizinkan status diterima:

```php
if ($order->status !== 'diterima') {
  return back()->with('error', 'Order tidak dapat dibatalkan karena status saat ini adalah "' . $order->status . '". Pembatalan hanya diizinkan saat status masih "diterima".');
}
```

Langkah 4: Tandai order batal dan simpan log status:

```php
$order->update([
  'is_cancelled' => true,
  'cancelled_at' => now(),
  'cancelled_by' => auth()->id(),
]);

OrderStatusLog::create([
  'order_id'   => $order->id,
  'status'     => 'dibatalkan',
  'changed_by' => auth()->id(),
  'changed_at' => now(),
]);
```

### 3.16 Contoh Nyata: Manajemen Rak
Misalkan admin menambahkan rak baru.

Langkah 1: Admin membuka /racks/create lalu submit form.

Langkah 2: RackController@store memvalidasi data:

```php
$validated = $request->validate([
  'name' => 'required|string|max:255|unique:racks',
  'location_description' => 'required|string|max:255',
  'is_active' => 'boolean'
]);
```

Langkah 3: Simpan data rak:

```php
$validated['is_active'] = $request->has('is_active');
Rack::create($validated);
```

Langkah 4: Saat menghapus rak, controller mencegah jika rak masih dipakai order:

```php
if ($rack->orders()->count() > 0) {
  return back()->with('error', 'Rak ini masih dikaitkan dengan pesanan dan tidak dapat dihapus.');
}
```

## Model dan Relasi Inti
- User
  - hasMany(Order, created_by)
  - hasMany(Order, completed_by)
- Customer
  - hasMany(Order)
- Order
  - belongsTo(Customer)
  - hasMany(OrderItem)
  - hasMany(Payment)
  - hasMany(OrderStatusLog)
  - belongsTo(Rack)
  - belongsTo(User, created_by/completed_by/cancelled_by)
- OrderItem
  - belongsTo(Order)
  - belongsTo(Service)
- ServiceCategory
  - hasMany(Service)
- Service
  - belongsTo(ServiceCategory)
  - hasMany(OrderItem)
- Rack
  - hasMany(Order)
- Payment
  - belongsTo(Order)
  - belongsTo(User, recorded_by)
- OrderStatusLog
  - belongsTo(Order)
  - belongsTo(User, changed_by)

## Alur Singkat (Template Baca Fitur)
Untuk membaca flow fitur apa pun di proyek ini:
1. Cari URL dan method di routes/web.php.
2. Lihat controller + method yang menangani route.
3. Cek validasi request dan middleware/role.
4. Lihat query model atau service yang dipanggil.
5. Ikuti data yang dikirim ke view atau redirect.
6. Cek efek samping (log status, update payment, lock user, dll).

## Catatan Penting Implementasi Saat Ini
- Role aktif di sistem adalah admin, kasir, operator.
- Update status order wajib berurutan dan hanya boleh oleh role berikut.
  - Operator: diterima → dicuci → dikeringkan → disetrika → siap_diambil.
  - Kasir: siap_diambil → selesai (penutupan order saat pelanggan mengambil barang).
  - Admin tidak dapat mengubah status order.
- Admin dapat memantau `/orders/status` (read-only) lewat menu sidebar Pelacakan Status.
  Tanpa filter status, halaman menampilkan order yang masih berjalan saja. Order berstatus
  `selesai` sengaja disembunyikan dari papan kerja kasir/operator dan hanya muncul bila filter
  `?status=selesai` dikirim secara eksplisit, sehingga histori tetap berada di ranah admin.
- Role `owner` telah di-*rename* menjadi `admin` oleh migration
  `2026_09_30_220000_rename_owner_role_to_admin` (enum `users.role`, tabel `roles` Spatie, dan
  akun seed `owner` → `admin`). Migration tersebut memperlebar enum dulu sebelum mengubah data
  karena MySQL menolak nilai di luar daftar enum.
- Status selesai mensyaratkan pembayaran lunas.
- Memilih status siap_diambil wajib menyertakan rack_id, dan hanya bisa dari halaman detail order.
- Tracking publik bisa diakses tanpa login.
- Sebagian resource route tersedia penuh meskipun tidak semua method di controller dipakai pada UI.
- Jangan memakai `php artisan config:cache` / `route:cache` di environment lokal, karena
  cache tersebut mengabaikan `phpunit.xml` sehingga seluruh test POST/PATCH gagal dengan 419.
  Gunakan `php artisan optimize:clear` bila perlu.
