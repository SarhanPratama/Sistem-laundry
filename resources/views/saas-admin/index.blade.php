@extends('layouts.app')

@section('title', 'SaaS Platform Superadmin')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 border border-purple-200 text-purple-700 text-xs font-bold rounded-full mb-1">
                <span>⚡ Platform Superadmin Control Panel</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">SaaS Business & Tenant Dashboard</h1>
            <p class="text-xs text-gray-500 mt-0.5">Monitoring seluruh performa bisnis, langganan aktif, dan usaha laundry yang terdaftar.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('saas-admin.tenants') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                Kelola Semua Usaha (Tenants)
            </a>
            <a href="{{ route('saas-admin.plans') }}" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-xs shadow-sm transition">
                Paket & Harga
            </a>
            <a href="{{ route('saas-admin.cms') }}" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-xs shadow-sm transition">
                CMS Web Profile
            </a>
        </div>
    </div>

    {{-- Statistics Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: MRR --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg">
            <p class="text-xs uppercase font-semibold text-blue-200 tracking-wider">Monthly Recurring Revenue (MRR)</p>
            <h3 class="text-2xl sm:text-3xl font-black mt-2">Rp {{ number_format($mrr, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-blue-100 mt-2 flex items-center gap-1">
                <span>Pendapatan berulang dari langganan aktif</span>
            </p>
        </div>

        {{-- Card 2: Total Revenue Paid --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm">
            <p class="text-xs uppercase font-semibold text-gray-400 tracking-wider">Total Pendapatan Terbayar</p>
            <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-emerald-600 font-semibold mt-2">Seluruh pembayaran invoice SaaS yang lunas</p>
        </div>

        {{-- Card 3: Total Tenants & Breakdown --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm">
            <p class="text-xs uppercase font-semibold text-gray-400 tracking-wider">Total Usaha Laundry Terdaftar</p>
            <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-2">{{ $totalTenants }} Usaha</h3>
            <div class="mt-2 flex items-center gap-2 text-[11px]">
                <span class="text-emerald-600 font-bold">{{ $activeTenants }} Aktif</span> •
                <span class="text-amber-600 font-bold">{{ $trialTenants }} Trial</span> •
                <span class="text-red-600 font-bold">{{ $pastDueTenants }} Berakhir</span>
            </div>
        </div>

        {{-- Card 4: Orders Platform-Wide --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm">
            <p class="text-xs uppercase font-semibold text-gray-400 tracking-wider">Total Order Cucian Platform</p>
            <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-2">{{ number_format($totalOrdersPlatform, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-gray-500 mt-2">Volume order operasional seluruh tenant</p>
        </div>
    </div>

    {{-- Split Columns: Recent Tenants & Recent SaaS Invoices --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Tenants --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900">Usaha Laundry Terdaftar Terbaru</h3>
                <a href="{{ route('saas-admin.tenants') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua &rarr;</a>
            </div>

            @if ($recentTenants->isEmpty())
                <p class="text-xs text-gray-400 py-6 text-center">Belum ada tenant usaha terdaftar.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentTenants as $t)
                        <div class="p-3.5 rounded-2xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/50 transition flex items-center justify-between text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-sm">{{ $t->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $t->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($t->status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                                        {{ $t->status }}
                                    </span>
                                </div>
                                <p class="text-gray-500 mt-0.5">
                                    Owner: <span class="text-gray-700 font-medium">{{ $t->owner?->name ?? $t->owner?->username ?? '-' }}</span> •
                                    Paket: <span class="font-semibold text-blue-600">{{ $t->activeSubscription?->plan?->name ?? 'Business' }}</span>
                                </p>
                            </div>

                            <div class="text-right">
                                <span class="font-bold text-gray-800">{{ $t->orders_count }} Order</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $t->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent SaaS Invoices --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900">Tagihan Langganan Terbaru</h3>
                <a href="{{ route('saas-admin.invoices') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua &rarr;</a>
            </div>

            @if ($recentInvoices->isEmpty())
                <p class="text-xs text-gray-400 py-6 text-center">Belum ada transaksi tagihan langganan.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentInvoices as $inv)
                        <div class="p-3.5 rounded-2xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/50 transition flex items-center justify-between text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900">{{ $inv->invoice_number }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $inv->status }}
                                    </span>
                                </div>
                                <p class="text-gray-500 mt-0.5">{{ $inv->tenant?->name }} • Paket {{ $inv->plan?->name }}</p>
                            </div>

                            <div class="text-right">
                                <span class="font-bold text-gray-900">Rp {{ number_format($inv->amount, 0, ',', '.') }}</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $inv->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Incoming Contact Messages / Leads --}}
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-gray-900">Pesan Masuk & Leads Calon Pelanggan (Web Profile)</h3>
                @if (($unreadMessagesCount ?? 0) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">
                        {{ $unreadMessagesCount }} Belum Dibaca
                    </span>
                @endif
            </div>
            <a href="{{ route('saas-admin.messages') }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua Pesan &rarr;</a>
        </div>

        @if (($contactMessages ?? collect())->isEmpty())
            <p class="text-xs text-gray-400 py-6 text-center">Belum ada pesan atau pertanyaan dari pengunjung website.</p>
        @else
            <div class="space-y-3">
                @foreach ($contactMessages as $msg)
                    <div class="p-4 rounded-2xl border {{ $msg->is_read ? 'border-gray-100 bg-gray-50/40' : 'border-blue-200 bg-blue-50/30' }} flex flex-col sm:flex-row sm:items-start justify-between gap-4 text-xs">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm">{{ $msg->name }}</span>
                                @if ($msg->business_name)
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold text-[11px]">{{ $msg->business_name }}</span>
                                @endif
                                <span class="text-blue-600 font-semibold">{{ $msg->contact }}</span>
                                <span class="text-[11px] text-gray-400">• {{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-700 leading-relaxed">{{ $msg->message }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($msg->isReplied())
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-[11px]">✓ Sudah Dibalas</span>
                            @endif
                            <a href="{{ route('saas-admin.messages') }}"
                               class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] transition">
                                {{ $msg->isReplied() ? 'Lihat Balasan' : 'Balas via Email' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

