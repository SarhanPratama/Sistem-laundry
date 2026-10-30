@extends('layouts.app')
@section('title', 'Order Management')
@section('page-title', 'Order')

@section('content')
    @php
        $statusColors = [
            'diterima' => 'bg-blue-100 text-blue-700',
            'dicuci' => 'bg-yellow-100 text-yellow-700',
            'dikeringkan' => 'bg-orange-100 text-orange-700',
            'disetrika' => 'bg-purple-100 text-purple-700',
            'siap_diambil' => 'bg-green-100 text-green-700',
            'selesai' => 'bg-gray-100 text-gray-600',
        ];
        $statusLabels = [
            'diterima' => 'Diterima',
            'dicuci' => 'Dicuci',
            'dikeringkan' => 'Dikeringkan',
            'disetrika' => 'Disetrika',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai',
        ];
        $tabLabels = [
            'diterima' => 'DITERIMA',
            'dicuci' => 'DICUCI',
            'dikeringkan' => 'DIKERINGKAN',
            'disetrika' => 'DISETRIKA',
            'siap_diambil' => 'SIAP DIAMBIL',
            'selesai' => 'SELESAI',
        ];
    @endphp

    <div class="mx-auto" x-data="{ search: '' }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kelola dan lacak semua pesanan laundry.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <form method="GET" action="{{ route('orders.index') }}" class="relative w-full sm:w-auto" x-on:submit.prevent>
                    @if ($activeStatus)
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Cari order atau pelanggan..."
                        class="pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64"
                        x-model.debounce="search" />
                </form>
                @role('kasir')
                <a href="{{ route('orders.create') }}"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm w-full sm:w-auto">
                    New Order
                </a>
                @endrole
            </div>
        </div>

        {{-- Status tabs --}}
        <div class="flex items-center gap-1 mb-6 overflow-x-auto pb-1">
            <a href="{{ route('orders.index', array_filter(['search' => $search ?? ''])) }}"
                class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition
                  {{ !$activeStatus ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                ALL ORDERS
                <span
                    class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ !$activeStatus ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $statusCounts->sum() }}
                </span>
            </a>
            @foreach ($statuses as $s)
                @if (($statusCounts[$s] ?? 0) > 0 || $activeStatus === $s)
                    <a href="{{ route('orders.index', array_filter(['status' => $s, 'search' => $search ?? ''])) }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition
                          {{ $activeStatus === $s ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        {{ $tabLabels[$s] }}
                        @if ($statusCounts[$s] ?? 0)
                            <span
                                class="text-xs font-bold px-1.5 py-0.5 rounded-full
                            {{ $activeStatus === $s ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $statusCounts[$s] }}
                            </span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Cards grid --}}
        @if ($orders->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm text-center py-20 text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-sm font-medium text-gray-500">Tidak ada order ditemukan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($orders as $order)
                    @php
                        $isOverdue =
                            !$order->is_cancelled &&
                            !in_array($order->status, ['selesai']) &&
                            $order->estimated_done_date->isPast();
                        $overdueDays = $isOverdue ? ceil(now()->diffInDays($order->estimated_done_date, false)) : 0;
                        $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600';
                        $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);

                        // Service summary
                        $itemCount = $order->items->count();
                        $firstItem = $order->items->first();
                        $serviceSummary = $firstItem
                            ? ($firstItem->service->name ?? '-') .
                                ' (' .
                                ((float) $firstItem->quantity == floor((float) $firstItem->quantity)
                                    ? (int) $firstItem->quantity
                                    : $firstItem->quantity) .
                                ' ' .
                                ($firstItem->service->unit ?? '') .
                                ')'
                            : '-';
                        if ($itemCount > 1) {
                            $serviceSummary .= ' +' . ($itemCount - 1) . ' lainnya';
                        }
                    @endphp

                    <a href="{{ route('orders.show', $order) }}"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 p-5 flex flex-col group"
                        x-show="search === '' || $el.dataset.orderNumber.toLowerCase().includes(search.toLowerCase()) || $el.dataset.customerName.toLowerCase().includes(search.toLowerCase())"
                        data-order-number="{{ $order->order_number }}"
                        data-customer-name="{{ $order->customer->full_name ?? '' }}">

                        {{-- Card header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="min-w-0">
                                <p class="text-xs font-mono text-gray-400 mb-0.5">{{ $order->order_number }}</p>
                                <p class="text-base font-bold text-gray-900 truncate group-hover:text-blue-600 transition">
                                    {{ $order->customer->full_name ?? '-' }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0 ml-2 {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        {{-- Service info --}}
                        <div class="space-y-1.5 mb-4 flex-1">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span class="truncate">{{ $serviceSummary }}</span>
                            </div>

                            @if ($order->status === 'selesai' && $order->completed_at)
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Selesai {{ $order->completed_at->format('d M, H:i') }}</span>
                                </div>
                            @else
                                <div
                                    class="flex items-center gap-2 text-sm {{ $isOverdue ? 'text-red-500' : 'text-gray-500' }}">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @if ($isOverdue)
                                        <span class="font-medium">Overdue ({{ $overdueDays }} hari)</span>
                                    @else
                                        <span>Due: {{ $order->estimated_done_date->format('d M, H:i') }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Progress bar for in-progress orders --}}
                            @if (in_array($order->status, ['dicuci', 'dikeringkan', 'disetrika']))
                                @php
                                    $progressMap = ['dicuci' => 33, 'dikeringkan' => 66, 'disetrika' => 85];
                                    $progress = $progressMap[$order->status] ?? 0;
                                @endphp
                                <div class="mt-2">
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full transition-all"
                                            style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Total Cost</span>
                            <span class="text-base font-bold text-gray-900">
                                Rp {{ number_format($order->total_cost, 0, ',', '.') }}
                            </span>
                        </div>

                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($orders->hasPages())
                <div
                    class="mt-8 bg-white border border-gray-200 rounded-xl shadow-sm px-6 py-4 flex items-center justify-center sm:justify-between flex-wrap gap-4">
                    {{ $orders->links('pagination::tailwind') }}
                </div>
            @endif
        @endif

    </div>
@endsection
