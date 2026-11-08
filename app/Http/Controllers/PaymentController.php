<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * List all orders with pending/partial payment (payment hub page).
     */
    public function list(Request $request)
    {
        $query = \App\Models\Order::with(['customer', 'payments'])
            ->where('is_cancelled', false)
            ->whereIn('payment_status', ['belum_bayar', 'dp']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('customer', fn($c) => $c->where('full_name', 'LIKE', '%' . $search . '%'));
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('payments.list', compact('orders', 'search'));
    }

    /**
     * Show payment history for an order.
     */
    public function index(Request $request, Order $order)
    {
        $order->load(['customer', 'payments.recordedBy']);

        return view('payments.index', compact('order'));
    }

    /**
     * Record a new payment for an order.
     *
     * Requirements: 6.1, 6.3, 6.6
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'method' => ['required', 'in:tunai,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $payment = $this->paymentService->recordPayment($order, $validated);
        } catch (\InvalidArgumentException $e) {
            // Show a notification instead of a Laravel error page
            return back()->withInput()->with('error', $e->getMessage());
        }

        $success = 'Pembayaran berhasil dicatat.';

        if ($payment->change_amount > 0) {
            $success .= ' Kembalian Rp ' . number_format($payment->change_amount, 0, ',', '.') . '.';
        }

        return redirect()->route('orders.show', $order)->with('success', $success);
    }
}
