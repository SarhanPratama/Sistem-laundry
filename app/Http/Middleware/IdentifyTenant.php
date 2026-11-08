<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->tenant_id) {
                TenantContext::setTenantId($user->tenant_id);
                $tenant = TenantContext::getTenant();

                if ($tenant) {
                    $subscription = $tenant->activeSubscription;
                    View::share('currentTenant', $tenant);
                    View::share('currentSubscription', $subscription);
                    View::share('appStoreName', \App\Models\Setting::get('store_name', $tenant->name));
                    View::share('appStoreCity', \App\Models\Setting::get('store_city', $tenant->phone ?? 'Laundry'));
                    View::share('appStorePhone', \App\Models\Setting::get('store_phone', $tenant->phone ?? ''));
                    View::share('appStoreAddress', \App\Models\Setting::get('store_address', $tenant->address ?? ''));
                    View::share('appReceiptFooter', \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kepercayaan Anda!'));
                    View::share('appBankInfo', \App\Models\Setting::get('bank_info', ''));
                }
            } else {
                TenantContext::clear();
            }
        } else {
            TenantContext::clear();
        }

        return $next($request);
    }
}

