<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('saas-admin.index');
        }

        // Production stats for operators
        if ($user->hasRole('operator')) {
            $statuses = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil'];

            $stats = [];
            foreach ($statuses as $status) {
                $stats[$status] = Order::where('status', $status)
                    ->where('is_cancelled', false)
                    ->count();
            }

            $productionStages = ['diterima', 'dicuci', 'dikeringkan', 'disetrika'];

            $orderPrioritasCount = Order::whereIn('status', $productionStages)
                ->where('is_cancelled', false)
                ->whereDate('estimated_done_date', '<=', today())
                ->count();

            $selesaiProduksiHariIni = Order::where('is_cancelled', false)
                ->whereDate('ready_at', today())
                ->count();

            $antreanProduksi = Order::with(['customer', 'items.service'])
                ->whereIn('status', $productionStages)
                ->where('is_cancelled', false)
                ->orderBy('estimated_done_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->take(8)
                ->get();

            $racks = \App\Models\Rack::withCount([
                'orders' => fn ($q) => $q->where('is_cancelled', false)->where('status', 'siap_diambil'),
            ])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('dashboard', [
                'is_operator' => true,
                'stats' => $stats,
                'totalAktif' => array_sum($stats),
                'orderPrioritasCount' => $orderPrioritasCount,
                'selesaiProduksiHariIni' => $selesaiProduksiHariIni,
                'antreanProduksi' => $antreanProduksi,
                'racks' => $racks,
            ]);
        }

        // Comprehensive stats for admin
        if ($user->hasRole('admin')) {
            $basePaymentsToday = Payment::whereHas('order', fn ($q) => $q->where('is_cancelled', false))
                ->whereDate('paid_at', today());

            // Financial metrics (realized net cash flow based on paid_at)
            $totalPendapatanHariIni = (float) (clone $basePaymentsToday)
                ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
                ->value('net_total');

            $kasTunaiHariIni = (float) (clone $basePaymentsToday)
                ->where('method', 'tunai')
                ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
                ->value('net_total');

            $transferHariIni = (float) (clone $basePaymentsToday)
                ->where('method', 'transfer')
                ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
                ->value('net_total');

            $totalPendapatanBulanIni = (float) Payment::whereHas('order', fn ($q) => $q->where('is_cancelled', false))
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
                ->value('net_total');

            $unpaidOrdersQuery = Order::where('is_cancelled', false)
                ->whereIn('payment_status', ['belum_bayar', 'dp']);

            $orderBelumLunasCount = (clone $unpaidOrdersQuery)->count();
            $totalPiutang = (float) (clone $unpaidOrdersQuery)
                ->selectRaw('COALESCE(SUM(total_cost - amount_paid), 0) as sisa')
                ->value('sisa');

            // Operational metrics
            $jumlahOrderAktif = Order::whereNotIn('status', ['selesai'])
                ->where('is_cancelled', false)
                ->count();

            $jumlahOrderSiapDiambil = Order::where('status', 'siap_diambil')
                ->where('is_cancelled', false)
                ->count();

            $jumlahOrderSelesai = Order::where('status', 'selesai')
                ->where('is_cancelled', false)
                ->whereDate('completed_at', today())
                ->count();

            $totalOrderBulanIni = Order::where('is_cancelled', false)
                ->whereMonth('received_date', now()->month)
                ->whereYear('received_date', now()->year)
                ->count();

            $totalPelanggan = \App\Models\Customer::where('is_active', true)->count();

            $orderTerlambatCount = Order::whereNotIn('status', ['selesai'])
                ->where('is_cancelled', false)
                ->whereDate('estimated_done_date', '<', today())
                ->count();

            // Status breakdown for admin
            $statuses = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil'];
            $statusStats = [];
            foreach ($statuses as $status) {
                $statusStats[$status] = Order::where('status', $status)
                    ->where('is_cancelled', false)
                    ->count();
            }

            // 7-Day Revenue Trend
            $revenueTrend7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = today()->subDays($i);
                $dayAmount = (float) Payment::whereHas('order', fn ($q) => $q->where('is_cancelled', false))
                    ->whereDate('paid_at', $day)
                    ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
                    ->value('net_total');

                $revenueTrend7Days[] = [
                    'date' => $day->format('d/m'),
                    'day_name' => $day->translatedFormat('D'),
                    'amount' => $dayAmount,
                    'is_today' => $i === 0,
                ];
            }

            // Top 5 Services This Month
            $topServicesBulanIni = \App\Models\OrderItem::with('service')
                ->whereHas('order', fn ($q) => $q->where('is_cancelled', false)
                    ->whereMonth('received_date', now()->month)
                    ->whereYear('received_date', now()->year))
                ->selectRaw('service_id, SUM(quantity) as total_qty, SUM(subtotal) as total_omzet, COUNT(DISTINCT order_id) as total_orders')
                ->groupBy('service_id')
                ->orderByDesc('total_omzet')
                ->take(5)
                ->get();

            // Recent Orders
            $recentOrdersAdmin = Order::with(['customer', 'rack'])
                ->where('is_cancelled', false)
                ->latest()
                ->take(6)
                ->get();

            return view('dashboard', [
                'is_admin' => true,
                'is_operator' => false,
                'totalPendapatanHariIni' => $totalPendapatanHariIni,
                'kasTunaiHariIni' => $kasTunaiHariIni,
                'transferHariIni' => $transferHariIni,
                'totalPendapatanBulanIni' => $totalPendapatanBulanIni,
                'orderBelumLunasCount' => $orderBelumLunasCount,
                'totalPiutang' => $totalPiutang,
                'jumlahOrderAktif' => $jumlahOrderAktif,
                'jumlahOrderSiapDiambil' => $jumlahOrderSiapDiambil,
                'jumlahOrderSelesai' => $jumlahOrderSelesai,
                'totalOrderBulanIni' => $totalOrderBulanIni,
                'totalPelanggan' => $totalPelanggan,
                'orderTerlambatCount' => $orderTerlambatCount,
                'statusStats' => $statusStats,
                'revenueTrend7Days' => $revenueTrend7Days,
                'topServicesBulanIni' => $topServicesBulanIni,
                'recentOrdersAdmin' => $recentOrdersAdmin,
            ]);
        }

        // Financial and operational stats for kasir
        $is_operator = false;
        $is_admin = false;

        $basePaymentsToday = Payment::whereHas('order', fn ($q) => $q->where('is_cancelled', false))
            ->whereDate('paid_at', today());

        $totalPendapatanHariIni = (float) (clone $basePaymentsToday)
            ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
            ->value('net_total');

        $kasTunaiHariIni = (float) (clone $basePaymentsToday)
            ->where('method', 'tunai')
            ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
            ->value('net_total');

        $transferHariIni = (float) (clone $basePaymentsToday)
            ->where('method', 'transfer')
            ->selectRaw('COALESCE(SUM(amount - change_amount), 0) as net_total')
            ->value('net_total');

        $jumlahOrderAktif = Order::whereNotIn('status', ['selesai'])
            ->where('is_cancelled', false)
            ->count();

        $jumlahOrderSiapDiambil = Order::where('status', 'siap_diambil')
            ->where('is_cancelled', false)
            ->count();

        $unpaidOrdersQuery = Order::where('is_cancelled', false)
            ->whereIn('payment_status', ['belum_bayar', 'dp']);

        $orderBelumLunasCount = (clone $unpaidOrdersQuery)->count();
        $totalPiutang = (float) (clone $unpaidOrdersQuery)
            ->selectRaw('COALESCE(SUM(total_cost - amount_paid), 0) as sisa')
            ->value('sisa');

        $orderTerlambatCount = Order::whereNotIn('status', ['selesai'])
            ->where('is_cancelled', false)
            ->whereDate('estimated_done_date', '<', today())
            ->count();

        $ordersSiapDiambil = Order::with(['customer', 'rack'])
            ->where('status', 'siap_diambil')
            ->where('is_cancelled', false)
            ->latest()
            ->take(6)
            ->get();

        $ordersHariIni = Order::with(['customer'])
            ->where('is_cancelled', false)
            ->whereDate('received_date', today())
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'totalPendapatanHariIni',
            'kasTunaiHariIni',
            'transferHariIni',
            'jumlahOrderAktif',
            'jumlahOrderSiapDiambil',
            'orderBelumLunasCount',
            'totalPiutang',
            'orderTerlambatCount',
            'ordersSiapDiambil',
            'ordersHariIni',
            'is_operator',
            'is_admin'
        ));
    }
}
