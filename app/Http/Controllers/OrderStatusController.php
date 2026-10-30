<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Throwable;

class OrderStatusController extends Controller
{
    const STATUS_SEQUENCE = [
        'diterima',
        'dicuci',
        'dikeringkan',
        'disetrika',
        'siap_diambil',
        'selesai',
    ];

    /**
     * Status yang menandai pelanggan sudah mengambil barang, hanya kasir yang boleh.
     */
    const PICKUP_STATUS = 'selesai';

    /**
     * Operator menjalankan tahap produksi, kasir menutup order saat pelanggan ambil barang.
     */
    public function requiredRoleFor(string $status): string
    {
        return $status === self::PICKUP_STATUS ? 'kasir' : 'operator';
    }

    public function index(Request $request)
    {
        $query = Order::with(['customer'])
            ->where('is_cancelled', false);

        $status = $request->input('status');

        if ($status) {
            // Filter eksplisit dihormati, termasuk `selesai` untuk melihat histori
            $query->where('status', $status);
        } else {
            // Tanpa filter, papan kerja hanya menampilkan order yang masih berjalan
            $query->where('status', '!=', self::PICKUP_STATUS);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('received_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('received_date', '<=', $dateTo);
        }

        if ($customerName = $request->input('customer_name')) {
            $query->whereHas('customer', function ($q) use ($customerName) {
                $q->where('full_name', 'LIKE', '%' . $customerName . '%');
            });
        }

        $orders   = $query->latest()->paginate(10)->withQueryString();
        $statuses = self::STATUS_SEQUENCE;

        // Admin hanya memantau, tidak mengubah status order
        $isReadOnly = ! auth()->user()->hasRole(['operator', 'kasir']);

        return view('orders.status', compact('orders', 'statuses', 'isReadOnly'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->is_cancelled) {
            return back()->with('error', 'Order yang sudah dibatalkan tidak dapat diperbarui statusnya.');
        }

        $newStatus = $request->input('status');

        // Produksi (diterima -> siap_diambil) hanya oleh operator,
        // penutupan order (siap_diambil -> selesai) hanya oleh kasir.
        $requiredRole = $this->requiredRoleFor($newStatus);

        if (!auth()->user()->hasRole($requiredRole)) {
            abort(403, $requiredRole === 'kasir'
                ? 'Order hanya dapat ditandai selesai oleh kasir ketika pelanggan mengambil barang.'
                : 'Tahapan produksi hanya dapat diubah oleh operator.');
        }

        $currentIndex = array_search($order->status, self::STATUS_SEQUENCE);
        $newIndex     = array_search($newStatus, self::STATUS_SEQUENCE);

        if ($currentIndex === false || $newIndex === false) {
            return back()->with('error', 'Status tidak valid.');
        }

        if ($newIndex !== $currentIndex + 1) {
            $expectedNext = self::STATUS_SEQUENCE[$currentIndex + 1] ?? null;
            $message = $expectedNext
                ? 'Transisi status tidak berurutan. Status saat ini adalah "' . $order->status . '", status berikutnya yang valid adalah "' . $expectedNext . '".'
                : 'Order sudah berada di status akhir dan tidak dapat diperbarui lagi.';

            return back()->with('error', $message);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === self::PICKUP_STATUS && $order->payment_status !== 'lunas') {
            return redirect()->route('payments.index', $order)
                ->with('error', 'Status tidak dapat diubah ke \'Selesai\' karena pembayaran belum lunas. Silakan selesaikan pembayaran terlebih dahulu.');
        }

        if ($newStatus === 'siap_diambil') {
            $request->validate([
                'rack_id' => 'required|exists:racks,id',
            ], [
                'rack_id.required' => 'Anda harus menentukan Lokasi Rak saat mengubah status ke Siap Diambil.',
                'rack_id.exists' => 'Lokasi Rak yang dipilih tidak valid.'
            ]);

            $updateData['ready_at'] = now();
            $updateData['rack_id'] = $request->input('rack_id');
        }

        if ($newStatus === self::PICKUP_STATUS) {
            $updateData['completed_at'] = now();
            $updateData['completed_by'] = auth()->id();
            $updateData['rack_id'] = null; // Bebaskan rak saat pesanan sudah selesai
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $updateData, $newStatus) {
            $order->update($updateData);

            // Log the status change
            OrderStatusLog::create([
                'order_id'   => $order->id,
                'status'     => $newStatus,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        });

        // Dispatch notification when order is ready for pickup
        if ($newStatus === 'siap_diambil') {
            try {
                if (class_exists(NotificationService::class)) {
                    $notificationService = new NotificationService();
                    $notificationService->send($order);
                }
            } catch (Throwable $e) {
                return back()
                    ->with('success', 'Status order berhasil diperbarui menjadi "' . ucfirst(str_replace('_', ' ', $newStatus)) . '".')
                    ->with('warning', 'Notifikasi kepada pelanggan gagal dikirim (Sistem belum siap).');
            }
        }

        return back()->with('success', 'Status order berhasil diperbarui menjadi "' . ucfirst(str_replace('_', ' ', $newStatus)) . '".');
    }
}
