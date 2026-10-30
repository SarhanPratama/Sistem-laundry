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
                                    class="mt-2 text-sm font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded inline-flex items-center gap-1">
                                    Lokasi: {{ $order->rack->name }}
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
                            @endphp
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center border-2
                                {{ $isCurrent ? 'bg-blue-600 border-blue-600 text-white shadow-md' : ($reached ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-gray-200 text-gray-300') }}">
                                    @if ($reached && !$isCurrent)
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
                                {{ $isCurrent ? 'text-blue-600' : ($reached ? 'text-green-600' : 'text-gray-400') }}">
                                    {{ strtoupper(str_replace('_', ' ', $s)) }}
                                </p>
                            </div>
                            @if (!$loop->last)
                                <div
                                    class="flex-1 h-0.5 mx-1 mb-5 {{ !$order->is_cancelled && $currentIndex !== false && $currentIndex > $i ? 'bg-green-400' : 'bg-gray-200' }}">
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
                    @role('operator')
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
                                . Menandai order sebagai <strong>Selesai</strong> dilakukan kasir saat pelanggan
                                mengambil barang.
                            </p>
                        @endif
                    @endrole

                    @role('kasir')
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

                        <a href="{{ route('payments.index', $order) }}"
                            class="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $paymentRows->isEmpty() ? 'Catat Pembayaran' : 'Kelola Pembayaran' }}
                        </a>

                        <button onclick="window.print()"
                            class="w-full flex items-center justify-center gap-2 py-3 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Receipt
                        </button>

                        @if (!$order->is_cancelled && $order->status === 'diterima')
                            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                onsubmit="return confirm('Yakin ingin membatalkan order ini?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="w-full py-2.5 text-red-500 hover:text-red-700 font-semibold text-sm transition">
                                    Cancel Order
                                </button>
                            </form>
                        @endif
                    @endrole
                </div>

            </div>
        </div>
    </div>

    {{-- Print receipt --}}
    <div class="hidden print:block p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">{{ $appStoreName ?? 'Laundry Management System' }}</h1>
            @if(!empty($appStoreAddress) || !empty($appStoreCity))
                <p class="text-xs text-gray-500">{{ $appStoreAddress ?? '' }} {{ $appStoreCity ? '('.$appStoreCity.')' : '' }}</p>
            @endif
            <p class="text-sm text-gray-600">Struk Tanda Terima &bull; {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        {{-- Order Number / Code --}}
        <div class="text-center mb-6">
            <p class="text-center text-lg font-mono font-bold">{{ $order->order_number }}</p>
            <p class="text-center text-sm text-gray-600 mt-1">Cek status online di website kami</p>
        </div>

        <div class="border-t border-b border-dashed border-gray-400 py-3 mb-4 text-sm space-y-1">
            <div class="flex justify-between"><span>No. Order</span><span
                    class="font-mono font-bold">{{ $order->order_number }}</span></div>
            <div class="flex justify-between"><span>Pelanggan</span><span>{{ $order->customer->full_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between"><span>Telepon</span><span>{{ $order->customer->phone_number ?? '-' }}</span>
            </div>
            <div class="flex justify-between"><span>Tgl
                    Terima</span><span>{{ $order->received_date->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span>Est.
                    Selesai</span><span>{{ $order->estimated_done_date->format('d/m/Y') }}</span></div>
        </div>
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="text-left py-1">Layanan</th>
                    <th class="text-right py-1">Jml</th>
                    <th class="text-right py-1">Harga</th>
                    <th class="text-right py-1">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="py-1">{{ $item->service->name ?? '-' }}</td>
                        <td class="text-right py-1">{{ $item->quantity }} {{ $item->service->unit ?? '' }}</td>
                        <td class="text-right py-1">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right py-1">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-dashed border-gray-400 pt-3 text-sm space-y-1">
            <div class="flex justify-between font-bold text-base"><span>TOTAL</span><span>Rp
                    {{ number_format($order->total_cost, 0, ',', '.') }}</span></div>
            @if ($totalTendered > 0)
                <div class="flex justify-between"><span>Uang Diterima</span><span>Rp
                        {{ number_format($totalTendered, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Kembalian</span><span>Rp
                        {{ number_format($totalChange, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between"><span>Dibayar</span><span>Rp
                    {{ number_format($order->amount_paid, 0, ',', '.') }}</span></div>
            @if ($remaining > 0)
                <div class="flex justify-between text-red-600"><span>Sisa</span><span>Rp
                        {{ number_format($remaining, 0, ',', '.') }}</span></div>
            @endif
        </div>
        <p class="text-center mt-6 text-xs text-gray-500">{{ $appReceiptFooter ?? 'Terima kasih atas kepercayaan Anda!' }}</p>
    </div>
    <style>
        @media print {
            body * {
                visibility: hidden
            }

            .print\:block,
            .print\:block * {
                visibility: visible
            }

            .print\:block {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%
            }
        }
    </style>
@endsection
