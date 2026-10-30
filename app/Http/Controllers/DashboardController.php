<?php

namespace App\Http\Controllers;

use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Production stats for operators
        if ($user->hasRole('operator')) {
            $statuses = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil'];

            $stats = [];
            foreach ($statuses as $status) {
                $stats[$status] = Order::where('status', $status)
                    ->where('is_cancelled', false)
                    ->count();
            }

            return view('dashboard', [
                'is_operator' => true,
                'stats' => $stats,
                'totalAktif' => array_sum($stats),
            ]);
        }

        // Comprehensive stats for admin
        if ($user->hasRole('admin')) {
            // Financial metrics
            $totalPendapatanHariIni = Order::where('payment_status', 'lunas')
                ->whereDate('completed_at', today())
                ->sum('total_cost');

            $totalPendapatanBulanIni = Order::where('payment_status', 'lunas')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('total_cost');

            // Operational metrics
            $jumlahOrderAktif = Order::whereNotIn('status', ['selesai'])
                ->where('is_cancelled', false)
                ->count();

            $jumlahOrderSiapDiambil = Order::where('status', 'siap_diambil')
                ->count();

            $jumlahOrderSelesai = Order::where('status', 'selesai')
                ->whereDate('completed_at', today())
                ->count();

            $totalOrderBulanIni = Order::whereMonth('received_date', now()->month)
                ->whereYear('received_date', now()->year)
                ->count();

            $totalPelanggan = Order::distinct('customer_id')->count();

            // Status breakdown for admin
            $statuses = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil'];
            $statusStats = [];
            foreach ($statuses as $status) {
                $statusStats[$status] = Order::where('status', $status)
                    ->where('is_cancelled', false)
                    ->count();
            }

            return view('dashboard', [
                'is_admin' => true,
                'is_operator' => false,
                'totalPendapatanHariIni' => $totalPendapatanHariIni,
                'totalPendapatanBulanIni' => $totalPendapatanBulanIni,
                'jumlahOrderAktif' => $jumlahOrderAktif,
                'jumlahOrderSiapDiambil' => $jumlahOrderSiapDiambil,
                'jumlahOrderSelesai' => $jumlahOrderSelesai,
                'totalOrderBulanIni' => $totalOrderBulanIni,
                'totalPelanggan' => $totalPelanggan,
                'statusStats' => $statusStats,
            ]);
        }

        // Financial and operational stats for kasir
        $is_operator = false;
        $is_admin = false;
        $totalPendapatanHariIni = Order::where('payment_status', 'lunas')
            ->whereDate('completed_at', today())
            ->sum('total_cost');

        $jumlahOrderAktif = Order::whereNotIn('status', ['selesai'])
            ->where('is_cancelled', false)
            ->count();

        $jumlahOrderSiapDiambil = Order::where('status', 'siap_diambil')
            ->count();

        return view('dashboard', compact(
            'totalPendapatanHariIni',
            'jumlahOrderAktif',
            'jumlahOrderSiapDiambil',
            'is_operator',
            'is_admin'
        ));
    }
}
