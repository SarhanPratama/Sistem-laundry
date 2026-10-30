<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $statuses = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil', 'selesai'];
        $activeStatus = $request->input('status');

        $query = Order::with(['customer', 'items.service'])
            ->where('is_cancelled', false);

        if ($activeStatus) {
            $query->where('status', $activeStatus);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('customer', fn($c) => $c->where('full_name', 'LIKE', '%' . $search . '%'));
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('received_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('received_date', '<=', $dateTo);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        // Count per status for tab badges
        $statusCounts = Order::where('is_cancelled', false)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('orders.index', compact('orders', 'statuses', 'activeStatus', 'statusCounts', 'search'));
    }

    public function create()
    {
        // Only admin and kasir can create orders
        Gate::authorize('create', Order::class);

        $customers  = Customer::where('is_active', true)->orderBy('full_name')->get();
        $services   = Service::with('category')->where('is_active', true)->orderBy('name')->get();
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();

        return view('orders.create', compact('customers', 'services', 'categories'));
    }

    public function store(StoreOrderRequest $request)
    {
        // Only admin and kasir can create orders
        Gate::authorize('create', Order::class);

        $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $orderNumber = $this->orderService->generateOrderNumber();

            $order = Order::create([
                'order_number'        => $orderNumber,
                'customer_id'         => $request->customer_id,
                'created_by'          => auth()->id(),
                'status'              => 'diterima',
                'payment_status'      => 'belum_bayar',
                'received_date'       => $request->received_date,
                'estimated_done_date' => $request->estimated_done_date,
                'total_cost'          => 0,
                'amount_paid'         => 0,
            ]);

            $itemsForTotal = [];

            foreach ($request->items as $item) {
                $service   = Service::findOrFail($item['service_id']);
                $unitPrice = $service->price_per_unit;
                $subtotal  = $item['quantity'] * $unitPrice;

                $order->items()->create([
                    'service_id' => $item['service_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                ]);

                $itemsForTotal[] = [
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                ];
            }

            $order->update([
                'total_cost' => $this->orderService->calculateTotal($itemsForTotal),
            ]);

            // Log initial status
            OrderStatusLog::create([
                'order_id'   => $order->id,
                'status'     => 'diterima',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order berhasil dibuat dengan nomor ' . $order->order_number . '.');
    }

    public function show(Order $order)
    {
        $order->load(['customer.orders', 'items.service.category', 'payments.recordedBy', 'createdBy', 'completedBy', 'cancelledBy', 'statusLogs.changedBy', 'rack']);

        $racks = \App\Models\Rack::where('is_active', true)->get();

        return view('orders.show', compact('order', 'racks'));
    }

    public function cancel(Order $order)
    {
        if ($order->status !== 'diterima') {
            return back()->with('error', 'Order tidak dapat dibatalkan karena status saat ini adalah "' . $order->status . '". Pembatalan hanya diizinkan saat status masih "diterima".');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->update([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'rack_id'      => null, // Bebaskan rak jika ada alokasi
            ]);

            // Log cancellation
            OrderStatusLog::create([
                'order_id'   => $order->id,
                'status'     => 'dibatalkan',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        });

        $message = 'Order berhasil dibatalkan.';
        if ((float) $order->amount_paid > 0) {
            $message .= ' Perhatian: Order memiliki pembayaran tercatat sebesar Rp ' . number_format($order->amount_paid, 0, ',', '.') . '. Harap proses pengembalian dana (refund) kepada pelanggan jika diperlukan.';
        }

        return back()->with('success', $message);
    }
}
