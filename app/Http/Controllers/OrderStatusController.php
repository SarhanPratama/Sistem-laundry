<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Rack;
use App\Services\NotificationService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $query = Order::with(['customer', 'rack'])
            ->where('is_cancelled', false);

        $orders = $query->latest()->get();
        $statuses = self::STATUS_SEQUENCE;
        $racks = Rack::where('is_active', true)->orderBy('name')->get();

        $user = auth()->user();
        $isReadOnly = $user->role !== 'admin'
            && !in_array($user->role, ['operator', 'kasir'], true)
            && ! $user->hasTenantPermission('update-order-status');

        return view('orders.status', compact('orders', 'statuses', 'isReadOnly', 'racks'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->is_cancelled) {
            return back()->with('error', 'Order yang sudah dibatalkan tidak dapat diperbarui statusnya.');
        }

        $newStatus = $request->input('status');

        $user = auth()->user();
        $isCustomRole = !in_array($user->role, ['admin', 'kasir', 'operator'], true);

        if ($isCustomRole) {
            if (!$user->hasTenantPermission('update-order-status')) {
                abort(403, 'Anda tidak memiliki tugas untuk memperbarui status order.');
            }
        } elseif ($user->role !== 'admin' && !$user->hasRole($this->requiredRoleFor($newStatus))) {
            abort(403, $newStatus === self::PICKUP_STATUS
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
            $tenantId = TenantContext::getTenantId();
            $request->validate([
                'rack_id' => [
                    'required',
                    Rule::exists('racks', 'id')->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
                ],
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

    /**
     * Pindahkan / perbarui lokasi rak penyimpanan untuk order yang berstatus siap_diambil.
     */
    public function updateRack(Request $request, Order $order)
    {
        if ($order->is_cancelled || $order->status !== 'siap_diambil') {
            return back()->with('error', 'Lokasi rak hanya dapat diubah pada order yang berstatus Siap Diambil.');
        }

        $tenantId = TenantContext::getTenantId();
        $validated = $request->validate([
            'rack_id' => [
                'required',
                Rule::exists('racks', 'id')->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
            ],
        ], [
            'rack_id.required' => 'Silakan pilih Lokasi Rak penyimpanan.',
            'rack_id.exists' => 'Lokasi Rak yang dipilih tidak valid.',
        ]);

        $order->update(['rack_id' => $validated['rack_id']]);
        $rack = Rack::find($validated['rack_id']);

        return back()->with('success', 'Lokasi rak berhasil diperbarui menjadi "' . ($rack->name ?? '-') . '".');
    }
}
