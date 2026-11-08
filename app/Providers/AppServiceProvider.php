<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Bagikan identitas toko secara dinamis ke seluruh view Blade
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $tenant = \App\Services\TenantContext::getTenant();
            $view->with('appStoreName', \App\Models\Setting::get('store_name', $tenant?->name ?? config('app.name', 'Laundiqo')));
            $view->with('appStoreCity', \App\Models\Setting::get('store_city', $tenant?->phone ?? 'Indonesia'));
            $view->with('appStorePhone', \App\Models\Setting::get('store_phone', $tenant?->phone ?? ''));
            $view->with('appStoreAddress', \App\Models\Setting::get('store_address', $tenant?->address ?? ''));
            $view->with('appReceiptFooter', \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kepercayaan Anda!'));
            $view->with('appBankInfo', \App\Models\Setting::get('bank_info', ''));
        });
    }
}
