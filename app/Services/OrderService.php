<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderService
{
    /**
     * Generate a unique per-tenant order number in the format {TENANT_PREFIX}-YYYYMMDD-XXXX
     * (or LDR-YYYYMMDD-XXXX when no tenant context is active).
     * The 4-digit sequential counter resets every day independently for each tenant.
     *
     * @param  int|null  $tenantId
     * @return string
     */
    public function generateOrderNumber(?int $tenantId = null): string
    {
        $today = Carbon::today()->format('Ymd');
        $tenant = $tenantId
            ? \App\Models\Tenant::find($tenantId)
            : (TenantContext::getTenant() ?? auth()->user()?->tenant);

        $code = $tenant ? $tenant->order_prefix : 'LDR';
        $prefix = "{$code}-{$today}-";

        // Find the highest existing counter for today for this specific tenant prefix
        $lastOrder = Order::withoutGlobalScopes()
            ->withTrashed()
            ->where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastCounter = (int) substr($lastOrder->order_number, -4);
            $nextCounter = $lastCounter + 1;
        } else {
            $nextCounter = 1;
        }

        return $prefix . str_pad((string) $nextCounter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate the total cost from an array of order items.
     * Each item must have 'quantity' and 'unit_price' keys.
     *
     * @param  array  $items
     * @return float
     */
    public function calculateTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float) $item['quantity'] * (float) $item['unit_price'];
        }

        return $total;
    }
}
