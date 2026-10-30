<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root: authenticated users go to dashboard, guests go to login
Route::get('/', function () {
        return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// Public auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public tracking route (no login required)
Route::get('/tracking', [\App\Http\Controllers\TrackingController::class, 'index'])->name('tracking.index');
Route::post('/tracking', [\App\Http\Controllers\TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{order_number}', [\App\Http\Controllers\TrackingController::class, 'show'])->name('tracking.show');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        // Service categories management
        Route::resource('service-categories', \App\Http\Controllers\ServiceCategoryController::class);
        Route::patch('service-categories/{serviceCategory}/toggle', [\App\Http\Controllers\ServiceCategoryController::class, 'toggle'])->name('service-categories.toggle');

        // Services management
        Route::resource('services', \App\Http\Controllers\ServiceController::class);
        Route::patch('services/{service}/deactivate', [\App\Http\Controllers\ServiceController::class, 'deactivate'])->name('services.deactivate');


        // User management
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::patch('users/{user}/deactivate', [\App\Http\Controllers\UserController::class, 'deactivate'])->name('users.deactivate');

        // Reports
        Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::match(['get', 'post'], 'reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');

        // Store / Business Settings
        Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });

    // Customer management (Admin & Kasir)
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::patch('customers/{customer}/deactivate', [\App\Http\Controllers\CustomerController::class, 'deactivate'])->name('customers.deactivate');

    // Order management (Admin, Kasir, Operator)
    Route::get('orders/status', [\App\Http\Controllers\OrderStatusController::class, 'index'])
        ->middleware('role:admin|kasir|operator')
        ->name('orders.status');
    Route::resource('orders', \App\Http\Controllers\OrderController::class);
    Route::patch('orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order}/status', [\App\Http\Controllers\OrderStatusController::class, 'update'])
        ->middleware('role:operator|kasir')
        ->name('orders.status.update');

    // Rack view (Admin & Operator)
    Route::middleware(['role:admin|operator'])->group(function () {
        Route::get('racks', [\App\Http\Controllers\RackController::class, 'index'])->name('racks.index');
        Route::get('racks/create', [\App\Http\Controllers\RackController::class, 'create'])->name('racks.create');
        Route::post('racks', [\App\Http\Controllers\RackController::class, 'store'])->name('racks.store');
        Route::get('racks/{rack}/edit', [\App\Http\Controllers\RackController::class, 'edit'])->name('racks.edit');
        Route::put('racks/{rack}', [\App\Http\Controllers\RackController::class, 'update'])->name('racks.update');
        Route::delete('racks/{rack}', [\App\Http\Controllers\RackController::class, 'destroy'])->name('racks.destroy');
        Route::get('racks/{rack}', [\App\Http\Controllers\RackController::class, 'show'])->name('racks.show');

    });

    // Payment management
    Route::get('payments', [\App\Http\Controllers\PaymentController::class, 'list'])->name('payments.list');
    Route::post('orders/{order}/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('orders/{order}/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
});
