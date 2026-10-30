<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderService
{
    /**
     * Generate a unique order number in the format LDR-YYYYMMDD-XXXX.
     * The 4-digit sequential counter resets every day.
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        $today = Carbon::today()->format('Ymd');
        $prefix = "LDR-{$today}-";

        // Find the highest existing counter for today
        $lastOrder = Order::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastCounter = (int) substr($lastOrder->order_number, -4);
            $nextCounter = $lastCounter + 1;
        } else {
            $nextCounter = 1;
        }

        return $prefix . str_pad($nextCounter, 4, '0', STR_PAD_LEFT);
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
