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

    {{-- Total Aktif Banner --}}
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90">Total Order Aktif</p>
                <p class="text-3xl font-bold">{{ $totalAktif }}</p>
                <p class="text-sm opacity-75 mt-1">Order yang sedang diproses</p>
            </div>
            <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('orders.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-400 hover:bg-blue-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Lihat Semua Order</p>
                    <p class="text-sm text-gray-500 mt-1">Kelola pesanan masuk</p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('racks.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-purple-400 hover:bg-purple-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Kelola Rak</p>
                    <p class="text-sm text-gray-500 mt-1">Penempatan barang</p>
                </div>
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m0 0l8-4m0 0l8 4m0 6l-8 4-8-4m0 0l8-4m0 0l8 4"/>
                </svg>
            </div>
        </a>
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
        <h3 class="font-semibold text-gray-900 mb-4">Ringkasan Status Produksi</h3>
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

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('orders.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-400 hover:bg-blue-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Kelola Order</p>
                    <p class="text-sm text-gray-500 mt-1">Lihat semua pesanan</p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('reports.index') }}" class="bg-white rounded-xl border border-gray-200 p-6 hover:border-purple-400 hover:bg-purple-50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">Laporan</p>
                    <p class="text-sm text-gray-500 mt-1">Lihat detail laporan keuangan</p>
                </div>
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </a>
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
@endif

@endsection
