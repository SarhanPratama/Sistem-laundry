<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Rack;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Poll real-time notifications & live data state signature for the authenticated user.
     */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'notifications' => []], 401);
        }

        $readKey = "user_{$user->id}_notifications_read_at";
        $lastReadIso = Cache::remember($readKey, 300, function () use ($readKey) {
            return Setting::withoutGlobalScopes()
                ->whereNull('tenant_id')
                ->where('key', $readKey)
                ->value('value');
        });

        // Default cursor for first-time check: 7 days ago so recent activity shows up
        $lastReadAt = $lastReadIso
            ? Carbon::parse($lastReadIso)
            : Carbon::now()->subDays(7);

        if ($user->isSuperAdmin()) {
            return $this->pollSuperAdmin($lastReadAt);
        }

        return $this->pollTenantUser($user, $lastReadAt);
    }

    /**
     * Mark all current notifications as read for the authenticated user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        $readKey = "user_{$user->id}_notifications_read_at";
        $nowIso = Carbon::now()->toIso8601String();

        Setting::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => null, 'key' => $readKey],
            ['value' => $nowIso]
        );

        Cache::put($readKey, $nowIso, 300);

        return response()->json([
            'success' => true,
            'read_at' => $nowIso,
        ]);
    }

    /**
     * Build notification feed + live sidebar badge counts + state signature for Platform Superadmin.
     */
    protected function pollSuperAdmin(Carbon $lastReadAt): JsonResponse
    {
        $items = collect();

        // 1. Recent Contact Messages / Leads
        $messages = ContactMessage::query()
            ->latest('created_at')
            ->limit(6)
            ->get();

        foreach ($messages as $msg) {
            $ts = $msg->created_at ?? Carbon::now();
            $items->push([
                'id' => 'lead_' . $msg->id,
                'type' => 'lead',
                'tone' => $msg->is_read ? 'blue' : 'rose',
                'title' => 'Pesan Masuk: ' . $msg->name,
                'message' => ($msg->business_name ? '[' . $msg->business_name . '] ' : '') . Str::limit($msg->message, 65),
                'url' => route('saas-admin.messages'),
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => (! $msg->is_read) && $ts->gt($lastReadAt),
            ]);
        }

        // 2. Recent Paid Invoices
        $paidInvoices = BillingInvoice::with(['tenant', 'plan'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(6)
            ->get();

        foreach ($paidInvoices as $inv) {
            $ts = $inv->paid_at ?? $inv->updated_at ?? $inv->created_at ?? Carbon::now();
            $items->push([
                'id' => 'inv_paid_' . $inv->id . '_' . $ts->timestamp,
                'type' => 'payment',
                'tone' => 'emerald',
                'title' => 'Tagihan Lunas: ' . ($inv->tenant->name ?? 'Tenant'),
                'message' => $inv->invoice_number . ' • Paket ' . ($inv->plan->name ?? '-') . ' (Rp ' . number_format((float) $inv->amount, 0, ',', '.') . ')',
                'url' => route('saas-admin.invoices', ['status' => 'paid']),
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => $ts->gt($lastReadAt),
            ]);
        }

        // 3. Recent Unpaid Invoices
        $unpaidInvoices = BillingInvoice::with(['tenant', 'plan'])
            ->where('status', 'unpaid')
            ->latest('created_at')
            ->limit(5)
            ->get();

        foreach ($unpaidInvoices as $inv) {
            $ts = $inv->created_at ?? Carbon::now();
            $items->push([
                'id' => 'inv_unpaid_' . $inv->id,
                'type' => 'invoice',
                'tone' => 'amber',
                'title' => 'Tagihan Baru (Unpaid): ' . ($inv->tenant->name ?? 'Tenant'),
                'message' => $inv->invoice_number . ' • Rp ' . number_format((float) $inv->amount, 0, ',', '.'),
                'url' => route('saas-admin.invoices', ['status' => 'unpaid']),
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => $ts->gt($lastReadAt),
            ]);
        }

        // 4. Recently Registered Tenants
        $tenants = Tenant::with('activeSubscription.plan')
            ->latest('created_at')
            ->limit(5)
            ->get();

        foreach ($tenants as $tenant) {
            $ts = $tenant->created_at ?? Carbon::now();
            $planName = $tenant->activeSubscription?->plan?->name ?? 'Starter';
            $items->push([
                'id' => 'tenant_' . $tenant->id,
                'type' => 'tenant',
                'tone' => 'blue',
                'title' => 'Tenant Baru Terdaftar',
                'message' => $tenant->name . ' • Paket ' . $planName,
                'url' => route('saas-admin.tenants'),
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => $ts->gt($lastReadAt),
            ]);
        }

        $sorted = $items->sortByDesc('timestamp')->take(12)->values();
        $unreadCount = $sorted->where('is_unread', true)->count();

        $unpaidInvoicesCount = BillingInvoice::where('status', 'unpaid')->count();
        $unreadLeadsCount = ContactMessage::where('is_read', false)->count();

        $sigParts = [
            ContactMessage::count(),
            (string) ContactMessage::max('updated_at'),
            Tenant::withTrashed()->count(),
            (string) Tenant::withTrashed()->max('updated_at'),
            BillingInvoice::count(),
            (string) BillingInvoice::max('updated_at'),
            Subscription::count(),
            (string) Subscription::max('updated_at'),
            Plan::count(),
            (string) Plan::max('updated_at'),
        ];

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $sorted,
            'sidebar_badges' => [
                'unpaid_invoices' => $unpaidInvoicesCount,
                'unread_leads' => $unreadLeadsCount,
            ],
            'state_signature' => md5(implode('|', $sigParts)),
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Build notification feed + state signature for Tenant Users (Admin, Kasir, Operator).
     */
    protected function pollTenantUser($user, Carbon $lastReadAt): JsonResponse
    {
        $tenant = $user->tenant;
        if (! $tenant) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
                'sidebar_badges' => [],
                'state_signature' => null,
                'server_time' => Carbon::now()->toIso8601String(),
            ]);
        }

        $items = collect();
        $isOperatorOnly = $user->role === 'operator';
        $statusLabels = [
            'diterima' => 'Diterima (Antrean Baru)',
            'dicuci' => 'Sedang Dicuci',
            'dikeringkan' => 'Sedang Dikeringkan',
            'disetrika' => 'Sedang Disetrika',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai & Diambil',
            'dibatalkan' => 'Dibatalkan',
            // Fallback aliases
            'received' => 'Diterima (Antrean Baru)',
            'washing' => 'Sedang Dicuci',
            'drying' => 'Sedang Dikeringkan',
            'ironing' => 'Sedang Disetrika',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai & Diambil',
        ];

        // 1. Subscription / Unpaid Billing Alert (for Admin / Kasir)
        if (! $isOperatorOnly) {
            $daysLeft = $tenant->daysRemaining();
            if ($daysLeft <= 3) {
                $items->push([
                    'id' => 'sub_exp_' . $tenant->id . '_' . $daysLeft,
                    'type' => 'billing',
                    'tone' => 'amber',
                    'title' => $daysLeft <= 0 ? 'Masa Aktif Langganan Habis' : "Langganan Berakhir {$daysLeft} Hari Lagi",
                    'message' => 'Segera perpanjang paket langganan agar operasional tetap berjalan lancar.',
                    'url' => route('billing.index'),
                    'timestamp' => Carbon::now()->subHours(2)->timestamp,
                    'time_ago' => 'Penting',
                    'is_unread' => Carbon::now()->subHours(2)->gt($lastReadAt),
                ]);
            }
        }

        // 2. Recent Orders Created (New Queue for Operator / Admin / Kasir)
        $recentOrders = Order::with(['customer', 'rack', 'createdBy'])
            ->where('tenant_id', $tenant->id)
            ->where('is_cancelled', false)
            ->latest('created_at')
            ->limit(6)
            ->get();

        foreach ($recentOrders as $order) {
            $ts = $order->created_at ?? Carbon::now();
            $customerName = $order->customer->full_name ?? 'Pelanggan';
            $cashierName = $order->createdBy->username ?? 'Kasir';
            $orderUrl = $isOperatorOnly ? route('orders.status') : route('orders.show', $order);

            $items->push([
                'id' => 'order_new_' . $order->id,
                'type' => 'order_new',
                'tone' => 'blue',
                'title' => 'Order Baru: ' . $order->order_number,
                'message' => $customerName . ' • Rp ' . number_format((float) $order->total_cost, 0, ',', '.') . ' (Input: ' . $cashierName . ')',
                'url' => $orderUrl,
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => $ts->gt($lastReadAt),
            ]);

            // If order is ready for pickup, push a high-visibility ready notification
            if (in_array($order->status, ['siap_diambil', 'ready'], true)) {
                $readyTs = $order->ready_at ? Carbon::parse($order->ready_at) : ($order->updated_at ?? Carbon::now());
                $rackInfo = $order->rack ? ' (Rak: ' . $order->rack->name . ')' : '';
                $items->push([
                    'id' => 'order_ready_' . $order->id . '_' . $readyTs->timestamp,
                    'type' => 'order_ready',
                    'tone' => 'emerald',
                    'title' => 'Cucian Siap Diambil: ' . $order->order_number,
                    'message' => $customerName . $rackInfo . ' siap diserahkan ke pelanggan.',
                    'url' => $orderUrl,
                    'timestamp' => $readyTs->timestamp,
                    'time_ago' => $readyTs->diffForHumans(),
                    'is_unread' => $readyTs->gt($lastReadAt),
                ]);
            }
        }

        // 3. Recent Order Status Updates from OrderStatusLog
        $recentLogs = OrderStatusLog::with(['order.customer', 'changedBy'])
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['dicuci', 'dikeringkan', 'disetrika', 'siap_diambil', 'selesai', 'washing', 'drying', 'ironing', 'completed'])
            ->latest('changed_at')
            ->limit(6)
            ->get();

        foreach ($recentLogs as $log) {
            if (! $log->order) {
                continue;
            }
            $ts = $log->changed_at ? Carbon::parse($log->changed_at) : Carbon::now();
            $statusText = $statusLabels[$log->status] ?? ucfirst(str_replace('_', ' ', $log->status));
            $actor = $log->changedBy->username ?? 'Staf';
            $orderUrl = $isOperatorOnly ? route('orders.status') : route('orders.show', $log->order_id);

            $items->push([
                'id' => 'order_log_' . $log->id,
                'type' => 'order_status',
                'tone' => in_array($log->status, ['siap_diambil', 'selesai', 'completed'], true) ? 'emerald' : 'blue',
                'title' => 'Status ' . $log->order->order_number . ': ' . $statusText,
                'message' => ($log->order->customer->full_name ?? 'Pelanggan') . ' • Diperbarui oleh ' . $actor,
                'url' => $orderUrl,
                'timestamp' => $ts->timestamp,
                'time_ago' => $ts->diffForHumans(),
                'is_unread' => $ts->gt($lastReadAt),
            ]);
        }

        // 4. Recent Payments (for Admin & Kasir)
        if (! $isOperatorOnly) {
            $recentPayments = Payment::with(['order.customer'])
                ->where('tenant_id', $tenant->id)
                ->latest('paid_at')
                ->limit(5)
                ->get();

            foreach ($recentPayments as $payment) {
                if (! $payment->order) {
                    continue;
                }
                $ts = $payment->paid_at ? Carbon::parse($payment->paid_at) : ($payment->created_at ?? Carbon::now());
                $items->push([
                    'id' => 'pay_' . $payment->id,
                    'type' => 'payment',
                    'tone' => 'emerald',
                    'title' => 'Pembayaran Masuk: ' . $payment->order->order_number,
                    'message' => ($payment->order->customer->full_name ?? 'Pelanggan') . ' • Rp ' . number_format((float) $payment->amount, 0, ',', '.') . ' (' . strtoupper($payment->method ?? 'TUNAI') . ')',
                    'url' => route('orders.show', $payment->order_id),
                    'timestamp' => $ts->timestamp,
                    'time_ago' => $ts->diffForHumans(),
                    'is_unread' => $ts->gt($lastReadAt),
                ]);
            }
        }

        $sorted = $items
            ->unique('id')
            ->sortByDesc('timestamp')
            ->take(12)
            ->values();

        $unreadCount = $sorted->where('is_unread', true)->count();

        $sigParts = [
            (string) $tenant->updated_at,
            (string) $tenant->status,
            Order::withTrashed()->where('tenant_id', $tenant->id)->count(),
            (string) Order::withTrashed()->where('tenant_id', $tenant->id)->max('updated_at'),
            (int) OrderStatusLog::where('tenant_id', $tenant->id)->max('id'),
            Payment::withTrashed()->where('tenant_id', $tenant->id)->count(),
            (string) Payment::withTrashed()->where('tenant_id', $tenant->id)->max('updated_at'),
            Customer::withTrashed()->where('tenant_id', $tenant->id)->count(),
            (string) Customer::withTrashed()->where('tenant_id', $tenant->id)->max('updated_at'),
            Rack::withTrashed()->where('tenant_id', $tenant->id)->count(),
            (string) Rack::withTrashed()->where('tenant_id', $tenant->id)->max('updated_at'),
            BillingInvoice::where('tenant_id', $tenant->id)->count(),
            (string) BillingInvoice::where('tenant_id', $tenant->id)->max('updated_at'),
            Subscription::where('tenant_id', $tenant->id)->count(),
            (string) Subscription::where('tenant_id', $tenant->id)->max('updated_at'),
        ];

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $sorted,
            'sidebar_badges' => [],
            'state_signature' => md5(implode('|', $sigParts)),
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }
}
