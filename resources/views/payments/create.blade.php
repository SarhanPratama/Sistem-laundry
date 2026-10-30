@extends('layouts.app')

@section('title', 'Catat Pembayaran - ' . $order->order_number)
@section('page-title', 'Catat Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto"
     x-data="{
         method: '{{ old('method', 'tunai') }}',
         amount: {{ old('amount', 0) }},
         totalCost: {{ (float) $order->total_cost }},
         amountPaid: {{ (float) $order->amount_paid }},
         get remaining() {
             return Math.max(0, this.totalCost - this.amountPaid);
         },
         get change() {
             if (this.method === 'tunai' && parseFloat(this.amount) > this.remaining) {
                 return parseFloat(this.amount) - this.remaining;
             }
             return 0;max-w-2xl
         },
         formatRupiah(val) {
             return 'Rp ' + Math.round(val).toLocaleString('id-ID');
         }
     }">

    {{-- Back link --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('payments.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Catat Pembayaran</h1>
    </div>

    {{-- Order Summary --}}
    <div class="bg-white shadow rounded-lg p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Ringkasan Order</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-500">Nomor Order</p>
                <p class="text-sm font-bold font-mono text-gray-900">{{ $order->order_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pelanggan</p>
                <p class="text-sm font-medium text-gray-800">{{ $order->customer->full_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Biaya</p>
                <p class="text-sm font-bold text-gray-900">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Sudah Dibayar</p>
                <p class="text-sm font-medium text-green-700">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Sisa Tagihan</p>
                <p class="text-sm font-bold text-red-600">Rp {{ number_format(max(0, $order->total_cost - $order->amount_paid), 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status Bayar</p>
                @php
                    $payColors = ['belum_bayar' => 'text-red-600', 'dp' => 'text-yellow-600', 'lunas' => 'text-green-600'];
                    $payLabels = ['belum_bayar' => 'Belum Bayar', 'dp' => 'DP', 'lunas' => 'Lunas'];
                @endphp
                <p class="text-sm font-semibold {{ $payColors[$order->payment_status] ?? '' }}">
                    {{ $payLabels[$order->payment_status] ?? $order->payment_status }}
                </p>
            </div>
        </div>
    </div>

    {{-- Overpayment Confirmation --}}
    @if (session('confirm_overpayment'))
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-yellow-800">Konfirmasi Kelebihan Bayar</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        Jumlah yang dibayarkan melebihi sisa tagihan sebesar
                        <strong>Rp {{ number_format(session('overpayment_amount'), 0, ',', '.') }}</strong>.
                        Apakah Anda yakin ingin melanjutkan?
                    </p>
                    <form method="POST" action="{{ route('payments.store', $order) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="hidden" name="method" value="{{ old('method') }}">
                        <input type="hidden" name="amount" value="{{ old('amount') }}">
                        <input type="hidden" name="is_dp" value="{{ old('is_dp', 0) }}">
                        <input type="hidden" name="confirmed" value="1">
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded transition">
                            Ya, Lanjutkan
                        </button>
                        <a href="{{ route('orders.show', $order) }}"
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded transition">
                            Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Payment Form --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-base font-semibold text-gray-700 mb-5">Form Pembayaran</h2>

        <form method="POST" action="{{ route('payments.store', $order) }}">
            @csrf

            {{-- Metode Pembayaran --}}
            <div class="mb-5">
                <label for="method" class="block text-sm font-medium text-gray-700 mb-1">
                    Metode Pembayaran <span class="text-red-500">*</span>
                </label>
                <select id="method" name="method" x-model="method"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('method') border-red-400 @enderror">
                    <option value="tunai" {{ old('method') === 'tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="transfer" {{ old('method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                </select>
                @error('method')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jumlah Bayar --}}
            <div class="mb-5">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Jumlah Bayar (Rp) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="amount" name="amount"
                       x-model="amount"
                       value="{{ old('amount') }}"
                       min="0.01" step="0.01"
                       placeholder="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-400 @enderror">
                @error('amount')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kembalian (tunai only, Alpine.js reactive) --}}
            <div x-show="method === 'tunai' && change > 0"
                 class="mb-5 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-blue-700">Kembalian</span>
                    <span class="text-base font-bold text-blue-800" x-text="formatRupiah(change)"></span>
                </div>
            </div>

            {{-- Uang Muka (DP) --}}
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_dp" value="1"
                           {{ old('is_dp') ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Ini adalah Uang Muka (DP)</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 ml-6">Centang jika pembayaran ini merupakan uang muka, bukan pelunasan.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 sm:flex-none px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Pembayaran
                </button>
                <a href="{{ route('orders.show', $order) }}"
                   class="flex-1 sm:flex-none px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
