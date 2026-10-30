@extends('layouts.app')
@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran')

@section('content')
<div class="mx-auto">

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pembayaran</h1>
        <p class="text-sm text-gray-500 mt-1">Order yang menunggu pembayaran atau belum lunas.</p>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('payments.list') }}" class="mb-8">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Cari nomor order atau nama pelanggan..."
                   class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-500/20 transition-all">
            @if($search)
                <a href="{{ route('payments.list') }}" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
        </div>
    </form>

    @if($orders->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm text-center py-16 text-gray-400">
            <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500">
                {{ $search ? 'Tidak ada order yang cocok.' : 'Semua order sudah lunas.' }}
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($orders as $order)
                @php
                    $remaining = max(0, (float)$order->total_cost - (float)$order->amount_paid);
                    $isDP = $order->payment_status === 'dp';
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col hover:shadow-md transition-shadow duration-200">

                    {{-- Card Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex bg-[#1A73E8] text-white text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded tracking-wide">
                            {{ $order->order_number }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $isDP ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600 px-2' }}">
                            {{ $isDP ? 'DP' : 'Belum Bayar' }}
                        </span>
                    </div>

                    {{-- Customer Info --}}
                    <div class="mb-5 flex-1">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1.5 leading-tight">
                            {{ $order->customer->full_name ?? '-' }}
                        </h3>
                        <div class="flex items-center text-xs sm:text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($order->created_at ?? $order->received_date)->translatedFormat('d F Y, H:i') }}
                        </div>
                    </div>

                    {{-- Footer Action --}}
                    <div class="flex items-end justify-between mt-auto pt-2">
                        <div>
                            <p class="text-[11px] sm:text-xs text-gray-400 font-medium mb-0.5 uppercase tracking-wide">Sisa Tagihan</p>
                            <p class="text-sm sm:text-base font-bold text-gray-900">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ route('payments.index', $order) }}"
                           class="inline-flex items-center justify-center px-5 sm:px-6 py-2 bg-[#0B57D0] hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 text-white text-sm font-semibold rounded-lg transition-all shadow-sm">
                            Bayar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($orders->hasPages())
            <div class="mt-8">
                {{ $orders->links('pagination::tailwind') }}
            </div>
        @endif
    @endif

</div>
@endsection
