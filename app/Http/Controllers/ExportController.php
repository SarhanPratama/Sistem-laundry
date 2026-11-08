<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Display data export overview page.
     */
    public function index()
    {
        $tenant = TenantContext::getTenant();
        $totalCustomers = Customer::count();
        $totalOrders    = Order::count();
        $totalPayments  = Payment::count();

        return view('export.index', compact('tenant', 'totalCustomers', 'totalOrders', 'totalPayments'));
    }

    /**
     * Export all customers to CSV.
     */
    public function exportCustomers(): StreamedResponse
    {
        $tenant = TenantContext::getTenant();
        $filename = 'customers_' . ($tenant?->slug ?? 'tenant') . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Nama Lengkap', 'Nomor Telepon', 'Alamat', 'Status Aktif', 'Tanggal Dibuat']);

            Customer::chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->full_name,
                        $c->phone_number,
                        $c->address ?? '-',
                        $c->is_active ? 'Aktif' : 'Nonaktif',
                        $c->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export all orders to CSV.
     */
    public function exportOrders(): StreamedResponse
    {
        $tenant = TenantContext::getTenant();
        $filename = 'orders_' . ($tenant?->slug ?? 'tenant') . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Nomor Order',
                'Nama Pelanggan',
                'Status Produksi',
                'Status Pembayaran',
                'Total Tagihan (Rp)',
                'Jumlah Terbayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Tanggal Masuk',
                'Estimasi Selesai',
                'Dibatalkan',
            ]);

            Order::with('customer')->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $o) {
                    $sisa = max(0, (float) $o->total_cost - (float) $o->amount_paid);
                    fputcsv($handle, [
                        $o->order_number,
                        $o->customer?->full_name ?? '-',
                        ucfirst($o->status),
                        ucfirst($o->payment_status),
                        $o->total_cost,
                        $o->amount_paid,
                        $sisa,
                        $o->received_date?->format('Y-m-d') ?? '-',
                        $o->estimated_done_date?->format('Y-m-d') ?? '-',
                        $o->is_cancelled ? 'Ya' : 'Tidak',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export all payments to CSV.
     */
    public function exportPayments(): StreamedResponse
    {
        $tenant = TenantContext::getTenant();
        $filename = 'payments_' . ($tenant?->slug ?? 'tenant') . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Transaksi',
                'Nomor Order',
                'Metode',
                'Nominal Masuk (Rp)',
                'Kembalian (Rp)',
                'Bersih Diterima (Rp)',
                'Jenis Pembayaran',
                'Waktu Bayar',
            ]);

            Payment::with('order')->chunk(200, function ($payments) use ($handle) {
                foreach ($payments as $p) {
                    $net = (float) $p->amount - (float) $p->change_amount;
                    fputcsv($handle, [
                        $p->id,
                        $p->order?->order_number ?? '-',
                        strtoupper($p->method),
                        $p->amount,
                        $p->change_amount,
                        $net,
                        $p->is_dp ? 'Uang Muka (DP)' : 'Pelunasan / Penuh',
                        $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}

