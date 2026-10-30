<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    /**
     * Record a payment for an order and update the order's payment status.
     *
     * $data['amount'] is the cash handed over by the customer (tendered amount).
     * For cash payments the excess over the remaining balance becomes change,
     * so only the net amount counts towards the order's amount_paid.
     *
     * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6
     */
    public function recordPayment(Order $order, array $data): Payment
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $data) {
            $amount           = (float) $data['amount'];
            $remainingBalance = $this->remainingBalance($order);
            $changeAmount     = $this->calculateChangeFor($data['method'], $amount, $remainingBalance);

            $payment = Payment::create([
                'order_id'      => $order->id,
                'recorded_by'   => auth()->id(),
                'method'        => $data['method'],
                'amount'        => $amount,
                'change_amount' => $changeAmount,
                'is_dp'         => $data['is_dp'] ?? false,
                'paid_at'       => now(),
            ]);

            // Update order's amount_paid with the net amount actually applied
            $newAmountPaid = (float) $order->amount_paid + ($amount - $changeAmount);
            $totalCost     = (float) $order->total_cost;

            // Determine new payment_status
            if ($newAmountPaid <= 0) {
                $paymentStatus = 'belum_bayar';
            } elseif ($newAmountPaid < $totalCost) {
                $paymentStatus = 'dp';
            } else {
                $paymentStatus = 'lunas';
            }

            $order->update([
                'amount_paid'    => $newAmountPaid,
                'payment_status' => $paymentStatus,
            ]);

            return $payment;
        });
    }

    /**
     * Calculate change for cash payments.
     * Returns amountPaid - totalCost if amountPaid > totalCost, else 0.
     *
     * Requirements: 6.2
     */
    public function calculateChange(float $amountPaid, float $totalCost): float
    {
        return $amountPaid > $totalCost ? $amountPaid - $totalCost : 0.0;
    }

    /**
     * Change is only given back for cash (tunai) payments.
     */
    public function calculateChangeFor(string $method, float $amountPaid, float $remainingBalance): float
    {
        return $method === 'tunai' ? $this->calculateChange($amountPaid, $remainingBalance) : 0.0;
    }

    /**
     * Outstanding balance of an order, never negative.
     */
    public function remainingBalance(Order $order): float
    {
        return max(0.0, (float) $order->total_cost - (float) $order->amount_paid);
    }
}
