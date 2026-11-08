@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">
            @if($is_operator) Produktivitas laundry @elseif($is_admin) Overview bisnis hari ini @else Ringkasan operasional hari ini @endif
        </p>
    </div>
    <div class="text-right">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</p>
        <p class="text-sm font-bold text-gray-800">{{ now()->translatedFormat('d F Y') }}</p>
    </div>
</div>

@if($is_operator)
    {{-- Operator Dashboard: Production Status Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">

        {{-- Diterima --}}
        <a href="{{ route('orders.index', ['status' => 'diterima']) }}" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hover:bg-blue-100 transition">
            <div class="text-center">
                <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['diterima'] ?? 0 }}</p>
                <p class="text-xs text-blue-600 mt-1">Diterima</p>
            </div>
        </a>

        {{-- Dicuci --}}
        <a href="{{ route('orders.index', ['status' => 'dicuci']) }}" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 hover:bg-yellow-100 transition">
            <div class="text-center">
                <svg class="w-8 h-8 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['dicuci'] ?? 0 }}</p>
                <p class="text-xs text-yellow-600 mt-1">Dicuci</p>
            </div>
        </a>

        {{-- Dikeringkan --}}
        <a href="{{ route('orders.index', ['status' => 'dikeringkan']) }}" class="bg-orange-50 border border-orange-200 rounded-xl p-4 hover:bg-orange-100 transition">
            <div class="text-center">
                <svg class="w-8 h-8 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['dikeringkan'] ?? 0 }}</p>
                <p class="text-xs text-orange-600 mt-1">Dikeringkan</p>
            </div>
        </a>

        {{-- Disetrika --}}
        <a href="{{ route('orders.index', ['status' => 'disetrika']) }}" class="bg-purple-50 border border-purple-200 rounded-xl p-4 hover:bg-purple-100 transition">
            <div class="text-center">
                <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['disetrika'] ?? 0 }}</p>
                <p class="text-xs text-purple-600 mt-1">Disetrika</p>
            </div>
        </a>

        {{-- Siap Diambil --}}
        <a href="{{ route('orders.index', ['status' => 'siap_diambil']) }}" class="bg-green-50 border border-green-200 rounded-xl p-4 hover:bg-green-100 transition">
            <div class="text-center">
                <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <p class="text-2xl font-bold text-green-600">{{ $stats['siap_diambil'] ?? 0 }}</p>
                <p class="text-xs text-green-600 mt-1">Siap Diambil</p>
            </div>
        </a>
    </div>

    {{-- Ringkasan Beban Kerja & Target Produksi Hari Ini --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 text-white flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">Total Order Aktif</p>
                <p class="text-3xl font-bold mt-1">{{ $totalAktif }}</p>
                <p class="text-xs text-blue-100 mt-1">Order sedang dalam siklus laundry</p>
            </div>
            <svg class="w-12 h-12 opacity-25" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>

        <div class="bg-white rounded-xl border {{ ($orderPrioritasCount ?? 0) > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-gray-200' }} p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider {{ ($orderPrioritasCount ?? 0) > 0 ? 'text-amber-700' : 'text-gray-400' }}">Deadline Hari Ini / Terlambat</p>
                <p class="text-3xl font-bold {{ ($orderPrioritasCount ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $orderPrioritasCount ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Wajib diprioritaskan selesai</p>
            </div>
            <div class="w-12 h-12 rounded-xl {{ ($orderPrioritasCount ?? 0) > 0 ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Selesai Masuk Rak Hari Ini</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $selesaiProduksiHariIni ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Cucian selesai disetrika hari ini</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('orders.status') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-400 hover:bg-blue-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Pelacakan Status Produksi</p>
                    <p class="text-xs text-gray-500 mt-1">Update tahapan cuci, kering, setrika</p>
                </div>
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('orders.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-400 hover:bg-blue-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Lihat Semua Order</p>
                    <p class="text-xs text-gray-500 mt-1">Cari & filter daftar pesanan</p>
                </div>
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('racks.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-purple-400 hover:bg-purple-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Kelola Rak</p>
                    <p class="text-xs text-gray-500 mt-1">Cek kapasitas & lokasi penyimpanan</p>
                </div>
                <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m0 0l8-4m0 0l8 4m0 6l-8 4-8-4m0 0l8-4m0 0l8 4"/>
                </svg>
            </div>
        </a>
    </div>

    {{-- Area Kerja Operator: Antrean Prioritas Produksi & Kapasitas Rak --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tabel Antrean Produksi Prioritas (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Antrean Kerja Produksi (Urutan Prioritas Deadline)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Langsung majukan tahap produksi atau pilih rak penyimpanan dari sini</p>
                </div>
                <a href="{{ route('orders.status') }}" class="text-xs font-bold text-blue-600 hover:underline">Buka Layar Penuh &rarr;</a>
            </div>
            @if(($antreanProduksi ?? collect())->isEmpty())
                <div class="p-10 text-center text-sm text-gray-400">
                    Semua cucian sudah selesai diproses! Tidak ada antrean produksi saat ini.
                </div>
            @else
                @php
                    $opNextMap = [
                        'diterima' => ['next' => 'dicuci', 'label' => 'Mulai Cuci', 'badge' => 'bg-blue-100 text-blue-700', 'cur' => 'Baru Masuk'],
                        'dicuci' => ['next' => 'dikeringkan', 'label' => 'Keringkan', 'badge' => 'bg-yellow-100 text-yellow-700', 'cur' => 'Sedang Dicuci'],
                        'dikeringkan' => ['next' => 'disetrika', 'label' => 'Setrika', 'badge' => 'bg-orange-100 text-orange-700', 'cur' => 'Dikeringkan'],
                        'disetrika' => ['next' => 'siap_diambil', 'label' => 'Masuk Rak', 'badge' => 'bg-purple-100 text-purple-700', 'cur' => 'Disetrika'],
                    ];
                @endphp
                <div class="divide-y divide-gray-100">
                    @foreach($antreanProduksi as $opOrder)
                        @php
                            $stageInfo = $opNextMap[$opOrder->status] ?? null;
                            $isOverdueOp = $opOrder->estimated_done_date && $opOrder->estimated_done_date->lt(today());
                            $isDueTodayOp = $opOrder->estimated_done_date && $opOrder->estimated_done_date->isToday();
                            $firstOpItem = $opOrder->items->first();
                            $opItemSummary = $firstOpItem
                                ? ($firstOpItem->service->name ?? 'Layanan') . ' (' . (float) $firstOpItem->quantity . ' ' . ($firstOpItem->service->unit ?? '') . ')'
                                : '-';
                            if ($opOrder->items->count() > 1) {
                                $opItemSummary .= ' +' . ($opOrder->items->count() - 1) . ' item';
                            }
                        @endphp
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('orders.show', $opOrder) }}" class="text-xs font-mono font-bold text-blue-600 hover:underline">
                                        {{ $opOrder->order_number }}
                                    </a>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $stageInfo['badge'] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $stageInfo['cur'] ?? $opOrder->status }}
                                    </span>
                                    @if($isOverdueOp)
                                        <span class="px-2 py-0.5 rounded-md bg-red-100 text-red-700 text-[10px] font-extrabold uppercase">
                                            Terlambat ({{ $opOrder->estimated_done_date->format('d/m') }})
                                        </span>
                                    @elseif($isDueTodayOp)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 text-[10px] font-extrabold uppercase">
                                            Target Hari Ini
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm font-bold text-gray-900 mt-1 truncate">{{ $opOrder->customer->full_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $opItemSummary }}</p>
                            </div>

                            @if($stageInfo)
                                <form method="POST" action="{{ route('orders.status.update', $opOrder) }}" class="flex items-center gap-2 flex-shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $stageInfo['next'] }}">

                                    @if($stageInfo['next'] === 'siap_diambil')
                                        @if(($racks ?? collect())->isNotEmpty())
                                            <select name="rack_id" required
                                                class="rounded-lg border-indigo-300 bg-indigo-50/70 text-xs font-bold text-indigo-900 py-1.5 px-2.5 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                <option value="" disabled selected>Pilih Rak *</option>
                                                @foreach($racks as $rack)
                                                    <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @endif

                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0B57D0] hover:bg-blue-800 text-white text-xs font-bold transition shadow-sm whitespace-nowrap">
                                        <span>{{ $stageInfo['label'] }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Panel Kanan: Status Ketersediaan Rak Penyimpanan (1/3) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Ketersediaan Rak Saat Ini</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Jumlah cucian siap diambil di tiap rak</p>
                </div>
                <a href="{{ route('racks.index') }}" class="text-xs font-bold text-purple-600 hover:underline">Master Rak &rarr;</a>
            </div>
            @if(($racks ?? collect())->isEmpty())
                <div class="p-8 text-center text-sm text-gray-400">
                    Belum ada data rak aktif.
                    <div class="mt-2">
                        <a href="{{ route('racks.create') }}" class="text-xs font-bold text-blue-600 hover:underline">+ Tambah Rak Baru</a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($racks as $rack)
                        <a href="{{ route('racks.show', $rack) }}" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900">Rak {{ $rack->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $rack->location_description }}</p>
                            </div>
                            @if(($rack->orders_count ?? 0) === 0)
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                    Kosong
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold">
                                    {{ $rack->orders_count }} Cucian
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@elseif($is_admin)
    {{-- Admin Dashboard: Comprehensive Business Metrics --}}

    {{-- Key Financial Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        {{-- Pendapatan Hari Ini --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-2">Pendapatan Hari Ini</p>
                <p class="text-xl font-bold text-green-600">
                    Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1.5">Order lunas selesai</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Pendapatan Bulan Ini --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-2">Pendapatan Bulan Ini</p>
                <p class="text-xl font-bold text-blue-600">
                    Rp {{ number_format($totalPendapatanBulanIni, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1.5">Total periode bulan</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>

        {{-- Total Order Bulan Ini --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-2">Order Bulan Ini</p>
                <p class="text-xl font-bold text-purple-600">{{ $totalOrderBulanIni }}</p>
                <p class="text-xs text-gray-400 mt-1.5">Total pesanan masuk</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        {{-- Total Pelanggan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-2">Total Pelanggan</p>
                <p class="text-xl font-bold text-indigo-600">{{ $totalPelanggan }}</p>
                <p class="text-xs text-gray-400 mt-1.5">Customer unik</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Rincian Arus Kas Hari Ini (Tunai Laci vs Transfer Bank) & Total Piutang --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Kas Tunai di Laci Kasir Hari Ini</p>
                <p class="text-lg font-bold text-emerald-600 mt-1">Rp {{ number_format($kasTunaiHariIni ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Uang fisik tunai diterima hari ini</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold">Tunai</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Transfer Masuk Rekening Hari Ini</p>
                <p class="text-lg font-bold text-blue-600 mt-1">Rp {{ number_format($transferHariIni ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Pembayaran non-tunai hari ini</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">Transfer</span>
        </div>

        <a href="{{ route('payments.list') }}" class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between hover:border-amber-400 hover:bg-amber-50/40 transition">
            <div>
                <p class="text-xs font-medium text-gray-500">Total Piutang Belum Lunas ({{ $orderBelumLunasCount ?? 0 }} Order)</p>
                <p class="text-lg font-bold text-amber-600 mt-1">Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Klik untuk pantau tagihan belum lunas &rarr;</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">Piutang</span>
        </a>
    </div>

    {{-- Peringatan Order Terlambat (Overdue Alert) untuk Admin --}}
    @if(($orderTerlambatCount ?? 0) > 0)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold flex-shrink-0">!</div>
                <div>
                    <p class="text-sm font-bold text-red-800">Perhatian Operasional: {{ $orderTerlambatCount }} Order Melewati Estimasi Selesai</p>
                    <p class="text-xs text-red-600">Terdapat pesanan yang belum selesai meski sudah melewati jadwal estimasi pengambilan pelanggan.</p>
                </div>
            </div>
            <a href="{{ route('orders.status') }}" class="px-3.5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition text-center whitespace-nowrap">
                Pantau Produksi
            </a>
        </div>
    @endif

    {{-- Operational Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Order Aktif --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Order Aktif</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $jumlahOrderAktif }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400">Sedang diproses</p>
        </div>

        {{-- Siap Diambil --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Siap Diambil</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $jumlahOrderSiapDiambil }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400">Menunggu pelanggan</p>
        </div>

        {{-- Selesai Hari Ini --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Selesai Hari Ini</p>
                    <p class="text-2xl font-bold text-green-600">{{ $jumlahOrderSelesai }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400">Selesai hari ini</p>
        </div>
    </div>

    {{-- Production Status Breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Ringkasan Status Produksi</h3>
            <a href="{{ route('orders.status') }}" class="text-xs font-bold text-blue-600 hover:underline">Buka Pelacakan &rarr;</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ $statusStats['diterima'] ?? 0 }}</p>
                <p class="text-xs text-gray-600 mt-1">Diterima</p>
            </div>
            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600">{{ $statusStats['dicuci'] ?? 0 }}</p>
                <p class="text-xs text-gray-600 mt-1">Dicuci</p>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <p class="text-2xl font-bold text-orange-600">{{ $statusStats['dikeringkan'] ?? 0 }}</p>
                <p class="text-xs text-gray-600 mt-1">Dikeringkan</p>
            </div>
            <div class="text-center p-3 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">{{ $statusStats['disetrika'] ?? 0 }}</p>
                <p class="text-xs text-gray-600 mt-1">Disetrika</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ $statusStats['siap_diambil'] ?? 0 }}</p>
                <p class="text-xs text-gray-600 mt-1">Siap Diambil</p>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Pendapatan 7 Hari Terakhir & Top Layanan Bulan Ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Tren Pendapatan 7 Hari Terakhir (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            @php
                $trendData = collect($revenueTrend7Days ?? []);
                $maxRevenue7Days = max(1, (float) $trendData->max('amount'));
                $totalRevenue7Days = (float) $trendData->sum('amount');
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Tren Pendapatan Kas 7 Hari Terakhir</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Total 7 hari: <strong class="text-gray-800">Rp {{ number_format($totalRevenue7Days, 0, ',', '.') }}</strong></p>
                </div>
                <a href="{{ route('reports.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Laporan Lengkap &rarr;</a>
            </div>

            <div class="grid grid-cols-7 gap-2 sm:gap-3 items-end h-44 pt-6 border-b border-gray-100 pb-3">
                @foreach($trendData as $dayItem)
                    @php
                        $heightPct = $dayItem['amount'] > 0
                            ? max(12, round(($dayItem['amount'] / $maxRevenue7Days) * 100))
                            : 4;
                    @endphp
                    <div class="flex flex-col items-center justify-end h-full group">
                        <span class="text-[10px] font-bold {{ $dayItem['is_today'] ? 'text-blue-600' : 'text-gray-500' }} mb-1.5 truncate max-w-full">
                            @if($dayItem['amount'] >= 1000)
                                {{ number_format($dayItem['amount'] / 1000, 0, ',', '.') }}rb
                            @else
                                Rp 0
                            @endif
                        </span>
                        <div class="w-full max-w-[40px] rounded-t-lg transition-all duration-300 {{ $dayItem['is_today'] ? 'bg-blue-600 shadow-sm' : ($dayItem['amount'] > 0 ? 'bg-blue-200 group-hover:bg-blue-400' : 'bg-gray-100') }}"
                             style="height: {{ $heightPct }}%;"
                             title="{{ $dayItem['day_name'] }}, {{ $dayItem['date'] }}: Rp {{ number_format($dayItem['amount'], 0, ',', '.') }}">
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-2 sm:gap-3 pt-2.5 text-center">
                @foreach($trendData as $dayItem)
                    <div>
                        <p class="text-[11px] font-bold {{ $dayItem['is_today'] ? 'text-blue-600' : 'text-gray-700' }}">{{ $dayItem['day_name'] }}</p>
                        <p class="text-[10px] text-gray-400">{{ $dayItem['date'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top 5 Layanan Terlaris Bulan Ini (1/3) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Layanan Terlaris Bulan Ini</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Berdasarkan kontribusi nilai order</p>
                </div>
                <a href="{{ route('services.index') }}" class="text-xs font-bold text-purple-600 hover:underline">Layanan &rarr;</a>
            </div>

            @if(($topServicesBulanIni ?? collect())->isEmpty())
                <div class="flex-1 flex items-center justify-center text-center py-8 text-sm text-gray-400">
                    Belum ada transaksi layanan bulan ini.
                </div>
            @else
                @php
                    $maxServiceOmzet = max(1, (float) $topServicesBulanIni->max('total_omzet'));
                @endphp
                <div class="space-y-4">
                    @foreach($topServicesBulanIni as $idx => $topItem)
                        @php
                            $barWidth = max(10, round(((float) $topItem->total_omzet / $maxServiceOmzet) * 100));
                            $qtyClean = (float) $topItem->total_qty == floor((float) $topItem->total_qty)
                                ? (int) $topItem->total_qty
                                : (float) $topItem->total_qty;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-gray-800 truncate pr-2">
                                    {{ $idx + 1 }}. {{ $topItem->service->name ?? 'Layanan' }}
                                </span>
                                <span class="font-bold text-gray-900 whitespace-nowrap">
                                    Rp {{ number_format($topItem->total_omzet, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 mb-1">
                                <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $barWidth }}%"></div>
                            </div>
                            <p class="text-[11px] text-gray-400">
                                {{ $qtyClean }} {{ $topItem->service->unit ?? '' }} &bull; {{ $topItem->total_orders }} order
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel Order Terbaru & Quick Actions Admin --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Order Terbaru (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Transaksi & Order Terbaru</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pantau pesanan terbaru yang masuk ke sistem</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Semua Order &rarr;</a>
            </div>
            @if(($recentOrdersAdmin ?? collect())->isEmpty())
                <div class="p-8 text-center text-sm text-gray-400">
                    Belum ada transaksi order.
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($recentOrdersAdmin as $admOrder)
                        <div class="px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('orders.show', $admOrder) }}" class="text-xs font-mono font-bold text-blue-600 hover:underline">
                                        {{ $admOrder->order_number }}
                                    </a>
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-bold uppercase">
                                        {{ str_replace('_', ' ', $admOrder->status) }}
                                    </span>
                                    @if($admOrder->rack)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold">
                                            Rak {{ $admOrder->rack->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm font-bold text-gray-900 truncate mt-0.5">{{ $admOrder->customer->full_name ?? '-' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-gray-900">Rp {{ number_format($admOrder->total_cost, 0, ',', '.') }}</p>
                                @if($admOrder->payment_status === 'lunas')
                                    <span class="text-[11px] font-bold text-emerald-600">LUNAS</span>
                                @elseif($admOrder->payment_status === 'dp')
                                    <span class="text-[11px] font-bold text-amber-600">DP</span>
                                @else
                                    <span class="text-[11px] font-bold text-red-600">BELUM BAYAR</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pintasan Manajemen Admin (1/3) --}}
        <div class="space-y-3">
            <a href="{{ route('orders.create') }}" class="flex items-center justify-between bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-4 shadow-sm transition">
                <div>
                    <p class="font-bold text-sm">Buat Order Baru</p>
                    <p class="text-xs text-blue-100 mt-0.5">Input transaksi kasir baru</p>
                </div>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </a>

            <a href="{{ route('orders.index') }}" class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 hover:border-blue-400 hover:bg-blue-50 transition">
                <div>
                    <p class="font-bold text-sm text-gray-900">Kelola Order</p>
                    <p class="text-xs text-gray-500 mt-0.5">Lihat semua pesanan</p>
                </div>
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 hover:border-purple-400 hover:bg-purple-50 transition">
                <div>
                    <p class="font-bold text-sm text-gray-900">Laporan Keuangan</p>
                    <p class="text-xs text-gray-500 mt-0.5">Analisis & ekspor laporan</p>
                </div>
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </a>

            <a href="{{ route('settings.index') }}" class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 hover:border-gray-400 hover:bg-gray-50 transition">
                <div>
                    <p class="font-bold text-sm text-gray-900">Pengaturan Usaha</p>
                    <p class="text-xs text-gray-500 mt-0.5">Profil toko, struk & rekening</p>
                </div>
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>
    </div>

@else
    {{-- Kasir Dashboard: Financial Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Total Pendapatan Hari Ini --}}
        <a href="{{ route('orders.index') }}"
           class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <div>
                <p class="text-sm text-gray-500 mb-2">Total Pendapatan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900">
                    Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1.5">Order lunas yang selesai hari ini</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </a>

        {{-- Jumlah Order Aktif --}}
        <a href="{{ route('orders.index') }}"
           class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <div>
                <p class="text-sm text-gray-500 mb-2">Order Aktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $jumlahOrderAktif }}</p>
                <p class="text-xs text-gray-400 mt-1.5">Order yang sedang diproses</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </a>

        {{-- Jumlah Order Siap Diambil --}}
        <a href="{{ route('orders.index') }}"
           class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <div>
                <p class="text-sm text-gray-500 mb-2">Siap Diambil</p>
                <p class="text-2xl font-bold text-gray-900">{{ $jumlahOrderSiapDiambil }}</p>
                <p class="text-xs text-gray-400 mt-1.5">Menunggu pengambilan pelanggan</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </a>

    </div>

    {{-- Baris 2: Rincian Kas Laci Kasir (Tunai vs Transfer) & Piutang Belum Lunas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Uang Tunai di Laci Hari Ini</p>
                <p class="text-lg font-bold text-emerald-600 mt-1">Rp {{ number_format($kasTunaiHariIni ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Wajib cocok saat tutup shift</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold">Tunai</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500">Transfer Masuk Hari Ini</p>
                <p class="text-lg font-bold text-blue-600 mt-1">Rp {{ number_format($transferHariIni ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Masuk ke rekening toko</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">Transfer</span>
        </div>

        <a href="{{ route('payments.list') }}" class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between hover:border-amber-400 hover:bg-amber-50/40 transition">
            <div>
                <p class="text-xs font-medium text-gray-500">Belum Lunas / DP ({{ $orderBelumLunasCount ?? 0 }} Order)</p>
                <p class="text-lg font-bold text-amber-600 mt-1">Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Klik untuk kelola pelunasan &rarr;</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">Piutang</span>
        </a>
    </div>

    {{-- Peringatan Order Terlambat (Overdue) jika ada --}}
    @if(($orderTerlambatCount ?? 0) > 0)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold">!</div>
                <div>
                    <p class="text-sm font-bold text-red-800">Perhatian: Ada {{ $orderTerlambatCount }} Order Melewati Estimasi Selesai</p>
                    <p class="text-xs text-red-600">Segera koordinasikan dengan bagian produksi agar cucian pelanggan dapat diselesaikan.</p>
                </div>
            </div>
            <a href="{{ route('orders.status') }}" class="px-3.5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition">
                Cek Status Produksi
            </a>
        </div>
    @endif

    {{-- Baris 3: Tombol Aksi Cepat Kasir (Quick POS Actions) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <a href="{{ route('orders.create') }}" class="flex items-center justify-center gap-2.5 py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Order Baru
        </a>

        <a href="{{ route('customers.create') }}" class="flex items-center justify-center gap-2.5 py-3.5 px-4 bg-white border border-gray-200 hover:border-blue-400 hover:bg-blue-50 text-gray-800 font-bold text-sm rounded-xl transition">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Tambah Pelanggan Baru
        </a>

        <a href="{{ route('payments.list') }}" class="flex items-center justify-center gap-2.5 py-3.5 px-4 bg-white border border-gray-200 hover:border-emerald-400 hover:bg-emerald-50 text-gray-800 font-bold text-sm rounded-xl transition">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Daftar Tagihan / Pelunasan
        </a>
    </div>

    {{-- Baris 4: Antrean Siap Diambil (Beserta Lokasi Rak) & Order Masuk Hari Ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Kolom Kiri: Cucian Siap Diambil Pelanggan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Cucian Siap Diambil Pelanggan</h3>
                    <p class="text-xs text-gray-500">Cek posisi rak dan status lunas sebelum diserahkan</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                    {{ $jumlahOrderSiapDiambil }} Siap
                </span>
            </div>

            @if(($ordersSiapDiambil ?? collect())->isEmpty())
                <p class="text-xs text-gray-400 py-8 text-center">Belum ada cucian yang berstatus Siap Diambil saat ini.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($ordersSiapDiambil as $readyOrder)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-gray-900">{{ $readyOrder->order_number }}</span>
                                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold text-[11px]">
                                        Rak: {{ $readyOrder->rack?->name ?? 'Belum diatur' }}
                                    </span>
                                </div>
                                <p class="text-gray-600 mt-0.5 font-medium">{{ $readyOrder->customer?->full_name ?? '-' }}</p>
                            </div>

                            <div class="flex items-center gap-2.5">
                                @if($readyOrder->payment_status === 'lunas')
                                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-bold text-[10px]">LUNAS</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-bold text-[10px]">
                                        Sisa Rp {{ number_format($readyOrder->remaining_amount, 0, ',', '.') }}
                                    </span>
                                @endif
                                <a href="{{ route('orders.show', $readyOrder) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold transition">
                                    Buka &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Order Masuk Hari Ini --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Order Masuk Hari Ini</h3>
                    <p class="text-xs text-gray-500">Pesanan terbaru yang diterima hari ini</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                    {{ ($ordersHariIni ?? collect())->count() }} Terbaru
                </span>
            </div>

            @if(($ordersHariIni ?? collect())->isEmpty())
                <p class="text-xs text-gray-400 py-8 text-center">Belum ada order baru yang masuk hari ini.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($ordersHariIni as $todayOrder)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-gray-900">{{ $todayOrder->order_number }}</span>
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-semibold text-[10px] uppercase">
                                        {{ str_replace('_', ' ', $todayOrder->status) }}
                                    </span>
                                </div>
                                <p class="text-gray-600 mt-0.5 font-medium">
                                    {{ $todayOrder->customer?->full_name ?? '-' }} &bull; Rp {{ number_format($todayOrder->total_cost, 0, ',', '.') }}
                                </p>
                            </div>

                            <a href="{{ route('orders.show', $todayOrder) }}" class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                                Nota / Detail
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endif

@endsection
