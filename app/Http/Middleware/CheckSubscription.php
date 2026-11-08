<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Platform Superadmin bypasses subscription checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = TenantContext::getTenant();
        if (!$tenant) {
            return $next($request);
        }

        // If tenant registered with direct_pay and hasn't paid their initial invoice yet,
        // redirect them to the invoice payment page until payment is completed.
        if ($pendingInvoice = $tenant->pendingInitialInvoice()) {
            if (!$request->routeIs('billing.*') && !$request->routeIs('logout')) {
                return redirect()->route('billing.invoice.show', $pendingInvoice)
                    ->with('error', 'Silakan selesaikan pembayaran tagihan paket Anda terlebih dahulu untuk mengakses dashboard operasional.');
            }
            return $next($request);
        }

        // Check if tenant is within 7-day grace period
        if ($tenant->isInGracePeriod()) {
            session()->flash('subscription_warning', 'Masa aktif langganan atau trial telah berakhir. Usaha Anda berada dalam Masa Tenggang (Grace Period 7 Hari). Segera perpanjang agar operasional tidak beralih ke mode Read-Only.');
        }

        // Check if tenant is read-only (past grace period)
        if ($tenant->isReadOnly()) {
            // Whitelisted routes: billing, logout, profile
            $allowedRoutes = [
                'billing.*',
                'logout',
                'profile.*',
                'export.*',
            ];

            $isAllowedRoute = false;
            foreach ($allowedRoutes as $pattern) {
                if ($request->routeIs($pattern)) {
                    $isAllowedRoute = true;
                    break;
                }
            }

            // For non-safe HTTP methods (POST, PUT, PATCH, DELETE) outside allowed routes, block!
            if (!$isAllowedRoute && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                return redirect()->route('billing.index')->with('error', 'Masa aktif langganan telah habis dan masa tenggang berakhir. Akun dalam mode Read-Only. Silakan perpanjang langganan untuk menambah atau mengubah data.');
            }

            // For GET requests, show read-only flash banner
            if (!$isAllowedRoute) {
                session()->now('read_only_alert', 'Akun usaha Anda saat ini berada dalam mode Read-Only karena langganan telah berakhir. Anda tetap dapat melihat data dan pesanan yang berjalan, namun tidak dapat membuat transaksi baru.');
            }
        }

        return $next($request);
    }
}

