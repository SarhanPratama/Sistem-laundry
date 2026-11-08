<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaasAdminController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\TenantRoleController;
use App\Http\Controllers\UserController;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

// Public marketing site; authenticated users go straight to their dashboard.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('marketing.home', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('price_monthly')->get(),
            'trialDays' => (int) Setting::getPlatform('platform_trial_days', '14'),
        ]);
})->name('marketing.home');

Route::view('/tentang-kami', 'marketing.about')->name('marketing.about');
Route::get('/kontak', [ContactController::class, 'show'])->name('marketing.contact');
Route::post('/kontak', [ContactController::class, 'submit'])
    ->middleware('throttle:contact')
    ->name('marketing.contact.submit');
Route::view('/kebijakan-privasi', 'marketing.privacy')->name('marketing.privacy');
Route::view('/syarat-layanan', 'marketing.terms')->name('marketing.terms');
Route::get('/sitemap.xml', function () {
    return response()->view('marketing.sitemap')->header('Content-Type', 'application/xml');
})->name('marketing.sitemap');

// Public auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public SaaS Tenant Registration (Self-service onboarding)
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register']);

// Public tracking route (no login required)
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/tracking', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{order_number}', [TrackingController::class, 'show'])->name('tracking.show');

// Payment Gateway Webhook (CSRF-exempt via middleware/api)
Route::post('/api/billing/webhook', [BillingController::class, 'webhook'])->name('billing.webhook');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('subscription');

    // Real-Time Notification Polling & Mark Read
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tenant Subscription & Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::middleware(['tenant.user'])->group(function () {
        Route::post('/billing/plan', [BillingController::class, 'choosePlan'])->name('billing.plan.choose');
        Route::get('/billing/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('billing.invoice.show');
        Route::post('/billing/invoices/{invoice}/pay', [BillingController::class, 'payInvoice'])->name('billing.invoice.pay');
        Route::post('/billing/invoices/{invoice}/check-status', [BillingController::class, 'checkStatus'])->name('billing.invoice.check-status');
    });

    // Tenant Data Portability & Export (Accessible even in read-only mode)
    Route::middleware(['tenant.user'])->group(function () {
        Route::get('/export-data', [ExportController::class, 'index'])->name('export.index');
        Route::get('/export-data/customers', [ExportController::class, 'exportCustomers'])->name('export.customers');
        Route::get('/export-data/orders', [ExportController::class, 'exportOrders'])->name('export.orders');
        Route::get('/export-data/payments', [ExportController::class, 'exportPayments'])->name('export.payments');
    });

    // SaaS Platform Superadmin Panel
    Route::middleware(['superadmin'])->prefix('saas-admin')->name('saas-admin.')->group(function () {
        Route::get('/', [SaasAdminController::class, 'index'])->name('index');

        // Tenants Management
        Route::get('/tenants', [SaasAdminController::class, 'tenants'])->name('tenants');
        Route::post('/tenants/{tenant}/extend', [SaasAdminController::class, 'extendSubscription'])->name('tenants.extend');
        Route::patch('/tenants/{tenant}/suspend', [SaasAdminController::class, 'toggleSuspend'])->name('tenants.suspend');
        Route::post('/tenants/{tenant}/reset-password', [SaasAdminController::class, 'resetOwnerPassword'])->name('tenants.reset-password');
        Route::delete('/tenants/{tenant}', [SaasAdminController::class, 'destroyTenant'])->name('tenants.destroy');
        Route::patch('/tenants/{id}/restore', [SaasAdminController::class, 'restoreTenant'])->name('tenants.restore');

        // Invoices Management
        Route::get('/invoices', [SaasAdminController::class, 'invoices'])->name('invoices');
        Route::post('/invoices/{invoice}/mark-paid', [SaasAdminController::class, 'markInvoicePaid'])->name('invoices.mark-paid');

        // Plans & Pricing Management
        Route::get('/plans', [SaasAdminController::class, 'plans'])->name('plans');
        Route::post('/plans', [SaasAdminController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [SaasAdminController::class, 'updatePlan'])->name('plans.update');
        Route::patch('/plans/{plan}/toggle', [SaasAdminController::class, 'togglePlan'])->name('plans.toggle');

        // Contact Leads / Messages Management
        Route::get('/messages', [SaasAdminController::class, 'messages'])->name('messages');
        Route::patch('/messages/{message}/read', [SaasAdminController::class, 'markMessageRead'])->name('messages.read');
        Route::post('/messages/{message}/reply', [SaasAdminController::class, 'replyMessage'])->name('messages.reply');
        Route::delete('/messages/{message}', [SaasAdminController::class, 'destroyMessage'])->name('messages.destroy');

        // Lightweight Web Profile CMS
        Route::get('/cms', [SaasAdminController::class, 'cms'])->name('cms');
        Route::put('/cms', [SaasAdminController::class, 'updateCms'])->name('cms.update');
    });

    // Operational Laundry Routes (protected by subscription middleware)
    Route::middleware(['tenant.user', 'subscription'])->group(function () {

        // Tenant administration
        Route::middleware(['tenant.permission:manage-services'])->group(function () {
            // Service categories management
            Route::resource('service-categories', ServiceCategoryController::class);
            Route::patch('service-categories/{serviceCategory}/toggle', [ServiceCategoryController::class, 'toggle'])->name('service-categories.toggle');
            Route::patch('service-categories/{id}/restore', [ServiceCategoryController::class, 'restore'])->name('service-categories.restore');

            // Services management
            Route::resource('services', ServiceController::class);
            Route::patch('services/{service}/deactivate', [ServiceController::class, 'deactivate'])->name('services.deactivate');
            Route::patch('services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');

        });

        Route::middleware(['tenant.permission:manage-users'])->group(function () {
            // User management and tenant role management
            Route::resource('users', UserController::class);
            Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
            Route::patch('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::resource('tenant-roles', TenantRoleController::class)->except(['show']);
        });

        Route::middleware(['tenant.permission:view-reports'])->group(function () {
            // Reports
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::match(['get', 'post'], 'reports/export', [ReportController::class, 'export'])->name('reports.export');
        });

        Route::middleware(['tenant.permission:manage-settings'])->group(function () {
            // Store / Business Settings
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });

        // Customer management
        Route::middleware(['tenant.permission:manage-customers'])->group(function () {
            Route::resource('customers', CustomerController::class);
            Route::patch('customers/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('customers.deactivate');
            Route::patch('customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
        });

        // Order management
        Route::get('orders/status', [OrderStatusController::class, 'index'])
            ->middleware('tenant.permission:view-dashboard')
            ->name('orders.status');
        Route::middleware(['tenant.permission:manage-orders'])->group(function () {
            Route::resource('orders', OrderController::class);
            Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        });
        Route::patch('orders/{order}/status', [OrderStatusController::class, 'update'])
            ->middleware('tenant.permission:update-order-status')
            ->name('orders.status.update');
        Route::patch('orders/{order}/rack', [OrderStatusController::class, 'updateRack'])
            ->middleware('tenant.permission:update-order-status')
            ->name('orders.rack.update');

        // Rack management
        Route::middleware(['tenant.permission:manage-racks'])->group(function () {
            Route::get('racks', [RackController::class, 'index'])->name('racks.index');
            Route::get('racks/create', [RackController::class, 'create'])->name('racks.create');
            Route::post('racks', [RackController::class, 'store'])->name('racks.store');
            Route::get('racks/{rack}/edit', [RackController::class, 'edit'])->name('racks.edit');
            Route::put('racks/{rack}', [RackController::class, 'update'])->name('racks.update');
            Route::delete('racks/{rack}', [RackController::class, 'destroy'])->name('racks.destroy');
            Route::patch('racks/{id}/restore', [RackController::class, 'restore'])->name('racks.restore');
            Route::get('racks/{rack}', [RackController::class, 'show'])->name('racks.show');
        });

        // Payment management
        Route::middleware(['tenant.permission:manage-payments'])->group(function () {
            Route::get('payments', [PaymentController::class, 'list'])->name('payments.list');
            Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::get('orders/{order}/payments', [PaymentController::class, 'index'])->name('payments.index');
        });
    });
});
