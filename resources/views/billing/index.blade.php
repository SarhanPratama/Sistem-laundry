@extends('layouts.app')

@section('title', 'Langganan & Tagihan')

@section('content')
<div class="max-w-8xl mx-auto space-y-8" x-data="{ cycle: 'monthly' }">

    {{-- Top Alert Messages --}}
    @if (session('subscription_warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h4 class="font-bold text-sm">Peringatan Masa Tenggang</h4>
                <p class="text-xs mt-0.5">{{ session('subscription_warning') }}</p>
            </div>
        </div>
    @endif

    {{-- Header Banner: Current Status --}}
    <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full {{ $tenant->status === 'active' ? 'bg-emerald-400' : ($tenant->status === 'trial' ? 'bg-amber-300' : 'bg-red-400') }}"></span>
                Status Usaha: {{ strtoupper($tenant->status) }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Paket {{ $currentPlan?->name ?? 'Business' }}</h1>
            <p class="text-blue-100 text-sm mt-1 max-w-xl">
                @if ($tenant->isInTrial())
                    Masa Trial Aktif: Tersisa <span class="font-bold text-white underline">{{ $tenant->daysRemaining() }} hari</span> (berakhir {{ $tenant->trial_ends_at?->format('d M Y') }}).
                @elseif ($tenant->hasActivePaidSubscription())
                    Langganan Aktif hingga <span class="font-bold text-white underline">{{ $subscription?->ends_at?->format('d M Y') }}</span> (tersisa {{ $tenant->daysRemaining() }} hari).
                @elseif ($tenant->isInGracePeriod())
                    <span class="font-bold text-amber-200">Masa aktif telah berakhir</span>. Anda dalam Masa Tenggang 7 Hari.
                @else
                    <span class="font-bold text-red-200">Langganan berakhir</span>. Sistem dalam mode Read-Only.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="#pilih-paket" class="px-5 py-3 rounded-xl bg-white text-blue-700 font-bold text-sm hover:bg-blue-50 transition shadow-sm">
                Perpanjang / Ganti Paket
            </a>
            <a href="{{ route('export.index') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm transition backdrop-blur-md">
                Ekspor Data
            </a>
        </div>
    </div>

    {{-- Section: Plans Pricing --}}
    <div id="pilih-paket" class="space-y-6">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900">Pilihan Paket Langganan</h2>
            <p class="text-sm text-gray-500 mt-1">Pilih paket yang sesuai dengan kapasitas outlet dan skala usaha laundry Anda.</p>

            {{-- Monthly / Yearly Toggle --}}
            <div class="mt-4 inline-flex items-center p-1 bg-gray-100 rounded-2xl border border-gray-200">
                <button type="button" @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-white shadow text-gray-900 font-bold' : 'text-gray-500 font-medium'"
                        class="px-4 py-1.5 rounded-xl text-xs transition">
                    Bulanan
                </button>
                <button type="button" @click="cycle = 'yearly'"
                        :class="cycle === 'yearly' ? 'bg-white shadow text-blue-600 font-bold' : 'text-gray-500 font-medium'"
                        class="px-4 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                    <span>Tahunan</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-bold">Hemat 2 Bulan!</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($plans as $p)
                @php
                    $isPopular = $p->slug === 'business';
                @endphp
                <div class="relative bg-white rounded-3xl border {{ $isPopular ? 'border-2 border-blue-600 shadow-xl shadow-blue-500/10' : 'border-gray-200 shadow-sm' }} p-6 sm:p-7 flex flex-col justify-between transition hover:-translate-y-1">
                    @if ($isPopular)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 bg-blue-600 text-white text-[11px] font-black uppercase tracking-wider rounded-full shadow-sm">
                            Paling Populer & Direkomendasikan
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">{{ $p->name }}</h3>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 font-medium text-gray-600">
                                {{ $p->max_outlets }} Outlet
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ $p->description }}</p>

                        {{-- Price Display --}}
                        <div class="mt-5 pb-5 border-b border-gray-100">
                            <div x-show="cycle === 'monthly'">
                                <span class="text-3xl font-black text-gray-900">Rp {{ number_format($p->price_monthly, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-400">/ bulan</span>
                            </div>
                            <div x-show="cycle === 'yearly'" style="display: none;">
                                <span class="text-3xl font-black text-blue-600">Rp {{ number_format($p->price_yearly, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-400">/ tahun</span>
                                <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">Setara Rp {{ number_format($p->price_yearly / 12, 0, ',', '.') }} / bulan</p>
                            </div>
                        </div>

                        {{-- Features List --}}
                        <div class="mt-5 space-y-2.5">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Fitur Utama:</p>
                            @if ($p->features)
                                @foreach ($p->features as $f)
                                    <div class="flex items-start gap-2.5 text-xs text-gray-600">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $f }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 pt-4">
                        <form method="POST" action="{{ route('billing.plan.choose') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $p->id }}">
                            <input type="hidden" name="billing_cycle" :value="cycle">
                            <button type="submit"
                                    class="w-full py-3 px-4 rounded-xl font-bold text-xs transition shadow-sm {{ $isPopular ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/25' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}">
                                Pilih & Buat Tagihan
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Section: Invoices History --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-bold text-gray-900">Riwayat Tagihan & Pembayaran Langganan</h3>
            <p class="text-xs text-gray-500 mt-0.5">Catatan tagihan resmi sistem SaaS untuk usaha laundry Anda.</p>
        </div>

        @if ($invoices->isEmpty())
            <div class="py-10 text-center text-gray-400 text-xs">
                Belum ada tagihan langganan yang tercatat.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nomor Tagihan</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Paket & Siklus</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Jumlah (Rp)</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider no-sort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($invoices as $inv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900 font-mono">
                                    {{ $inv->invoice_number }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-600">
                                    Paket {{ $inv->plan?->name }} ({{ $inv->billing_cycle === 'yearly' ? 'Tahunan' : 'Bulanan' }})
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900" data-order="{{ (int) $inv->amount }}">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500" data-order="{{ $inv->due_at?->timestamp ?? 0 }}">
                                    {{ $inv->due_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($inv->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Lunas
                                        </span>
                                    @elseif ($inv->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            Menunggu Pembayaran
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ strtoupper($inv->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('billing.invoice.show', $inv) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-semibold transition">
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

