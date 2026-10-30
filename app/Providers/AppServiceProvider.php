<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bagikan identitas toko secara dinamis ke seluruh view Blade
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('appStoreName', \App\Models\Setting::get('store_name', config('app.name', 'Sistem Laundry')));
            $view->with('appStoreCity', \App\Models\Setting::get('store_city', 'Indonesia'));
            $view->with('appStorePhone', \App\Models\Setting::get('store_phone', ''));
            $view->with('appStoreAddress', \App\Models\Setting::get('store_address', ''));
            $view->with('appReceiptFooter', \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kepercayaan Anda!'));
        });
    }
}
