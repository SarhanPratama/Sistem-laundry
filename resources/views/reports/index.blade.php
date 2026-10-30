@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<div class="mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
    </div>
    
    {{-- Filter Card (Styled like Order Tracking) --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-8">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col lg:flex-row items-end gap-5">

            <div class="w-full lg:flex-1 min-w-0">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rentang Tanggal</label>
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="relative w-full min-w-0">
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full px-2.5 sm:px-3 py-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <span class="text-gray-400 text-xs sm:text-sm font-medium flex-shrink-0">to</span>
                    <div class="relative w-full min-w-0">
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full px-2.5 sm:px-3 py-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Periode</label>
                <div class="relative">
                    <select name="period"
                            class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                        <option value="daily"   {{ $period === 'daily'   ? 'selected' : '' }}>Harian</option>
                        <option value="weekly"  {{ $period === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-auto flex-shrink-0">
                <button type="submit"
                        class="w-full lg:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 border-2 border-[#0B57D0] text-[#0B57D0] hover:bg-blue-50 font-semibold text-sm rounded-xl transition-colors bg-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Tampilkan Filter
                </button>
            </div>

        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Nilai Order</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($orderValueData['total'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Total tagihan order pada periode</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Kas Diterima</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($cashFlowData['total'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Pembayaran masuk (tanggal bayar)</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Piutang</p>
            <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($receivables['outstanding'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $receivables['orders_with_balance'] }} order belum lunas</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Order</p>
            <p class="text-2xl font-bold text-blue-600">{{ $orderSummary['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Semua order dalam periode</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Order Selesai</p>
            <p class="text-2xl font-bold text-gray-700">{{ $orderSummary['selesai'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Status selesai</p>
        </div>
        <div class="bg-white shadow rounded-lg p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Order Dibatalkan</p>
            <p class="text-2xl font-bold text-red-600">{{ $orderSummary['dibatalkan'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Dibatalkan dalam periode</p>
        </div>
    </div>

    {{-- Export Buttons --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <form method="POST" action="{{ route('reports.export') }}">
            @csrf
            <input type="hidden" name="format"     value="pdf">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date"   value="{{ $endDate }}">
            <input type="hidden" name="period"     value="{{ $period }}">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded transition duration-150">
                Ekspor PDF
            </button>
        </form>
        <form method="POST" action="{{ route('reports.export') }}">
            @csrf
            <input type="hidden" name="format"     value="excel">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date"   value="{{ $endDate }}">
            <input type="hidden" name="period"     value="{{ $period }}">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded transition duration-150">
                Ekspor Excel
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Nilai Order & Kas Diterima Table --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Nilai Order &amp; Kas Diterima</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $startDate }} s/d {{ $endDate }} &mdash;
                    @if($period === 'daily') Harian
                    @elseif($period === 'weekly') Mingguan
                    @else Bulanan
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Nilai order dihitung dari tanggal order masuk; kas diterima dihitung dari tanggal pembayaran.
                </p>
            </div>
            @if($periodKeys->isEmpty())
                <div class="text-center py-10 text-gray-400 text-sm">Tidak ada data pada periode ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Order</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kas Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($periodKeys as $periodKey)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $periodKey }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">Rp {{ number_format($orderValueData['data'][$periodKey] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-green-700 text-right font-medium">Rp {{ number_format($cashFlowData['data'][$periodKey] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-sm font-bold text-blue-700 text-right">Rp {{ number_format($orderValueData['total'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-green-600 text-right">Rp {{ number_format($cashFlowData['total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        {{-- Popular Services Table --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Layanan Terpopuler</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $startDate }} s/d {{ $endDate }}</p>
            </div>
            @if(empty($popularServices))
                <div class="text-center py-10 text-gray-400 text-sm">Tidak ada data layanan.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Order</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($popularServices as $i => $service)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $service['service_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $service['count'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ number_format($service['total_quantity'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">Rp {{ number_format($service['total_revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
