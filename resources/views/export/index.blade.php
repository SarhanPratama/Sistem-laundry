@extends('layouts.app')

@section('title', 'Ekspor Data Usaha')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Ekspor & Portabilitas Data</h1>
        <p class="text-xs text-gray-500 mt-0.5">Unduh seluruh catatan bisnis laundry Anda dalam format spreadsheet CSV (kompatibel Excel & Google Sheets).</p>
    </div>

    {{-- Guarantee Banner --}}
    <div class="p-5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 flex items-start gap-3 shadow-sm">
        <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <div>
            <h4 class="font-bold text-sm">Prinsip Kepemilikan Data Penuh</h4>
            <p class="text-xs text-blue-700 mt-0.5">
                Data pelanggan dan transaksi adalah 100% milik usaha laundry Anda. Kapan pun Anda membutuhkan cadangan atau jika berhenti berlangganan, seluruh data dapat diunduh tanpa batasan.
            </p>
        </div>
    </div>

    {{-- Export Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Export Customers --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Data Pelanggan</h3>
                <p class="text-xs text-gray-500 mt-1">Nama lengkap, nomor telepon, alamat, dan status keaktifan pelanggan.</p>
                <p class="text-xs font-semibold text-blue-600 mt-3">{{ number_format($totalCustomers) }} Kontak Pelanggan</p>
            </div>

            <div class="mt-6">
                <a href="{{ route('export.customers') }}" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV Pelanggan
                </a>
            </div>
        </div>

        {{-- Export Orders --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Data Order Cucian</h3>
                <p class="text-xs text-gray-500 mt-1">Nomor pesanan, tanggal masuk, estimasi, status cuci, dan nilai tagihan.</p>
                <p class="text-xs font-semibold text-indigo-600 mt-3">{{ number_format($totalOrders) }} Transaksi Order</p>
            </div>

            <div class="mt-6">
                <a href="{{ route('export.orders') }}" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV Order
                </a>
            </div>
        </div>

        {{-- Export Payments --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Data Kas & Pembayaran</h3>
                <p class="text-xs text-gray-500 mt-1">Seluruh riwayat pembayaran tunai & transfer, nominal DP, dan kembalian.</p>
                <p class="text-xs font-semibold text-emerald-600 mt-3">{{ number_format($totalPayments) }} Catatan Pembayaran</p>
            </div>

            <div class="mt-6">
                <a href="{{ route('export.payments') }}" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV Pembayaran
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

