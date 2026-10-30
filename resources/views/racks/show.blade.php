@extends('layouts.app')
@section('title', 'Detail Rak ' . $rack->name)
@section('page-title', 'Detail Rak Penyimpanan')

@section('content')
    <div class="mb-5 flex items-center gap-3 space-x-2">
        <a href="{{ route('racks.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rak: {{ $rack->name }}</h1>
            <p class="text-sm text-gray-600">Lokasi: {{ $rack->location_description }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50">
            <div>
                <h2 class="text-base font-bold text-gray-900">Daftar Pesanan di Rak</h2>
                <p class="text-sm text-gray-500 mt-1">Status Rak:
                    @if($rack->is_active)
                        <span class="text-green-600 font-semibold">Aktif</span>
                    @else
                        <span class="text-red-600 font-semibold">Nonaktif</span>
                    @endif
                </p>
            </div>
            <div class="text-right">
                <span class="text-sm font-semibold text-gray-400 uppercase tracking-widest block">Total Pesanan</span>
                <span class="text-2xl font-bold text-gray-900">{{ $rack->orders()->count() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto p-4 sm:p-6">
            <table class="datatable w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th scope="col" class="px-4 py-3.5">No. Order</th>
                    <th scope="col" class="px-4 py-3.5">Pelanggan</th>
                    <th scope="col" class="px-4 py-3.5">Tgl Selesai / Tgl Diterima</th>
                    <th scope="col" class="px-4 py-3.5">Status Transaksi</th>
                    <th scope="col" class="px-4 py-3.5 text-right no-sort">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3.5">
                            <span class="font-bold text-gray-900 font-mono">{{ $order->order_number }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-gray-900">{{ $order->customer->full_name ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $order->customer->phone_number ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-medium text-gray-900">Est: {{ $order->estimated_done_date ? $order->estimated_done_date->format('d M Y') : '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Terima: {{ $order->received_date ? $order->received_date->format('d M Y') : '-' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($order->status == 'siap_diambil')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Siap Diambil</span>
                            @elseif($order->status == 'selesai')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Selesai</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                            @endif

                            @if($order->payment_status == 'lunas')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 ml-1">LUNAS</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 ml-1">BELUM LUNAS</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-right">
                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                                Detail Order &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="text-gray-500 font-medium text-sm">Belum ada pesanan yang ditempatkan pada rak ini.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
