@extends('layouts.app')

@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('content')
<div class="mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pelanggan</h1>
        </div>
        <a href="{{ route('customers.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition duration-150 shadow-sm w-full sm:w-auto">
            Tambah Pelanggan
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('customers.index') }}" class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </div>
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Cari berdasarkan nama atau nomor telepon..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                x-data
                x-ref="searchInput"
            >
            @if($search)
                <a href="{{ route('customers.index') }}"
                   class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
        </div>
    </form>

    @if ($customers->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <svg class="mx-auto h-14 w-14 mb-4 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-base font-medium text-gray-500">
                {{ $search ? 'Tidak ada pelanggan yang cocok.' : 'Belum ada pelanggan.' }}
            </p>
            @if(!$search)
                <p class="text-sm mt-1">Klik "Tambah Pelanggan" untuk memulai.</p>
            @endif
        </div>
    @else

        {{-- Customer Card Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($customers as $customer)
                @php
                    // Generate initials from name
                    $words    = explode(' ', trim($customer->full_name));
                    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                    // Avatar color based on first letter
                    $colors = [
                        'A' => 'bg-purple-100 text-purple-600',
                        'B' => 'bg-blue-100 text-blue-600',
                        'C' => 'bg-cyan-100 text-cyan-600',
                        'D' => 'bg-indigo-100 text-indigo-600',
                        'E' => 'bg-emerald-100 text-emerald-600',
                        'F' => 'bg-fuchsia-100 text-fuchsia-600',
                        'G' => 'bg-green-100 text-green-600',
                        'H' => 'bg-orange-100 text-orange-600',
                        'I' => 'bg-sky-100 text-sky-600',
                        'J' => 'bg-violet-100 text-violet-600',
                        'K' => 'bg-teal-100 text-teal-600',
                        'L' => 'bg-lime-100 text-lime-600',
                        'M' => 'bg-rose-100 text-rose-600',
                        'N' => 'bg-amber-100 text-amber-600',
                        'O' => 'bg-pink-100 text-pink-600',
                        'P' => 'bg-blue-100 text-blue-600',
                        'Q' => 'bg-purple-100 text-purple-600',
                        'R' => 'bg-red-100 text-red-600',
                        'S' => 'bg-green-100 text-green-600',
                        'T' => 'bg-teal-100 text-teal-600',
                        'U' => 'bg-indigo-100 text-indigo-600',
                        'V' => 'bg-violet-100 text-violet-600',
                        'W' => 'bg-cyan-100 text-cyan-600',
                        'X' => 'bg-orange-100 text-orange-600',
                        'Y' => 'bg-yellow-100 text-yellow-600',
                        'Z' => 'bg-emerald-100 text-emerald-600',
                    ];
                    $firstLetter = strtoupper(substr($customer->full_name, 0, 1));
                    $avatarColor = $colors[$firstLetter] ?? 'bg-gray-100 text-gray-600';

                    // Format total spent
                    $totalSpent = $customer->orders_sum_total_cost ?? 0;
                    if ($totalSpent >= 1_000_000) {
                        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000_000, 1, '.', '') . 'M';
                    } elseif ($totalSpent >= 1_000) {
                        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000, 0, '.', '') . 'K';
                    } else {
                        $spentFormatted = 'Rp ' . number_format($totalSpent, 0, ',', '.');
                    }
                @endphp

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">

                    {{-- Card Header --}}
                    <div class="p-5 pb-4">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-full {{ $avatarColor }} flex items-center justify-center flex-shrink-0 text-base font-bold">
                                {{ $initials }}
                            </div>
                            {{-- Name & Phone --}}
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $customer->full_name }}</h3>
                                    @if (!$customer->is_active)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 flex-shrink-0">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $customer->phone_number }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Row --}}
                    <div class="mx-5 mb-4 grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-gray-500 font-medium mb-1">Total Order</p>
                            <p class="text-xl font-bold text-gray-900">{{ $customer->orders_count }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-gray-500 font-medium mb-1">Total Pengeluaran</p>
                            <p class="text-xl font-bold text-gray-900 font-mono">{{ $spentFormatted }}</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="px-5 pb-5 grid grid-cols-2 gap-3">
                        <a href="{{ route('customers.show', $customer) }}"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Lihat
                        </a>
                        <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-medium text-white transition duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Order
                        </a>
                    </div>

                    {{-- Admin actions (edit / deactivate) --}}
                    @role('admin')
                    <div class="border-t border-gray-100 px-5 py-3 flex items-center gap-3 bg-gray-50/50">
                        <a href="{{ route('customers.edit', $customer) }}"
                           class="text-xs text-gray-500 hover:text-blue-600 font-medium transition">
                            Edit
                        </a>
                        @if ($customer->is_active)
                            <form method="POST" action="{{ route('customers.deactivate', $customer) }}"
                                  class="inline"
                                  onsubmit="return confirm('Yakin ingin menonaktifkan pelanggan ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-gray-500 hover:text-orange-600 font-medium transition">
                                    Nonaktifkan
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">Sudah nonaktif</span>
                        @endif
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                              class="inline ml-auto"
                              onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-600 font-medium transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                    @endrole

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($customers->hasPages())
            <div>
                {{ $customers->links('pagination::tailwind') }}
            </div>
        @endif

    @endif

</div>
@endsection
