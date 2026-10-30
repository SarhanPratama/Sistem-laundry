@extends('layouts.app')

@section('title', 'Pelacakan Status Order')
@section('page-title', 'Pelacakan Status')

@section('content')
    <div class="mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight mb-1">Order Tracking</h1>
                <p class="text-gray-500 text-sm">Manage and monitor live laundry operations.</p>
            </div>
            @role('kasir')
                <a href="{{ route('orders.create') }}"
                    class="inline-flex items-center justify-center gap-2 bg-[#0B57D0] hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition duration-200 shadow-sm w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Order
                </a>
            @endrole
        </div>

        {{-- Banner read-only untuk admin --}}
        @if ($isReadOnly ?? false)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <p class="font-semibold">Mode pemantauan</p>
                    <p class="mt-0.5">
                        Sebagai admin Anda dapat melihat histori dan progres status laundry, namun perubahan
                        status tetap dilakukan operator untuk tahap produksi dan kasir saat pelanggan mengambil barang.
                        Gunakan filter <span class="font-medium">Status &rarr; Selesai</span> untuk melihat order yang sudah
                        diambil.
                    </p>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-8">
            <form method="GET" action="{{ route('orders.status') }}" class="flex flex-col lg:flex-row items-end gap-5">

                <div class="w-full lg:w-1/4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <div class="relative">
                        <select name="status"
                            class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:flex-1 min-w-0">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                    <div class="flex flex-col sm:flex-row items-center gap-2">
                        <div class="relative w-full min-w-0">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full px-2.5 sm:px-3 py-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <span class="text-gray-400 text-xs sm:text-sm font-medium flex-shrink-0">to</span>
                        <div class="relative w-full min-w-0">
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full px-2.5 sm:px-3 py-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Name</label>
                    <input type="text" name="customer_name" value="{{ request('customer_name') }}"
                        placeholder="Search customer..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="w-full lg:w-auto flex-shrink-0">
                    <button type="submit"
                        class="w-full lg:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 border-2 border-[#0B57D0] text-[#0B57D0] hover:bg-blue-50 font-semibold text-sm rounded-xl transition-colors bg-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Apply Filters
                    </button>
                </div>

            </form>
        </div>

        {{-- Orders Table Card --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-8">
            @if ($orders->isEmpty())
                <div class="text-center py-16 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-base font-medium text-gray-600">No orders found.</p>
                    <p class="text-sm mt-1">Try adjusting your filters or create a new order.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white">
                                    Order ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white">
                                    Customer</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white">
                                    Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white">
                                    Date Received</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white">
                                    Est. Finish</th>
                                <th
                                    class="px-6 py-4 text-xs font-bold text-gray-800 uppercase tracking-widest bg-white text-center">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @php
                                $colors = [
                                    'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                    'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                                    'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
                                    'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                                    'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                                    'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
                                ];

                                $statusConfig = [
                                    'diterima' => [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-600',
                                        'label' => 'Diterima',
                                    ],
                                    'dicuci' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Dicuci'],
                                    'dikeringkan' => [
                                        'bg' => 'bg-yellow-100',
                                        'text' => 'text-yellow-600',
                                        'label' => 'Dikeringkan',
                                    ],
                                    'disetrika' => [
                                        'bg' => 'bg-purple-100',
                                        'text' => 'text-purple-600',
                                        'label' => 'Disetrika',
                                    ],
                                    'siap_diambil' => [
                                        'bg' => 'bg-orange-100',
                                        'text' => 'text-orange-600',
                                        'label' => 'Siap',
                                    ],
                                    'selesai' => [
                                        'bg' => 'bg-green-100',
                                        'text' => 'text-green-600',
                                        'label' => 'Selesai',
                                    ],
                                ];

                                $statusSequence = [
                                    'diterima',
                                    'dicuci',
                                    'dikeringkan',
                                    'disetrika',
                                    'siap_diambil',
                                    'selesai',
                                ];

                                // Produksi hanya oleh operator, pengambilan barang (selesai) hanya oleh kasir
                                $isOperator = auth()->user()->hasRole('operator');
                                $isKasir = auth()->user()->hasRole('kasir');
                            @endphp

                            @foreach ($orders as $index => $order)
                                @php
                                    $status = $statusConfig[$order->status] ?? [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-600',
                                        'label' => ucfirst($order->status),
                                    ];

                                    // Avatar initials
                                    $words = explode(' ', trim($order->customer->full_name ?? 'U'));
                                    $initials = strtoupper(
                                        substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''),
                                    );

                                    // Cycle through colors for avatar
                                    $colorKeys = array_keys($colors);
                                    $avatarColor = $colors[$colorKeys[$index % count($colorKeys)]];

                                    $currentIndex = array_search($order->status, $statusSequence);
                                    $nextStatus =
                                        $currentIndex !== false && isset($statusSequence[$currentIndex + 1])
                                            ? $statusSequence[$currentIndex + 1]
                                            : null;

                                    // Produksi hanya oleh operator, pengambilan barang (selesai) hanya oleh kasir
                                    $isPickup = $nextStatus === 'selesai';
                                    $canAdvance = $nextStatus !== null && ($isPickup ? $isKasir : $isOperator);
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition duration-150 group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}"
                                            class="text-sm font-semibold text-[#0B57D0] hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold {{ $avatarColor['bg'] }} {{ $avatarColor['text'] }}">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ $order->customer->full_name ?? 'Unknown Customer' }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $order->customer->phone_number ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $status['bg'] }} {{ $status['text'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm text-gray-700">{{ $order->received_date->format('M d, h:i A') }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($order->estimated_done_date)
                                            @if ($order->status === 'selesai')
                                                <p class="text-sm text-gray-500">Completed</p>
                                            @else
                                                <p
                                                    class="text-sm {{ $order->estimated_done_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                                    {{ $order->estimated_done_date->isToday() ? 'Today, ' . $order->estimated_done_date->format('h:i A') : $order->estimated_done_date->format('M d, h:i A') }}
                                                </p>
                                            @endif
                                        @else
                                            <p class="text-sm text-gray-400">-</p>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('orders.show', $order) }}" title="View Details"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </a>
                                            @if ($canAdvance)
                                                @if ($nextStatus === 'siap_diambil')
                                                    {{-- Pilih rak hanya bisa dari halaman detail order --}}
                                                    <a href="{{ route('orders.show', $order) }}"
                                                        title="Pilih Lokasi Rak di detail order"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded bg-[#0B57D0] hover:bg-blue-800 text-white transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('orders.status.update', $order) }}"
                                                        onsubmit="return confirm('Ubah status order {{ $order->order_number }} dari &quot;{{ $status['label'] }}&quot; menjadi &quot;{{ ucfirst(str_replace('_', ' ', $nextStatus)) }}&quot;?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status"
                                                            value="{{ $nextStatus }}">
                                                        <button type="submit"
                                                            title="Next Status: {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded {{ $isPickup ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#0B57D0] hover:bg-blue-800' }} text-white transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M9 5l7 7-7 7"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <div class="w-8 h-8"></div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages() || $orders->total() > 0)
                    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-white">
                        <div class="text-sm text-gray-500">
                            Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of
                            {{ $orders->total() }} orders
                        </div>
                        <div>
                            {{ $orders->links('pagination::tailwind') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
