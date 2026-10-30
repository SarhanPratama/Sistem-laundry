<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Nilai order (omzet/tagihan) berdasarkan tanggal order masuk.
     *
     * Metrik ini menghitung total tagihan dari seluruh order yang tidak
     * dibatalkan pada periode tersebut, terlepas dari sudah dibayar atau belum.
     * Metrik ini BUKAN kas yang diterima — gunakan getCashFlowReport() untuk
     * uang yang benar-benar masuk.
     *
     * @param  string  $period  'daily' | 'weekly' | 'monthly'
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate  Y-m-d
     * @return array{total: float, data: array<string, float>}
     */
    public function getOrderValueReport(string $period, string $startDate, string $endDate): array
    {
        $groupFormat = $this->periodExpression('received_date', $period);

        $rows = Order::where('is_cancelled', false)
            ->whereDate('received_date', '>=', $startDate)
            ->whereDate('received_date', '<=', $endDate)
            ->select(
                DB::raw("{$groupFormat} as period_key"),
                DB::raw('SUM(total_cost) as order_value')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $data = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $data[$row->period_key] = (float) $row->order_value;
            $total += (float) $row->order_value;
        }

        return [
            'total' => $total,
            'data' => $data,
        ];
    }

    /**
     * @deprecated Gunakan getOrderValueReport(). Method ini dipertahankan
     *             hanya untuk kompatibilitas dan tetap mengembalikan nilai order.
     *
     * @return array{total: float, data: array<string, float>}
     */
    public function getRevenueReport(string $period, string $startDate, string $endDate): array
    {
        return $this->getOrderValueReport($period, $startDate, $endDate);
    }

    /**
     * Laporan kas masuk (uang yang benar-benar diterima) per periode.
     *
     * Berbasis riwayat pembayaran (`payments.paid_at`) dan menjumlahkan nominal
     * bersih yang diterima (`amount - change_amount`). Hanya pembayaran untuk
     * order yang tidak dibatalkan yang dihitung. Karena memakai tanggal bayar,
     * DP dan pelunasan tercatat pada periode masing-masing.
     *
     * @param  string  $period  'daily' | 'weekly' | 'monthly'
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate  Y-m-d
     * @return array{total: float, data: array<string, float>}
     */
    public function getCashFlowReport(string $period, string $startDate, string $endDate): array
    {
        $query = Payment::whereHas('order', function ($q) {
            $q->where('is_cancelled', false);
        })
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate);

        $groupFormat = $this->periodExpression('paid_at', $period);

        $rows = $query
            ->select(
                DB::raw("{$groupFormat} as period_key"),
                DB::raw('SUM(amount - change_amount) as cash_received')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $data = [];
        $total = 0.0;
        foreach ($rows as $row) {
            $data[$row->period_key] = (float) $row->cash_received;
            $total += (float) $row->cash_received;
        }

        return [
            'total' => $total,
            'data' => $data,
        ];
    }

    /**
     * Piutang (sisa tagihan) atas order yang masuk pada periode tertentu.
     *
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate  Y-m-d
     * @return array{total_billed: float, total_paid: float, outstanding: float, orders_with_balance: int}
     */
    public function getReceivablesReport(string $startDate, string $endDate): array
    {
        $row = Order::where('is_cancelled', false)
            ->whereDate('received_date', '>=', $startDate)
            ->whereDate('received_date', '<=', $endDate)
            ->selectRaw('SUM(total_cost) as total_billed')
            ->selectRaw('SUM(amount_paid) as total_paid')
            ->selectRaw('SUM(CASE WHEN total_cost > amount_paid THEN total_cost - amount_paid ELSE 0 END) as outstanding')
            ->selectRaw('SUM(CASE WHEN total_cost > amount_paid THEN 1 ELSE 0 END) as orders_with_balance')
            ->first();

        return [
            'total_billed' => (float) ($row->total_billed ?? 0),
            'total_paid' => (float) ($row->total_paid ?? 0),
            'outstanding' => (float) ($row->outstanding ?? 0),
            'orders_with_balance' => (int) ($row->orders_with_balance ?? 0),
        ];
    }

    /**
     * Get order summary counts for the given date range.
     *
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate  Y-m-d
     * @return array{total: int, selesai: int, dibatalkan: int, aktif: int}
     */
    public function getOrderSummary(string $startDate, string $endDate): array
    {
        $base = Order::whereDate('received_date', '>=', $startDate)
            ->whereDate('received_date', '<=', $endDate);

        $total = (clone $base)->count();
        $selesai = (clone $base)->where('status', 'selesai')->count();
        $dibatalkan = (clone $base)->where('is_cancelled', true)->count();
        $aktif = (clone $base)->where('status', '!=', 'selesai')
            ->where('is_cancelled', false)
            ->count();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'dibatalkan' => $dibatalkan,
            'aktif' => $aktif,
        ];
    }

    /**
     * Get popular services ranked by order count.
     *
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate  Y-m-d
     * @return array<int, array{service_name: string, count: int, total_quantity: float, total_revenue: float}>
     */
    public function getPopularServices(string $startDate, string $endDate, int $limit = 10): array
    {
        $rows = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->where('orders.is_cancelled', false)
            ->whereDate('orders.received_date', '>=', $startDate)
            ->whereDate('orders.received_date', '<=', $endDate)
            ->select(
                'services.name as service_name',
                DB::raw('COUNT(order_items.id) as count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.service_id', 'services.name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'service_name' => $row->service_name,
            'count' => (int) $row->count,
            'total_quantity' => (float) $row->total_quantity,
            'total_revenue' => (float) $row->total_revenue,
        ])->toArray();
    }

    /**
     * Build a database-specific period grouping expression.
     */
    private function periodExpression(string $column, string $period): string
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        $format = match ($period) {
            'weekly' => $isSqlite ? '%Y-W%W' : '%Y-W%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return $isSqlite
            ? "STRFTIME('{$format}', {$column})"
            : "DATE_FORMAT({$column}, '{$format}')";
    }
}
