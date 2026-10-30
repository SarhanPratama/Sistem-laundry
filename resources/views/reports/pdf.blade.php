<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Laundry</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; }
        .page { padding: 30px 40px; }
        .header { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 14px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; color: #1d4ed8; }
        .header p  { font-size: 11px; color: #555; margin-top: 4px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1d4ed8; border-left: 4px solid #1d4ed8; padding-left: 8px; margin: 20px 0 10px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .summary-grid td { width: 33.33%; padding: 10px 12px; border: 1px solid #e5e7eb; vertical-align: top; }
        .card-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .card-green  { color: #16a34a; }
        .card-blue   { color: #2563eb; }
        .card-gray   { color: #374151; }
        .card-red    { color: #dc2626; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.data-table thead tr { background-color: #1d4ed8; color: #fff; }
        table.data-table thead th { padding: 8px 10px; text-align: left; font-size: 11px; font-weight: bold; }
        table.data-table thead th.right { text-align: right; }
        table.data-table tbody tr:nth-child(even) { background-color: #f9fafb; }
        table.data-table tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        table.data-table tbody td.right { text-align: right; }
        table.data-table tfoot tr { background-color: #eff6ff; }
        table.data-table tfoot td { padding: 8px 10px; font-size: 11px; font-weight: bold; border-top: 2px solid #1d4ed8; }
        table.data-table tfoot td.right { text-align: right; color: #16a34a; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: right; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>Laporan Sistem Laundry</h1>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section-title">Ringkasan</div>
    <table class="summary-grid">
        <tr>
            <td>
                <div class="card-label">Nilai Order</div>
                <div class="card-value card-blue">Rp {{ number_format($orderValueData['total'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="card-label">Kas Diterima</div>
                <div class="card-value card-green">Rp {{ number_format($cashFlowData['total'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="card-label">Piutang</div>
                <div class="card-value" style="color:#d97706;">Rp {{ number_format($receivables['outstanding'], 0, ',', '.') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="card-label">Total Order</div>
                <div class="card-value card-blue">{{ $orderSummary['total'] }}</div>
            </td>
            <td>
                <div class="card-label">Order Selesai</div>
                <div class="card-value card-gray">{{ $orderSummary['selesai'] }}</div>
            </td>
            <td>
                <div class="card-label">Order Dibatalkan</div>
                <div class="card-value card-red">{{ $orderSummary['dibatalkan'] }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Nilai Order &amp; Kas Diterima
        @if($period === 'daily') (Harian)
        @elseif($period === 'weekly') (Mingguan)
        @else (Bulanan)
        @endif
    </div>

    @if($periodKeys->isEmpty())
        <p style="color:#6b7280; font-size:11px;">Tidak ada data pada periode ini.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th class="right">Nilai Order</th>
                    <th class="right">Kas Diterima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periodKeys as $periodKey)
                    <tr>
                        <td>{{ $periodKey }}</td>
                        <td class="right">Rp {{ number_format($orderValueData['data'][$periodKey] ?? 0, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($cashFlowData['data'][$periodKey] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="right" style="color:#1d4ed8;">Rp {{ number_format($orderValueData['total'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($cashFlowData['total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <p style="font-size:10px; color:#6b7280; margin-bottom:10px;">
        Piutang: Rp {{ number_format($receivables['outstanding'], 0, ',', '.') }}
        dari {{ $receivables['orders_with_balance'] }} order yang belum lunas.
        Nilai order dihitung dari tanggal order masuk; kas diterima dihitung dari tanggal pembayaran.
    </p>

    <div class="section-title">Layanan Terpopuler</div>

    @if(empty($popularServices))
        <p style="color:#6b7280; font-size:11px;">Tidak ada data layanan pada periode ini.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Layanan</th>
                    <th class="right">Jumlah Pesanan</th>
                    <th class="right">Total Qty</th>
                    <th class="right">Nilai Order</th>
                </tr>
            </thead>
            <tbody>
                @foreach($popularServices as $i => $service)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $service['service_name'] }}</td>
                        <td class="right">{{ $service['count'] }}</td>
                        <td class="right">{{ number_format($service['total_quantity'], 2, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($service['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Sistem Informasi Manajemen Laundry &mdash; Laporan ini dibuat secara otomatis oleh sistem.</div>

</div>
</body>
</html>
