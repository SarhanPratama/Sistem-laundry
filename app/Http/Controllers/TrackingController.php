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

        $normalized = strtoupper(trim($request->order_number));

        $order = Order::withoutGlobalScopes()
            ->where(function ($query) use ($normalized) {
                $query->where('order_number', $normalized)
                      ->orWhereRaw('UPPER(order_number) = ?', [$normalized]);
            })
            ->first();

        if (!$order) {
            return back()
                ->withInput()
                ->with('error', 'Pesanan dengan nomor "' . $normalized . '" tidak ditemukan. Pastikan kode transaksi sesuai dengan yang tertera pada struk laundry Anda.');
        }

        return redirect()->route('tracking.show', $order->order_number);
    }

    /**
     * Menampilkan halaman tracking publik untuk pelanggan
     * Diakses tanpa login dengan nomor pesanan / order number
     */
    public function show($order_number)
    {
        $normalized = strtoupper(trim($order_number));

        // Cari order berdasarkan nomor pesanan (order_number) across platform safely
        $order = Order::withoutGlobalScopes()
            ->where(function ($query) use ($order_number, $normalized) {
                $query->where('order_number', $order_number)
                      ->orWhereRaw('UPPER(order_number) = ?', [$normalized]);
            })
            ->with(['customer', 'items.service.category', 'payments', 'statusLogs', 'rack', 'tenant'])
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
