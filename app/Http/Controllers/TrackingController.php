<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Menampilkan halaman pencarian resi
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Handle proses pencarian
     */
    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string'
        ]);

        $order = Order::where('order_number', $request->order_number)->first();

        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan. Silakan periksa kembali nomor pesanan Anda.');
        }

        return redirect()->route('tracking.show', $order->order_number);
    }

    /**
     * Menampilkan halaman tracking publik untuk pelanggan
     * Diakses tanpa login dengan nomor pesanan / order number
     */
    public function show($order_number)
    {
        // Cari order berdasarkan nomor pesanan (order_number)
        $order = Order::where('order_number', $order_number)
            ->with(['customer', 'items', 'payments', 'statusLogs', 'rack'])
            ->firstOrFail();

        // Tentukan status icon dan warna
        $statusMap = [
            'diterima' => ['icon' => '📋', 'color' => 'blue', 'label' => 'Diterima'],
            'dicuci' => ['icon' => '🫧', 'color' => 'yellow', 'label' => 'Dicuci'],
            'dikeringkan' => ['icon' => '☀️', 'color' => 'orange', 'label' => 'Dikeringkan'],
            'disetrika' => ['icon' => '👔', 'color' => 'purple', 'label' => 'Disetrika'],
            'siap_diambil' => ['icon' => '✅', 'color' => 'green', 'label' => 'Siap Diambil'],
            'selesai' => ['icon' => '🎉', 'color' => 'gray', 'label' => 'Selesai'],
            'dibatalkan' => ['icon' => '❌', 'color' => 'red', 'label' => 'Dibatalkan'],
        ];

        $currentStatus = $statusMap[$order->status] ?? ['icon' => '❓', 'color' => 'gray', 'label' => 'Status Tidak Dikenal'];

        // Hitung durasi/timeline
        $timeline = [
            ['status' => 'diterima', 'label' => 'Diterima', 'time' => $order->created_at],
            ['status' => 'dicuci', 'label' => 'Dicuci', 'time' => null],
            ['status' => 'dikeringkan', 'label' => 'Dikeringkan', 'time' => null],
            ['status' => 'disetrika', 'label' => 'Disetrika', 'time' => null],
            ['status' => 'siap_diambil', 'label' => 'Siap Diambil', 'time' => null],
            ['status' => 'selesai', 'label' => 'Selesai', 'time' => $order->completed_at],
        ];

        // Update timeline dengan data dari status logs
        foreach ($order->statusLogs as $log) {
            foreach ($timeline as &$item) {
                if ($item['status'] === $log->status && !$item['time']) {
                    $item['time'] = $log->changed_at;
                }
            }
        }

        // Format huruf pertama status menjadi uppercase
        $orderStatus = ucfirst(str_replace('_', ' ', $order->status));

        return view('tracking.show', compact('order', 'currentStatus', 'timeline', 'orderStatus'));
    }
}
