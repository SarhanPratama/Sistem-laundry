<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get revenue report grouped by period.
     *
     * @param  string  $period     'daily' | 'weekly' | 'monthly'
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate    Y-m-d
     * @return array{total: float, data: array<string, float>}
     */
    public function getRevenueReport(string $period, string $startDate, string $endDate): array
    {
        $query = Order::where('payment_status', 'lunas')
            ->where('is_cancelled', false)
            ->whereDate('received_date', '>=', $startDate)
            ->whereDate('received_date', '<=', $endDate);

        switch ($period) {
            case 'weekly':
                $groupFormat = "DATE_FORMAT(received_date, '%Y-W%u')";
                break;
            case 'monthly':
                $groupFormat = "DATE_FORMAT(received_date, '%Y-%m')";
                break;
            default: // daily
                $groupFormat = "DATE_FORMAT(received_date, '%Y-%m-%d')";
                break;
        }

        // Fallback for SQLite (used in testing)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            switch ($period) {
                case 'weekly':
                    $groupFormat = "STRFTIME('%Y-W%W', received_date)";
                    break;
                case 'monthly':
                    $groupFormat = "STRFTIME('%Y-%m', received_date)";
                    break;
                default:
                    $groupFormat = "STRFTIME('%Y-%m-%d', received_date)";
                    break;
            }
        }

        $rows = $query->select(
                DB::raw("{$groupFormat} as period_key"),
                DB::raw('SUM(total_cost) as revenue')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $data  = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $data[$row->period_key] = (float) $row->revenue;
            $total += (float) $row->revenue;
        }

        return [
            'total' => $total,
            'data'  => $data,
        ];
    }

    /**
     * Get order summary counts for the given date range.
     *
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate    Y-m-d
     * @return array{total: int, selesai: int, dibatalkan: int, aktif: int}
     */
    public function getOrderSummary(string $startDate, string $endDate): array
    {
        $base = Order::whereDate('received_date', '>=', $startDate)
            ->whereDate('received_date', '<=', $endDate);

        $total      = (clone $base)->count();
        $selesai    = (clone $base)->where('status', 'selesai')->count();
        $dibatalkan = (clone $base)->where('is_cancelled', true)->count();
        $aktif      = (clone $base)->where('status', '!=', 'selesai')
                                   ->where('is_cancelled', false)
                                   ->count();

        return [
            'total'      => $total,
            'selesai'    => $selesai,
            'dibatalkan' => $dibatalkan,
            'aktif'      => $aktif,
        ];
    }

    /**
     * Get popular services ranked by order count.
     *
     * @param  string  $startDate  Y-m-d
     * @param  string  $endDate    Y-m-d
     * @param  int     $limit
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
            'service_name'   => $row->service_name,
            'count'          => (int) $row->count,
            'total_quantity' => (float) $row->total_quantity,
            'total_revenue'  => (float) $row->total_revenue,
        ])->toArray();
    }
}
