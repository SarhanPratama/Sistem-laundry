@extends('layouts.app')
@section('title', 'Pembayaran - ' . $order->order_number)
@section('page-title', 'Pembayaran')

@section('content')
@php
    $remaining   = max(0, (float)$order->total_cost - (float)$order->amount_paid);
    $payLabels   = ['belum_bayar' => 'Belum Bayar', 'dp' => 'DP', 'lunas' => 'Lunas'];
    $payColors   = ['belum_bayar' => 'text-red-600', 'dp' => 'text-yellow-600', 'lunas' => 'text-green-600'];
    $payBg       = ['belum_bayar' => 'bg-red-50 border-red-200', 'dp' => 'bg-yellow-50 border-yellow-200', 'lunas' => 'bg-green-50 border-green-200'];
@endphp

<div class="mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Pembayaran</h1>
            <p class="text-xs text-gray-400 font-mono">{{ $order->order_number }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Summary + History --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Summary cards --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Total</p>
                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Dibayar</p>
                    <p class="text-lg font-bold text-green-600">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Sisa</p>
                    <p class="text-lg font-bold {{ $remaining > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Payment status badge --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($order->payment_status === 'lunas')
                        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @elseif($order->payment_status === 'dp')
                        <div class="w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400">Status Pembayaran</p>
                        <p class="text-base font-bold {{ $payColors[$order->payment_status] ?? 'text-gray-700' }}">
                            {{ $payLabels[$order->payment_status] ?? $order->payment_status }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Pelanggan</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $order->customer->full_name ?? '-' }}</p>
                </div>
            </div>

            {{-- Payment history --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Riwayat Pembayaran</h2>
                    <span class="text-xs text-gray-400">{{ $order->payments->count() }} transaksi</span>
                </div>

                @if($order->payments->isEmpty())
                    <div class="text-center py-12 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        <p class="text-sm">Belum ada pembayaran.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($order->payments as $payment)
                            <div class="px-5 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                        {{ $payment->method === 'tunai' ? 'bg-green-100' : 'bg-blue-100' }}">
                                        @if($payment->method === 'tunai')
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 capitalize">
                                            {{ $payment->method === 'tunai' ? 'Tunai' : 'Transfer Bank' }}
                                            @if($payment->is_dp)
                                                <span class="ml-1 text-xs font-medium text-yellow-600 bg-yellow-100 px-1.5 py-0.5 rounded">DP</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ $payment->paid_at->format('d M Y, H:i') }}
                                            @if($payment->recordedBy)
                                                &bull; {{ $payment->recordedBy->username }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                    @if($payment->change_amount > 0)
                                        <p class="text-xs text-blue-500 mt-0.5">Kembalian: Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between text-sm">
                        <span class="font-medium text-gray-600">Total Dibayar</span>
                        <span class="font-bold text-green-600">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT: Payment form --}}
        <div class="lg:col-span-1">
            @if(!$order->is_cancelled && $order->payment_status !== 'lunas')
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-20"
                     x-data="{
                         method: '{{ old('method', 'tunai') }}',
                         amount: {{ old('amount', 0) }},
                         remaining: {{ $remaining }},
                         get change() {
                             if (this.method === 'tunai' && parseFloat(this.amount) > this.remaining) {
                                 return parseFloat(this.amount) - this.remaining;
                             }
                             return 0;
                         },
                         fmt(val) { return 'Rp ' + Math.round(val).toLocaleString('id-ID'); }
                     }">

                    <h2 class="text-sm font-bold text-gray-900 mb-4">Catat Pembayaran</h2>

                    {{-- Overpayment confirmation --}}
                    @if(session('confirm_overpayment'))
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                            <p class="text-xs font-semibold text-yellow-800 mb-1">Konfirmasi Kelebihan Bayar</p>
                            <p class="text-xs text-yellow-700 mb-3">
                                Jumlah melebihi sisa tagihan sebesar <strong>Rp {{ number_format(session('overpayment_amount'), 0, ',', '.') }}</strong>. Lanjutkan?
                            </p>
                            <form method="POST" action="{{ route('payments.store', $order) }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="method" value="{{ old('method') }}">
                                <input type="hidden" name="amount" value="{{ old('amount') }}">
                                <input type="hidden" name="is_dp" value="{{ old('is_dp', 0) }}">
                                <input type="hidden" name="confirmed" value="1">
                                <button type="submit" class="flex-1 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold rounded-lg transition">Ya, Lanjutkan</button>
                                <a href="{{ route('payments.index', $order) }}" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition text-center">Batal</a>
                            </form>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payments.store', $order) }}" class="space-y-4">
                        @csrf

                        {{-- Metode --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="method" value="tunai" x-model="method" class="sr-only peer"
                                           {{ old('method', 'tunai') === 'tunai' ? 'checked' : '' }}>
                                    <div class="flex items-center justify-center gap-1.5 py-2.5 border-2 rounded-xl text-sm font-medium transition
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-600 hover:border-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Tunai
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="method" value="transfer" x-model="method" class="sr-only peer"
                                           {{ old('method') === 'transfer' ? 'checked' : '' }}>
                                    <div class="flex items-center justify-center gap-1.5 py-2.5 border-2 rounded-xl text-sm font-medium transition
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-600 hover:border-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Transfer
                                    </div>
                                </label>
                            </div>
                            @error('method') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Jumlah --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Jumlah Bayar (Rp)</label>
                            <input type="number" name="amount" x-model="amount"
                                   value="{{ old('amount') }}"
                                   min="0" step="0.01" placeholder="0"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-400 @enderror">
                            @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Kembalian (tunai only) --}}
                        <div x-show="method === 'tunai' && change > 0"
                             class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex justify-between items-center">
                            <span class="text-xs font-medium text-blue-700">Kembalian</span>
                            <span class="text-sm font-bold text-blue-800" x-text="fmt(change)"></span>
                        </div>

                        {{-- Sisa tagihan hint --}}
                        <div class="bg-gray-50 rounded-xl px-4 py-3 flex justify-between items-center">
                            <span class="text-xs text-gray-500">Sisa tagihan</span>
                            <span class="text-sm font-bold text-gray-700">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                        </div>

                        {{-- DP checkbox --}}
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_dp" value="1"
                                   {{ old('is_dp') ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Ini adalah Uang Muka (DP)</span>
                        </label>

                        <button type="submit"
                                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                            Simpan Pembayaran
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
                    @if($order->is_cancelled)
                        <p class="text-sm text-gray-500">Order ini sudah dibatalkan.</p>
                    @else
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-green-700">Pembayaran Lunas</p>
                        <p class="text-xs text-gray-400 mt-1">Order ini sudah terbayar penuh.</p>
                    @endif
                    <a href="{{ route('orders.show', $order) }}"
                       class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800 font-medium">
                        ← Kembali ke Order
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
