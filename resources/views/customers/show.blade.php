@extends('layouts.app')

@section('title', 'Detail Pelanggan')
@section('page-title', 'Detail Pelanggan')

@section('content')
@php
    // Avatar initials & color
    $words    = explode(' ', trim($customer->full_name));
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

    $colors = [
        'A'=>'bg-purple-100 text-purple-600','B'=>'bg-blue-100 text-blue-600',
        'C'=>'bg-cyan-100 text-cyan-600','D'=>'bg-indigo-100 text-indigo-600',
        'E'=>'bg-emerald-100 text-emerald-600','F'=>'bg-fuchsia-100 text-fuchsia-600',
        'G'=>'bg-green-100 text-green-600','H'=>'bg-orange-100 text-orange-600',
        'I'=>'bg-sky-100 text-sky-600','J'=>'bg-violet-100 text-violet-600',
        'K'=>'bg-teal-100 text-teal-600','L'=>'bg-lime-100 text-lime-600',
        'M'=>'bg-rose-100 text-rose-600','N'=>'bg-amber-100 text-amber-600',
        'O'=>'bg-pink-100 text-pink-600','P'=>'bg-blue-100 text-blue-600',
        'R'=>'bg-red-100 text-red-600','S'=>'bg-green-100 text-green-600',
        'T'=>'bg-teal-100 text-teal-600','U'=>'bg-indigo-100 text-indigo-600',
        'V'=>'bg-violet-100 text-violet-600','W'=>'bg-cyan-100 text-cyan-600',
        'Y'=>'bg-yellow-100 text-yellow-600','Z'=>'bg-emerald-100 text-emerald-600',
    ];
    $avatarColor = $colors[strtoupper(substr($customer->full_name, 0, 1))] ?? 'bg-gray-100 text-gray-600';

    // Format total spent
    if ($totalSpent >= 1_000_000) {
        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000_000, 1, '.', '') . 'M';
    } elseif ($totalSpent >= 1_000) {
        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000, 0, '.', '') . 'K';
    } else {
        $spentFormatted = 'Rp ' . number_format($totalSpent, 0, ',', '.');
    }

    // Status badge map
    $statusColors = [
        'diterima'     => 'bg-blue-100 text-blue-700',
        'dicuci'       => 'bg-yellow-100 text-yellow-700',
        'dikeringkan'  => 'bg-orange-100 text-orange-700',
        'disetrika'    => 'bg-purple-100 text-purple-700',
        'siap_diambil' => 'bg-green-100 text-green-700',
        'selesai'      => 'bg-gray-100 text-gray-600',
    ];
    $statusLabels = [
        'diterima'     => 'Diterima',
        'dicuci'       => 'Dicuci',
        'dikeringkan'  => 'Dikeringkan',
        'disetrika'    => 'Disetrika',
        'siap_diambil' => 'Siap Diambil',
        'selesai'      => 'Selesai',
    ];
@endphp

<div class="mx-auto">

    {{-- Back + Edit header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pelanggan</h1>
        </div>
        <a href="{{ route('customers.edit', $customer) }}"
           class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN: Profile + Stats --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-full {{ $avatarColor }} flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    {{ $initials }}
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $customer->full_name }}</h2>

                {{-- Phone --}}
                <p class="flex items-center justify-center gap-1.5 text-sm text-gray-500 mt-2">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ $customer->phone_number }}
                </p>

                {{-- Address --}}
                @if($customer->address)
                    <p class="flex items-center justify-center gap-1.5 text-sm text-gray-500 mt-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $customer->address }}
                    </p>
                @endif

                {{-- Status badge --}}
                <div class="mt-4">
                    @if ($customer->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            Nonaktif
                        </span>
                    @endif
                </div>

                {{-- Admin actions --}}
                @role('admin')
                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-center gap-3">
                    @if ($customer->is_active)
                        <form method="POST" action="{{ route('customers.deactivate', $customer) }}"
                              onsubmit="return confirm('Yakin ingin menonaktifkan pelanggan ini?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-orange-500 hover:text-orange-700 font-medium transition">
                                Nonaktifkan
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                          onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-medium transition">
                            Hapus
                        </button>
                    </form>
                </div>
                @endrole
            </div>

            {{-- Total Orders stat --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Total Order</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalOrders }}</p>
            </div>

            {{-- Total Spent stat --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Total Pengeluaran</p>
                <p class="text-3xl font-bold text-gray-900 font-mono">{{ $spentFormatted }}</p>
            </div>

            {{-- Member Since stat --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Member Sejak</p>
                <p class="text-3xl font-bold text-gray-900">{{ $customer->created_at->format('M Y') }}</p>
            </div>

            {{-- New Order CTA --}}
            <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}"
               class="flex items-center justify-center gap-2 w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-2xl transition duration-150 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Order Baru
            </a>

        </div>

        {{-- RIGHT COLUMN: Order History --}}
        <div class="lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Riwayat Order</h3>

            @if ($customer->orders->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm text-center py-16 text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Belum ada riwayat order.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-100">
                    @foreach ($customer->orders as $order)
                        @php
                            $statusColor = $order->is_cancelled
                                ? 'bg-red-100 text-red-600'
                                : ($statusColors[$order->status] ?? 'bg-gray-100 text-gray-600');
                            $statusLabel = $order->is_cancelled
                                ? 'Dibatalkan'
                                : ($statusLabels[$order->status] ?? ucfirst($order->status));
                        @endphp
                        <a href="{{ route('orders.show', $order) }}"
                           class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition group">
                            <div class="min-w-0">
                                {{-- Order number + status badge --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-mono text-gray-400">{{ $order->order_number }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                {{-- Date --}}
                                <p class="text-sm text-gray-600">{{ $order->received_date->format('d M Y') }}</p>
                                {{-- Total --}}
                                <p class="text-base font-bold text-gray-900 mt-0.5">
                                    Rp {{ number_format($order->total_cost, 0, ',', '.') }}
                                </p>
                            </div>
                            {{-- Chevron --}}
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0 ml-4 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
