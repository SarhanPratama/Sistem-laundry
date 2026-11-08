@extends('layouts.app')
@section('title', 'Detail Order')
@section('page-title', 'Detail Order')
@section('content')
    @php
        $statusSequence = ['diterima', 'dicuci', 'dikeringkan', 'disetrika', 'siap_diambil', 'selesai'];
        $statusLabels = [
            'diterima' => 'Diterima',
            'dicuci' => 'Dicuci',
            'dikeringkan' => 'Dikeringkan',
            'disetrika' => 'Disetrika',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai',
        ];
        $currentIndex = array_search($order->status, $statusSequence);
        $payLabels = ['belum_bayar' => 'Belum Bayar', 'dp' => 'DP', 'lunas' => 'LUNAS'];
        $payColors = ['belum_bayar' => 'text-red-600', 'dp' => 'text-yellow-600', 'lunas' => 'text-green-600'];
        $words = explode(' ', trim($order->customer->full_name ?? 'U'));
        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        $totalOrders = $order->customer->orders->count();
        // Build status history from order_status_logs (accurate timestamps)
        $statusHistory = $order->statusLogs
            ->map(function ($log) use ($statusLabels) {
                $isCancelled = $log->status === 'dibatalkan';
                return [
                    'label' => $isCancelled ? 'Dibatalkan' : $statusLabels[$log->status] ?? ucfirst($log->status),
                    'time' => $log->changed_at,
                    'by' => $log->changedBy->username ?? null,
                    'active' => true,
                    'cancelled' => $isCancelled,
                ];
            })
            ->toArray();
        $nextStatus =
            !$order->is_cancelled && $currentIndex !== false && isset($statusSequence[$currentIndex + 1])
                ? $statusSequence[$currentIndex + 1]
                : null;
        $methodLabels = ['tunai' => 'Tunai', 'transfer' => 'Transfer'];
        $totalTendered = $order->total_tendered;
        $totalChange = $order->total_change;
        $remaining = $order->remaining_amount;
        // Running applied amount after each transaction, oldest first
        $payments = $order->payments->sortBy('paid_at')->values();
        $runningPaid = 0.0;
        $paymentRows = $payments->map(function ($payment) use (&$runningPaid) {
            $applied = (float) $payment->amount - (float) $payment->change_amount;
            $runningPaid += $applied;
            return [
                'payment' => $payment,
                'applied' => $applied,
                'running' => $runningPaid,
            ];
        });

        // Format WhatsApp number & Nota message
        $rawPhone = preg_replace('/[^0-9]/', '', $order->customer->phone_number ?? '');
        $waPhone = str_starts_with($rawPhone, '0')
            ? '62' . substr($rawPhone, 1)
            : (str_starts_with($rawPhone, '62') ? $rawPhone : ($rawPhone ? '62' . $rawPhone : ''));
        $itemLines = $order->items->map(function ($item) {
            return '- ' . ($item->service->name ?? 'Layanan') . ' (' . $item->quantity . ' ' . ($item->service->unit ?? '') . '): Rp ' . number_format($item->subtotal, 0, ',', '.');
        })->implode("\n");
        $trackingUrl = route('tracking.show', $order->order_number);
        $waText = "*NOTA LAUNDRY - " . ($appStoreName ?? 'Laundiqo') . "*\n"
            . "No. Order: *" . $order->order_number . "*\n"
            . "Pelanggan: " . ($order->customer->full_name ?? '-') . "\n"
            . "Status Cucian: *" . ($statusLabels[$order->status] ?? ucfirst($order->status)) . "*\n"
            . ($order->rack ? "Lokasi Rak: *" . $order->rack->name . "*\n" : "")
            . "\n*Rincian Layanan:*\n" . $itemLines . "\n\n"
            . "Total Tagihan: *Rp " . number_format($order->total_cost, 0, ',', '.') . "*\n"
            . "Sudah Dibayar: Rp " . number_format($order->amount_paid, 0, ',', '.') . "\n"
            . ($remaining > 0 ? "Sisa Tagihan: *Rp " . number_format($remaining, 0, ',', '.') . "*\n" : "Status Bayar: *LUNAS*\n")
            . "\nLacak status cucian Anda secara real-time di:\n" . $trackingUrl;
        $waUrl = $waPhone ? ('https://wa.me/' . $waPhone . '?text=' . urlencode($waText)) : null;
    @endphp

    <div class="mx-auto">

        {{-- Top bar --}}
        <div class="flex items-center gap-3 mb-6 print:hidden">
            <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Order Details</h1>
            @if (!$order->is_cancelled)
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                {{ $order->status === 'diterima' ? 'bg-blue-100 text-blue-700' : '' }}
                {{ $order->status === 'dicuci' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $order->status === 'dikeringkan' ? 'bg-orange-100 text-orange-700' : '' }}
                {{ $order->status === 'disetrika' ? 'bg-purple-100 text-purple-700' : '' }}
                {{ $order->status === 'siap_diambil' ? 'bg-green-100 text-green-700' : '' }}
                {{ $order->status === 'selesai' ? 'bg-gray-100 text-gray-600' : '' }}">
                    {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                </span>
            @else
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">DIBATALKAN</span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- LEFT COLUMN (2/3) --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Order header card --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Order ID</p>
                            <p class="text-2xl font-bold font-mono text-gray-900">{{ $order->order_number }}</p>
                            @if ($order->rack)
                                <p
                                    class="mt-2 text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Lokasi Rak: {{ $order->rack->name }}{{ $order->rack->location_description ? ' (' . $order->rack->location_description . ')' : '' }}
                                </p>
                            @elseif ($order->is_cancelled)
                                <p
                                    class="mt-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    Lokasi Rak: Order Dibatalkan
                                </p>
                            @elseif ($order->status === 'selesai')
                                <p
                                    class="mt-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Lokasi Rak: Sudah diambil pelanggan
                                </p>
                            @else
                                <p
                                    class="mt-2 text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 px-3 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Lokasi Rak: Belum ditempatkan (Tahap {{ $statusLabels[$order->status] ?? ucfirst($order->status) }})
                                </p>
                            @endif
                        </div>
                        <div class="flex gap-6 text-right">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Date</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $order->received_date->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Est. Pickup
                                </p>
                                <p
                                    class="text-sm font-semibold {{ $order->estimated_done_date->isPast() && !in_array($order->status, ['selesai']) && !$order->is_cancelled ? 'text-red-600' : 'text-blue-600' }}">
                                    {{ $order->estimated_done_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Status progress stepper --}}
                    <div class="flex items-center overflow-x-auto overflow-y-hidden pb-4 -mb-4 scrollbar-hide"
                        style="scrollbar-width: none; -ms-overflow-style: none;">
                        <style>
                            .scrollbar-hide::-webkit-scrollbar {
                                display: none;
                            }
                        </style>
                        @foreach ($statusSequence as $i => $s)
                            @php
                                $reached = !$order->is_cancelled && $currentIndex !== false && $currentIndex >= $i;
                                $isCurrent = !$order->is_cancelled && $order->status === $s;
                                $isCompletedFinal = $s === 'selesai' && $isCurrent;
                            @endphp
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center border-2
                                {{ $isCompletedFinal ? 'bg-green-500 border-green-500 text-white shadow-md' : ($isCurrent ? 'bg-blue-600 border-blue-600 text-white shadow-md' : ($reached ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-gray-200 text-gray-300')) }}">
                                    @if ($reached && (!$isCurrent || $isCompletedFinal))
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <div class="w-2 h-2 rounded-full {{ $isCurrent ? 'bg-white' : 'bg-gray-300' }}">
                                        </div>
                                    @endif
                                </div>
                                <p
                                    class="text-xs mt-1.5 font-medium text-center leading-tight w-14 mx-3 text-nowrap
                                {{ $isCompletedFinal ? 'text-green-600' : ($isCurrent ? 'text-blue-600' : ($reached ? 'text-green-600' : 'text-gray-400')) }}">
                                    {{ strtoupper(str_replace('_', ' ', $s)) }}
                                </p>
                            </div>
                            @if (!$loop->last)
                                @php
                                    $connectorReached =
                                        !$order->is_cancelled && $currentIndex !== false && $currentIndex > $i;
                                    $connectorActive =
                                        !$order->is_cancelled && $currentIndex !== false && $currentIndex === $i;
                                @endphp
                                <div
                                    class="status-connector flex-1 mx-1 mb-5 {{ $connectorReached ? 'status-connector-reached' : '' }} {{ $connectorActive ? 'status-connector-active' : '' }}"
                                    aria-hidden="true">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Order items --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Order Items</h2>
                        <span class="text-xs text-gray-400 font-medium">{{ $order->items->count() }}
                            ITEM{{ $order->items->count() > 1 ? 'S' : '' }}</span>
                    </div>
                    <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
                        <table class="w-full min-w-[320px]">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        Service</th>
                                    <th class="pb-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        Qty/Unit</th>
                                    <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        Price</th>
                                    <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($order->items as $item)
                                    @php
                                        $qty = (float) $item->quantity;
                                        $qtyDisplay = $qty == floor($qty) ? (int) $qty : $qty;
                                    @endphp
                                    <tr>
                                        <td class="py-3.5">
                                            <p class="text-sm font-semibold text-gray-900">{{ $item->service->name ?? '-' }}
                                            </p>
                                            @if ($item->service?->category)
                                                <p class="text-xs text-gray-400 mt-0.5">{{ $item->service->category->name }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="py-3.5 text-center text-sm text-gray-600">{{ $qtyDisplay }}
                                            {{ $item->service->unit ?? '' }}</td>
                                        <td class="py-3.5 text-right text-sm text-gray-600">Rp
                                            {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="py-3.5 text-right text-sm font-bold text-gray-900">Rp
                                            {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 pt-4 border-b border-gray-100 space-y-1.5">

                        <div class="flex justify-between text-base font-bold text-gray-900 pt-1 border-t border-gray-100">
                            <span>Total</span>
                            <span class="text-blue-600">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment summary --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900">Pembayaran</h2>
                        @if ($order->payment_status === 'lunas')
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                LUNAS
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-xs font-bold {{ $payColors[$order->payment_status] ?? 'text-gray-700' }} {{ $order->payment_status === 'dp' ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200' }} px-2.5 py-1 rounded-full">
                                {{ $payLabels[$order->payment_status] ?? $order->payment_status }}
                            </span>
                        @endif
                    </div>

                    {{-- Totals --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Total
                                Tagihan</p>
                            <p class="text-base font-bold text-gray-900">Rp
                                {{ number_format($order->total_cost, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-semibold text-green-600 uppercase tracking-widest mb-1">Dibayar
                            </p>
                            <p class="text-base font-bold text-green-700">Rp
                                {{ number_format($order->amount_paid, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-semibold text-blue-600 uppercase tracking-widest mb-1">Kembalian
                            </p>
                            <p class="text-base font-bold text-blue-700">Rp
                                {{ number_format($totalChange, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl p-3 text-center {{ $remaining > 0 ? 'bg-red-50' : 'bg-gray-50' }}">
                            <p
                                class="text-[10px] font-semibold uppercase tracking-widest mb-1 {{ $remaining > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                Sisa</p>
                            <p class="text-base font-bold {{ $remaining > 0 ? 'text-red-700' : 'text-gray-400' }}">Rp
                                {{ number_format($remaining, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($paymentRows->isEmpty())
                        <div class="text-center py-8 border border-dashed border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-400">Belum ada pembayaran.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px]">
                                <thead>
                                    <tr class="border-b border-gray-100">
                                        <th
                                            class="pb-2.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Waktu</th>
                                        <th
                                            class="pb-2.5 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Metode</th>
                                        <th
                                            class="pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Jumlah Bayar</th>
                                        <th
                                            class="pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Kembalian</th>
                                        <th
                                            class="pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Diterapkan</th>
                                        <th
                                            class="pb-2.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Sisa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($paymentRows as $row)
                                        @php
                                            $payment = $row['payment'];
                                            $rowRemaining = max(0, (float) $order->total_cost - $row['running']);
                                            $isLast = $loop->last;
                                        @endphp
                                        <tr>
                                            <td class="py-3">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    {{ $payment->paid_at->format('H:i') }}
                                                    @if ($payment->recordedBy)
                                                        &bull; {{ $payment->recordedBy->username }}
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="py-3">
                                                <span
                                                    class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded inline-flex items-center gap-1">
                                                    {{ $methodLabels[$payment->method] ?? $payment->method }}
                                                    @if ($payment->is_dp)
                                                        <span
                                                            class="text-yellow-700 bg-yellow-100 px-1.5 py-0.5 rounded">DP</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="py-3 text-right text-sm font-bold text-gray-900">Rp
                                                {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            <td class="py-3 text-right text-sm {{ (float) $payment->change_amount > 0 ? 'text-blue-600 font-semibold' : 'text-gray-300' }}">
                                                @if ((float) $payment->change_amount > 0)
                                                    Rp
                                                    {{ number_format($payment->change_amount, 0, ',', '.') }}
                                                @else
                                                    &mdash;
                                                @endif
                                            </td>
                                            <td class="py-3 text-right text-sm text-gray-600">Rp
                                                {{ number_format($row['applied'], 0, ',', '.') }}</td>
                                            <td
                                                class="py-3 text-right text-sm font-semibold {{ $isLast && $rowRemaining > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                                @if ($isLast)
                                                    Rp {{ number_format($rowRemaining, 0, ',', '.') }}
                                                @else
                                                    &mdash;
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-gray-100">
                                        <td colspan="2" class="pt-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            Total
                                        </td>
                                        <td class="pt-3 text-right text-sm font-bold text-gray-900">Rp
                                            {{ number_format($totalTendered, 0, ',', '.') }}</td>
                                        <td
                                            class="pt-3 text-right text-sm font-bold {{ $totalChange > 0 ? 'text-blue-600' : 'text-gray-300' }}">
                                            Rp {{ number_format($totalChange, 0, ',', '.') }}</td>
                                        <td class="pt-3 text-right text-sm font-bold text-green-700">Rp
                                            {{ number_format($order->amount_paid, 0, ',', '.') }}</td>
                                        <td
                                            class="pt-3 text-right text-sm font-bold {{ $remaining > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                            Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if ($totalChange > 0)
                            <p class="text-xs text-blue-600 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mt-3">
                                Total kembalian <strong>Rp
                                    {{ number_format($totalChange, 0, ',', '.') }}</strong> sudah dikembalikan ke
                                pelanggan dan tidak dihitung sebagai pembayaran order.
                            </p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- Customer card --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-11 h-11 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $order->customer->full_name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $order->customer->phone_number ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Total Orders</p>
                            <p class="text-xl font-bold text-gray-900">{{ $totalOrders }}</p>
                        </div>

                    </div>
                </div>

                {{-- Status history --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Status History</h3>
                    <div class="space-y-0">
                        @foreach (array_reverse($statusHistory) as $hist)
                            <div class="flex gap-3 {{ !$loop->last ? 'pb-4' : '' }}">
                                <div class="flex flex-col items-center flex-shrink-0">
                                    <div
                                        class="w-3 h-3 rounded-full mt-0.5
                                    {{ isset($hist['cancelled']) ? 'bg-red-500' : ($hist['active'] ? 'bg-blue-600' : 'bg-gray-200') }}">
                                    </div>
                                    @if (!$loop->last)
                                        <div
                                            class="w-px flex-1 mt-1 min-h-4 {{ $hist['active'] ? 'bg-blue-200' : 'bg-gray-100' }}">
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 pb-1">
                                    <p
                                        class="text-sm font-semibold leading-tight
                                    {{ isset($hist['cancelled']) ? 'text-red-600' : ($hist['active'] ? 'text-gray-900' : 'text-gray-400') }}">
                                        {{ $hist['label'] }}
                                    </p>
                                    @if ($hist['time'])
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $hist['time']->format('d M Y, H:i') }}
                                        </p>
                                    @endif
                                    @if ($hist['by'])
                                        <p class="text-xs font-semibold text-blue-500 mt-0.5 uppercase tracking-wide">
                                            {{ $hist['by'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="space-y-3 print:hidden">
                    @if (auth()->user()->hasTenantPermission('update-order-status'))
                        @if (!$order->is_cancelled && $nextStatus && $nextStatus !== 'selesai')
                            <form method="POST" action="{{ route('orders.status.update', $order) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $nextStatus }}">

                            @if ($nextStatus === 'siap_diambil')
                                <div class="mb-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                                    <label
                                        class="block text-sm font-bold text-indigo-900 mb-2 flex items-center justify-between">
                                        Pilih Lokasi Rak
                                        <span
                                            class="text-[10px] uppercase font-bold tracking-wider text-red-600 bg-red-100 px-2 py-0.5 rounded">*Wajib</span>
                                    </label>
                                    <div class="relative">
                                        <select name="rack_id" required
                                            class="w-full rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm font-medium text-gray-700 bg-white py-2.5 px-3 appearance-none shadow-sm cursor-pointer hover:border-indigo-300 transition-colors">
                                            <option value="" disabled selected>&mdash; Pilih Rak Penyimpanan &mdash;
                                            </option>
                                            @foreach ($racks as $rack)
                                                <option value="{{ $rack->id }}">{{ $rack->name }}
                                                    ({{ $rack->location_description }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-indigo-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-indigo-600 mt-2 font-medium">Rak akan ditampilkan ke struk
                                        pelanggan online.</p>
                                </div>
                            @endif

                                @php $nextLabel = strtoupper(str_replace('_', ' ', $nextStatus)); @endphp
                                <button type="submit" onclick="return confirm('Ubah status ke {{ $nextLabel }}?')"
                                    class="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Update Status
                                </button>
                            </form>
                        @elseif (!$order->is_cancelled && $nextStatus === 'selesai')
                            <p class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl p-3 leading-relaxed">
                                Barang siap diambil pelanggan di
                                @if ($order->rack)
                                    <strong>{{ $order->rack->name }}</strong>
                                @else
                                    <strong>rak penampungan</strong>
                                @endif
                                @if (auth()->user()->role === 'operator')
                                    . Menandai order sebagai <strong>Selesai</strong> dilakukan kasir saat pelanggan
                                    mengambil barang.
                                @else
                                    . Menandai order sebagai <strong>Selesai</strong> dilakukan saat pelanggan mengambil
                                    barang.
                                @endif
                            </p>

                            @if ($racks->isNotEmpty())
                                <form method="POST" action="{{ route('orders.rack.update', $order) }}"
                                    class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 space-y-2">
                                    @csrf @method('PATCH')
                                    <label class="block text-xs font-bold text-indigo-900">
                                        Pindah / Ubah Lokasi Rak Penyimpanan
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <select name="rack_id" required
                                            class="flex-1 rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-xs font-semibold text-gray-700 bg-white py-2 px-2.5 shadow-sm">
                                            @foreach ($racks as $rack)
                                                <option value="{{ $rack->id }}" {{ (int) $order->rack_id === (int) $rack->id ? 'selected' : '' }}>
                                                    {{ $rack->name }}{{ $rack->location_description ? ' (' . $rack->location_description . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm whitespace-nowrap">
                                            Simpan Rak
                                        </button>
                                    </div>
                                </form>
                            @endif
                        @endif
                    @endif

                    @if (auth()->user()->role !== 'operator' && (auth()->user()->hasTenantPermission('update-order-status') || auth()->user()->hasTenantPermission('manage-payments') || auth()->user()->hasTenantPermission('manage-orders')))
                        @if (!$order->is_cancelled && $nextStatus === 'selesai')
                            <form method="POST" action="{{ route('orders.status.update', $order) }}"
                                onsubmit="return confirm('Tandai order ini selesai karena pelanggan sudah mengambil barang?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="selesai">
                                <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Barang Diambil &amp; Selesaikan
                                </button>
                            </form>
                            @if ($order->payment_status !== 'lunas')
                                <p class="text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                                    Pembayaran belum lunas. Order hanya bisa diselesaikan setelah pelanggan membayar
                                    penuh.
                                </p>
                            @endif
                        @endif

                        @if (auth()->user()->hasTenantPermission('manage-payments'))
                            <a href="{{ route('payments.index', $order) }}"
                            class="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $paymentRows->isEmpty() ? 'Catat Pembayaran' : 'Kelola Pembayaran' }}
                            </a>
                        @endif

                        <div class="grid grid-cols-1 {{ $waUrl ? 'sm:grid-cols-2' : '' }} gap-2">
                            <button type="button" onclick="window.print()"
                                class="w-full flex items-center justify-center gap-2 py-3 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak Nota
                            </button>

                            @if ($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824z"/>
                                    </svg>
                                    Kirim WA
                                </a>
                            @endif
                        </div>

                        @if (auth()->user()->hasTenantPermission('manage-orders') && !$order->is_cancelled && $order->status === 'diterima')
                            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                onsubmit="return confirm('Yakin ingin membatalkan order ini?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="w-full py-2.5 text-red-500 hover:text-red-700 font-semibold text-sm transition">
                                    Cancel Order
                                </button>
                            </form>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Print receipt (Optimized for 58mm & 80mm Thermal POS Printers) --}}
    <div id="thermal-receipt" class="hidden print:block">
        <div class="thermal-header">
            <h1 class="thermal-store-name">{{ strtoupper($appStoreName ?? 'LAUNDIQO') }}</h1>
            @if(!empty($appStoreAddress) || !empty($appStoreCity))
                <p class="thermal-sub">{{ $appStoreAddress ?? '' }}{{ !empty($appStoreAddress) && !empty($appStoreCity) ? ', ' : '' }}{{ $appStoreCity ?? '' }}</p>
            @endif
            @if(!empty($appStorePhone))
                <p class="thermal-sub">Telp/WA: {{ $appStorePhone }}</p>
            @endif
            <div class="thermal-divider-double"></div>
            <p class="thermal-title">
                @if ($order->status === 'selesai' && $order->payment_status === 'lunas')
                    STRUK PENGAMBILAN &amp; LUNAS
                @elseif ($paymentRows->isNotEmpty())
                    NOTA &amp; BUKTI BAYAR ({{ strtoupper($payLabels[$order->payment_status] ?? 'DP') }})
                @else
                    NOTA TANDA TERIMA CUCIAN
                @endif
            </p>
            <p class="thermal-sub">{{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="thermal-divider"></div>

        {{-- Metadata Order --}}
        <div class="thermal-meta">
            <div class="thermal-row">
                <span>No. Order</span>
                <span class="thermal-bold">{{ $order->order_number }}</span>
            </div>
            <div class="thermal-row">
                <span>Pelanggan</span>
                <span class="thermal-bold">{{ $order->customer->full_name ?? '-' }}</span>
            </div>
            <div class="thermal-row">
                <span>No. HP</span>
                <span>{{ $order->customer->phone_number ?? '-' }}</span>
            </div>
            <div class="thermal-row">
                <span>Tgl Masuk</span>
                <span>{{ $order->received_date->format('d/m/Y') }}</span>
            </div>
            <div class="thermal-row">
                <span>Est. Selesai</span>
                <span>{{ $order->estimated_done_date->format('d/m/Y') }}</span>
            </div>
            <div class="thermal-row">
                <span>Status Cucian</span>
                <span class="thermal-bold">{{ strtoupper($statusLabels[$order->status] ?? $order->status) }}</span>
            </div>
            @if ($order->rack)
                <div class="thermal-row">
                    <span>Lokasi Rak</span>
                    <span class="thermal-bold">{{ $order->rack->name }}</span>
                </div>
            @endif
            @if ($order->status === 'selesai')
                <div class="thermal-row">
                    <span>Tgl Diambil</span>
                    <span>{{ ($order->completed_at ?? now())->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="thermal-divider"></div>

        {{-- Daftar Item (Format 2 Baris Standar Thermal 58mm/80mm agar tidak terpotong) --}}
        <div class="thermal-items">
            @foreach ($order->items as $item)
                <div class="thermal-item">
                    <div class="thermal-item-name">{{ $item->service->name ?? '-' }}</div>
                    <div class="thermal-row">
                        <span>{{ $item->quantity }} {{ $item->service->unit ?? '' }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span class="thermal-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="thermal-divider"></div>

        {{-- Ringkasan Tagihan --}}
        <div class="thermal-totals">
            <div class="thermal-row thermal-total-main">
                <span>TOTAL</span>
                <span>Rp {{ number_format($order->total_cost, 0, ',', '.') }}</span>
            </div>
            @if ($totalTendered > 0)
                <div class="thermal-row">
                    <span>Tunai/Masuk</span>
                    <span>Rp {{ number_format($totalTendered, 0, ',', '.') }}</span>
                </div>
                <div class="thermal-row">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($totalChange, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="thermal-row">
                <span>Total Dibayar</span>
                <span>Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
            </div>
            @if ($remaining > 0)
                <div class="thermal-row thermal-bold">
                    <span>SISA TAGIHAN</span>
                    <span>Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                </div>
            @else
                <div class="thermal-row thermal-bold">
                    <span>STATUS BAYAR</span>
                    <span>LUNAS</span>
                </div>
            @endif
        </div>

        {{-- Riwayat Pembayaran --}}
        @if ($paymentRows->isNotEmpty())
            <div class="thermal-divider"></div>
            <div class="thermal-payments">
                <p class="thermal-bold" style="margin-bottom: 2px;">Riwayat Pembayaran:</p>
                @foreach ($paymentRows as $row)
                    <div class="thermal-row">
                        <span>{{ $row['payment']->paid_at?->format('d/m H:i') }} ({{ strtoupper($row['payment']->method) }})</span>
                        <span>{{ number_format($row['applied'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="thermal-divider"></div>

        {{-- Footer Thermal --}}
        <div class="thermal-footer">
            @if ($order->status === 'selesai' && $order->payment_status === 'lunas')
                <p class="thermal-bold">LUNAS - BARANG TELAH DIAMBIL</p>
            @elseif ($remaining > 0 && !empty($appBankInfo))
                <p class="thermal-bold">Transfer: {{ $appBankInfo }}</p>
            @endif
            <p style="margin-top: 4px;">Lacak status cucian online:</p>
            <p class="thermal-bold" style="word-break: break-all;">{{ route('tracking.show', $order->order_number) }}</p>
            <p style="margin-top: 6px;">{{ $appReceiptFooter ?? 'Terima kasih atas kepercayaan Anda!' }}</p>
        </div>
    </div>
    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 0mm;
            }

            html, body {
                width: 58mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
            }

            body * {
                visibility: hidden;
            }

            #thermal-receipt,
            #thermal-receipt * {
                visibility: visible;
                color: #000 !important;
                font-family: 'Courier New', Courier, monospace !important;
            }

            #thermal-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 54mm;
                max-width: 100%;
                margin: 0 auto;
                padding: 2mm 1.5mm 6mm 1.5mm;
                font-size: 10pt;
                line-height: 1.3;
                background: #fff;
            }

            .thermal-header,
            .thermal-footer {
                text-align: center;
            }

            .thermal-store-name {
                font-size: 12pt;
                font-weight: 700;
                margin: 0 0 2px 0;
                line-height: 1.2;
            }

            .thermal-title {
                font-size: 10pt;
                font-weight: 700;
                margin: 2px 0;
            }

            .thermal-sub {
                font-size: 8.5pt;
                margin: 1px 0;
            }

            .thermal-divider {
                border-top: 1px dashed #000;
                margin: 5px 0;
            }

            .thermal-divider-double {
                border-top: 2px solid #000;
                margin: 4px 0;
            }

            .thermal-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 4px;
                font-size: 9pt;
                margin: 1.5px 0;
            }

            .thermal-item {
                margin: 3px 0;
            }

            .thermal-item-name {
                font-size: 9.5pt;
                font-weight: 700;
            }

            .thermal-bold {
                font-weight: 700;
            }

            .thermal-total-main {
                font-size: 10.5pt;
                font-weight: 700;
                margin: 3px 0;
            }
        }
    </style>
@endsection
