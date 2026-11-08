@extends('marketing.layouts.app')

@section('title', 'Laundiqo — Aplikasi Manajemen Laundry Online')
@section('meta_description', 'Kelola pesanan, proses cucian, pembayaran, dan laporan usaha laundry lebih mudah bersama Laundiqo.')

@section('content')
<section id="beranda" class="marketing-hero" aria-labelledby="hero-title">
    <div class="marketing-container grid items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
        <div class="marketing-reveal min-w-0">
            <span class="marketing-eyebrow">{{ \App\Models\Setting::getPlatform('platform_hero_badge', 'APLIKASI MANAJEMEN LAUNDRY ONLINE') }}</span>
            <h1 id="hero-title" class="mt-5 text-4xl font-black leading-tight tracking-tight text-[#0B1F3A] sm:text-5xl lg:text-6xl">
                @if (\App\Models\Setting::getPlatform('platform_hero_title'))
                    {{ \App\Models\Setting::getPlatform('platform_hero_title') }}
                @else
                    Kelola Laundry Lebih Mudah. <span class="text-[#087FB8]">Pantau Bisnis dari Mana Saja.</span>
                @endif
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-[#59708B]">{{ \App\Models\Setting::getPlatform('platform_hero_subtitle', 'Catat pesanan, pantau proses cucian, kelola pembayaran, dan lihat laporan usaha dalam satu aplikasi laundry online yang mudah digunakan.') }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="group marketing-button marketing-button-primary text-center">
                    <span>Mulai Gunakan Laundiqo</span>
                    <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
                <a href="#cara-kerja" class="marketing-button marketing-button-secondary text-center">Lihat Cara Kerja</a>
            </div>
            <p class="mt-5 text-sm text-[#59708B]">Dari cucian diterima sampai siap diambil, semua proses lebih tertata.</p>
        </div>

        {{-- Hero Preview: Menggunakan hasil screenshot asli sistem Laundiqo --}}
        <div class="dashboard-preview marketing-reveal marketing-reveal-delay overflow-hidden min-w-0"
             x-data="{ heroSlide: 'overview' }"
             aria-label="Tampilan dashboard Laundiqo"
             role="img">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#DBEAF3] bg-white px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5" aria-hidden="true">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    </div>
                    <span class="hidden sm:inline-block text-[11px] font-semibold text-slate-400 ml-1">app.laundiqo.id/dashboard</span>
                </div>

                <div class="inline-flex items-center rounded-lg bg-slate-100 p-0.5 text-[11px] font-bold">
                    <button type="button"
                            @click="heroSlide = 'overview'"
                            :class="heroSlide === 'overview' ? 'bg-white text-[#087FB8] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="rounded-md px-2.5 py-1 transition">
                        Ringkasan Bisnis
                    </button>
                    <button type="button"
                            @click="heroSlide = 'analytics'"
                            :class="heroSlide === 'analytics' ? 'bg-white text-[#087FB8] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="rounded-md px-2.5 py-1 transition">
                        Tren &amp; Analitik
                    </button>
                </div>
            </div>

            <div class="bg-white">
                <img x-show="heroSlide === 'overview'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-[0.98]"
                     x-transition:enter-end="opacity-100 scale-100"
                     src="{{ asset('images/showcase/dashboard-overview.png') }}"
                     alt="Tampilan Dashboard Utama Laundiqo"
                     class="w-full h-auto block">
                <img x-show="heroSlide === 'analytics'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-[0.98]"
                     x-transition:enter-end="opacity-100 scale-100"
                     src="{{ asset('images/showcase/dashboard-analytics.png') }}"
                     alt="Tampilan Grafik Tren Pendapatan dan Transaksi Terbaru Laundiqo"
                     class="w-full h-auto block">
            </div>
        </div>
    </div>
</section>

<section id="solusi" class="marketing-section bg-white" aria-labelledby="solution-title">
    <div class="marketing-container">
        <div class="mx-auto max-w-2xl text-center">
            <span class="marketing-eyebrow">MASALAH &amp; SOLUSI</span>
            <h2 id="solution-title" class="marketing-heading">Masih Mengelola Laundry dengan Cara Manual?</h2>
            <p class="marketing-lead">Pesanan yang menumpuk, status cucian yang sulit dipantau, dan laporan yang tersebar membuat operasional tidak efisien.</p>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-3">
            {{-- Card 1: Pencatatan Order --}}
            <article class="problem-card group marketing-reveal flex flex-col justify-between relative overflow-hidden hover:border-[#08C6D8]">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#087FB8] to-[#08C6D8] origin-left scale-x-0 transition-transform duration-500 group-hover:scale-x-100"></div>
                <div>
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div class="animate-float-slow inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#087FB8] to-[#08C6D8] text-white shadow-md shadow-[#087FB8]/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600 transition-colors duration-300 group-hover:bg-[#F0F7FF] group-hover:text-[#087FB8]">
                            Pencatatan Order
                        </span>
                    </div>

                    {{-- Masalah (Before) --}}
                    <div class="rounded-xl border border-rose-100 bg-rose-50/70 p-3.5 transition-opacity duration-300 group-hover:opacity-85">
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-500">Kendala Manual</span>
                                <p class="mt-0.5 text-xs font-semibold text-slate-700">Order dicatat di buku atau chat</p>
                            </div>
                        </div>
                    </div>

                    {{-- Panah Transformasi --}}
                    <div class="my-3 flex items-center gap-2 text-[#087FB8]">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#F0F7FF] text-[#087FB8] ring-1 ring-[#DBEAF3] transition-colors duration-300 group-hover:bg-[#087FB8] group-hover:text-white">
                            <svg class="w-5 h-5 animate-flow-down" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-l from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                    </div>

                    {{-- Solusi (After) --}}
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5 transition-all duration-300 group-hover:border-emerald-300 group-hover:bg-emerald-50/90 group-hover:shadow-sm">
                        <div class="flex items-start gap-2.5">
                            <span class="animate-check-pop mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-700">Solusi Laundiqo</span>
                                <h3 class="mt-0.5 text-base font-extrabold text-[#142B49]">Data order tersimpan lebih rapi dalam sistem</h3>
                                <p class="mt-1.5 text-xs leading-relaxed text-slate-600">Nota digital, nomor order otomatis, detail layanan, dan estimasi selesai tercatat terpusat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Card 2: Pelacakan Produksi & Rak --}}
            <article class="problem-card group marketing-reveal reveal-delay-1 flex flex-col justify-between relative overflow-hidden hover:border-[#08C6D8]">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#087FB8] to-[#08C6D8] origin-left scale-x-0 transition-transform duration-500 group-hover:scale-x-100"></div>
                <div>
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div class="animate-float-slow inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#087FB8] to-[#08C6D8] text-white shadow-md shadow-[#087FB8]/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6" style="animation-delay: 400ms">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600 transition-colors duration-300 group-hover:bg-[#F0F7FF] group-hover:text-[#087FB8]">
                            Produksi &amp; Lokasi Rak
                        </span>
                    </div>

                    {{-- Masalah (Before) --}}
                    <div class="rounded-xl border border-rose-100 bg-rose-50/70 p-3.5 transition-opacity duration-300 group-hover:opacity-85">
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-500">Kendala Manual</span>
                                <p class="mt-0.5 text-xs font-semibold text-slate-700">Staf sulit mengetahui posisi cucian</p>
                            </div>
                        </div>
                    </div>

                    {{-- Panah Transformasi --}}
                    <div class="my-3 flex items-center gap-2 text-[#087FB8]">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#F0F7FF] text-[#087FB8] ring-1 ring-[#DBEAF3] transition-colors duration-300 group-hover:bg-[#087FB8] group-hover:text-white">
                            <svg class="w-5 h-5 animate-flow-down" style="animation-delay: 300ms" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-l from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                    </div>

                    {{-- Solusi (After) --}}
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5 transition-all duration-300 group-hover:border-emerald-300 group-hover:bg-emerald-50/90 group-hover:shadow-sm">
                        <div class="flex items-start gap-2.5">
                            <span class="animate-check-pop mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white" style="animation-delay: 300ms">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-700">Solusi Laundiqo</span>
                                <h3 class="mt-0.5 text-base font-extrabold text-[#142B49]">Status proses dan rak bisa diperiksa</h3>
                                <p class="mt-1.5 text-xs leading-relaxed text-slate-600">Pantau 5 tahapan cucian (Diterima s/d Siap Diambil) beserta posisi rak penyimpanan secara akurat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Card 3: Pembayaran & Laporan Keuangan --}}
            <article class="problem-card group marketing-reveal reveal-delay-2 flex flex-col justify-between relative overflow-hidden hover:border-[#08C6D8]">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#087FB8] to-[#08C6D8] origin-left scale-x-0 transition-transform duration-500 group-hover:scale-x-100"></div>
                <div>
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div class="animate-float-slow inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#087FB8] to-[#08C6D8] text-white shadow-md shadow-[#087FB8]/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6" style="animation-delay: 800ms">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600 transition-colors duration-300 group-hover:bg-[#F0F7FF] group-hover:text-[#087FB8]">
                            Keuangan &amp; Laporan
                        </span>
                    </div>

                    {{-- Masalah (Before) --}}
                    <div class="rounded-xl border border-rose-100 bg-rose-50/70 p-3.5 transition-opacity duration-300 group-hover:opacity-85">
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-500">Kendala Manual</span>
                                <p class="mt-0.5 text-xs font-semibold text-slate-700">Pembayaran dan rekap terpisah</p>
                            </div>
                        </div>
                    </div>

                    {{-- Panah Transformasi --}}
                    <div class="my-3 flex items-center gap-2 text-[#087FB8]">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#F0F7FF] text-[#087FB8] ring-1 ring-[#DBEAF3] transition-colors duration-300 group-hover:bg-[#087FB8] group-hover:text-white">
                            <svg class="w-5 h-5 animate-flow-down" style="animation-delay: 600ms" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </span>
                        <div class="h-px flex-1 bg-gradient-to-l from-transparent via-[#DBEAF3] to-[#08C6D8]/50"></div>
                    </div>

                    {{-- Solusi (After) --}}
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5 transition-all duration-300 group-hover:border-emerald-300 group-hover:bg-emerald-50/90 group-hover:shadow-sm">
                        <div class="flex items-start gap-2.5">
                            <span class="animate-check-pop mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white" style="animation-delay: 600ms">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-700">Solusi Laundiqo</span>
                                <h3 class="mt-0.5 text-base font-extrabold text-[#142B49]">Riwayat pembayaran serta laporan tersedia</h3>
                                <p class="mt-1.5 text-xs leading-relaxed text-slate-600">Pisahkan kas tunai di laci vs transfer, pantau piutang DP, dan unduh laporan PDF/Excel kapan saja.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<section id="fitur" class="marketing-section bg-[#F5FAFF]" aria-labelledby="feature-title">
    <div class="marketing-container">
        <div class="max-w-2xl">
            <span class="marketing-eyebrow">FITUR UTAMA</span>
            <h2 id="feature-title" class="marketing-heading">Semua yang Dibutuhkan untuk Mengelola Laundry</h2>
            <p class="marketing-lead">Satu tempat untuk kasir, operator, dan pemilik usaha.</p>
        </div>

        @php
            $features = [
                [
                    'num' => '01',
                    'title' => 'Manajemen Pesanan',
                    'desc' => 'Catat order dan layanan dengan lebih praktis.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                ],
                [
                    'num' => '02',
                    'title' => 'Tracking Cucian',
                    'desc' => 'Pelanggan dapat mengecek status pesanan melalui nomor order.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                ],
                [
                    'num' => '03',
                    'title' => 'Alur Produksi',
                    'desc' => 'Pantau tahap diterima, dicuci, dikeringkan, disetrika, hingga siap diambil.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                ],
                [
                    'num' => '04',
                    'title' => 'Pembayaran Fleksibel',
                    'desc' => 'Catat pembayaran penuh, DP, dan pelunasan sesuai fitur yang aktif.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
                ],
                [
                    'num' => '05',
                    'title' => 'Manajemen Rak',
                    'desc' => 'Ketahui tempat penyimpanan cucian yang telah diproses.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
                ],
                [
                    'num' => '06',
                    'title' => 'Laporan Usaha',
                    'desc' => 'Lihat rekap order serta laporan PDF atau Excel yang tersedia.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                ],
            ];
        @endphp

        <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $idx => $feature)
                <article class="feature-card group marketing-reveal reveal-delay-{{ min($idx, 5) }} hover:border-[#08C6D8]">
                    <div class="mb-5 flex items-center justify-between">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-[#087FB8] ring-1 ring-blue-100 transition-all duration-300 group-hover:bg-gradient-to-br group-hover:from-[#087FB8] group-hover:to-[#08C6D8] group-hover:text-white group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:shadow-[#087FB8]/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                {!! $feature['icon'] !!}
                            </svg>
                        </span>
                        <span class="text-xs font-black tracking-widest text-slate-300 transition-colors duration-300 group-hover:text-[#087FB8]">{{ $feature['num'] }}</span>
                    </div>
                    <h3 class="transition-colors duration-300 group-hover:text-[#087FB8]">{{ $feature['title'] }}</h3>
                    <p>{{ $feature['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section id="cara-kerja" class="marketing-section bg-white" x-data="{ activeStep: 0 }" aria-labelledby="workflow-title"><div class="marketing-container"><div class="mx-auto max-w-3xl text-center"><span class="marketing-eyebrow">CARA KERJA</span><h2 id="workflow-title" class="marketing-heading">Dari Cucian Diterima Sampai Selesai, Semua Lebih Terpantau.</h2><p class="marketing-lead">Simulasi alur edukasi produk, bukan data transaksi langsung.</p></div><div class="mt-10 pb-2" role="tablist" aria-label="Tahapan proses laundry"><div class="mx-auto grid grid-cols-2 sm:grid-cols-3 lg:flex lg:flex-wrap justify-center gap-2">@foreach (['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika', 'Siap Diambil', 'Selesai'] as $index => $label)<button type="button" role="tab" :aria-selected="activeStep === {{ $index }} ? 'true' : 'false'" @click="activeStep = {{ $index }}" class="workflow-tab text-center justify-center" :class="activeStep === {{ $index }} ? 'workflow-tab-active' : ''">{{ $index + 1 }}. {{ $label }}</button>@endforeach</div></div><div class="mx-auto mt-8 max-w-2xl rounded-2xl border border-[#DBEAF3] bg-[#F5FAFF] p-7 text-center transition-all duration-300">@foreach ([['Diterima', 'Kasir membuat pesanan.'], ['Dicuci', 'Operator memperbarui proses pencucian.'], ['Dikeringkan', 'Cucian menuju tahap pengeringan.'], ['Disetrika', 'Pakaian dirapikan.'], ['Siap Diambil', 'Barang diletakkan di rak dan siap diserahkan.'], ['Selesai', 'Kasir menyelesaikan penyerahan dan pembayaran.']] as $index => $step)<div x-show="activeStep === {{ $index }}" x-cloak role="tabpanel"><span class="feature-icon">{{ $index + 1 }}</span><h3 class="text-xl font-black text-[#0B1F3A]">{{ $step[0] }}</h3><p class="mt-3 text-[#59708B]">{{ $step[1] }}</p><div class="mx-auto mt-6 h-2 max-w-xs overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-gradient-to-r from-[#087FB8] to-[#08C6D8]" style="width: {{ (($index + 1) / 6) * 100 }}%"></div></div></div>@endforeach<div class="mt-7 flex justify-between gap-3"><button type="button" class="marketing-button marketing-button-secondary" :disabled="activeStep === 0" @click="activeStep--">Sebelumnya</button><button type="button" class="marketing-button marketing-button-primary" :disabled="activeStep === 5" @click="activeStep++">Selanjutnya</button></div></div></div></section>

<section id="tampilan" class="marketing-section bg-[#F5FAFF]" x-data="{ activeRole: 'admin' }" aria-labelledby="showcase-title">
    <div class="marketing-container">
        <div class="mx-auto max-w-2xl text-center">
            <span class="marketing-eyebrow">TAMPILAN APLIKASI</span>
            <h2 id="showcase-title" class="marketing-heading">Kenali Laundiqo dari Setiap Peran</h2>
            <p class="marketing-lead">Tampilan yang sesuai dengan dashboard asli pemilik usaha, kasir, operator produksi, hingga halaman pelacakan pelanggan.</p>
        </div>

        <div class="mt-10 grid grid-cols-2 sm:flex sm:flex-wrap border-b border-[#DBEAF3]" role="tablist" aria-label="Peran aplikasi">
            @foreach (['admin' => 'Dashboard Admin', 'kasir' => 'Dashboard Kasir', 'operator' => 'Dashboard Operator', 'tracking' => 'Tracking Pelanggan'] as $role => $label)
                <button type="button" role="tab" :aria-selected="activeRole === '{{ $role }}' ? 'true' : 'false'" @click="activeRole = '{{ $role }}'" class="showcase-tab text-center justify-center" :class="activeRole === '{{ $role }}' ? 'showcase-tab-active' : ''">{{ $label }}</button>
            @endforeach
        </div>

        <div class="showcase-panel mt-8" role="region" aria-live="polite">
            {{-- 1. Preview Admin (Menggunakan screenshot asli sistem) --}}
            <div x-show="activeRole === 'admin'" x-cloak class="grid items-center gap-8 lg:grid-cols-12" x-data="{ adminView: 'overview' }">
                <div class="lg:col-span-7 min-w-0 rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-sm">
                    <div class="bg-slate-50 border-b border-gray-200 px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="font-bold text-gray-800">Dashboard Admin &mdash; Overview &amp; Analitik</span>
                        <div class="inline-flex rounded-lg bg-gray-200/70 p-0.5 text-[11px] font-bold">
                            <button type="button"
                                    @click="adminView = 'overview'"
                                    :class="adminView === 'overview' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600'"
                                    class="rounded-md px-2.5 py-0.5 transition">
                                Ringkasan Atas
                            </button>
                            <button type="button"
                                    @click="adminView = 'analytics'"
                                    :class="adminView === 'analytics' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600'"
                                    class="rounded-md px-2.5 py-0.5 transition">
                                Grafik &amp; Order
                            </button>
                        </div>
                    </div>
                    <img x-show="adminView === 'overview'"
                         src="{{ asset('images/showcase/dashboard-overview.png') }}"
                         alt="Dashboard Admin Laundiqo - Ringkasan Bisnis"
                         class="w-full h-auto block">
                    <img x-show="adminView === 'analytics'"
                         x-cloak
                         src="{{ asset('images/showcase/dashboard-analytics.png') }}"
                         alt="Dashboard Admin Laundiqo - Tren Pendapatan dan Layanan Terlaris"
                         class="w-full h-auto block">
                </div>
                <div class="lg:col-span-5 min-w-0">
                    <h3 class="text-2xl font-black text-[#0B1F3A]">Dashboard Admin: Overview Bisnis, Arus Kas &amp; Tren Omzet</h3>
                    <p class="mt-4 leading-7 text-[#59708B]">Menampilkan metrik finansial utama (Pendapatan Hari Ini, Kas Tunai di Laci vs Transfer Rekening, Total Piutang Belum Lunas), Ringkasan Status Produksi 5 tahap, Grafik Tren Pendapatan 7 Hari Terakhir, serta Top Layanan Terlaris.</p>
                </div>
            </div>

            {{-- 2. Preview Kasir (Sesuai dashboard.blade.php @else) --}}
            <div x-show="activeRole === 'kasir'" x-cloak class="grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-7 min-w-0 rounded-2xl border border-gray-200 bg-slate-50 overflow-hidden shadow-sm">
                    @if (file_exists(public_path('images/showcase/kasir.png')))
                        <img src="{{ asset('images/showcase/kasir.png') }}" alt="Dashboard Kasir Laundiqo" class="w-full h-auto block">
                    @else
                        <div class="bg-white border-b border-gray-200 px-4 py-2.5 flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-800">Dashboard &mdash; Ringkasan operasional hari ini</span>
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold text-[10px]">Role: Kasir</span>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1.5">Total Pendapatan Hari Ini</p>
                                        <p class="text-lg font-bold text-gray-900">Rp 850.000</p>
                                        <p class="text-[10px] text-gray-400 mt-1">Order lunas yang selesai hari ini</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center text-green-500 font-bold text-xs">Rp</div>
                                </div>
                                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1.5">Order Aktif</p>
                                        <p class="text-lg font-bold text-gray-900">18</p>
                                        <p class="text-[10px] text-gray-400 mt-1">Order yang sedang diproses</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 font-bold text-xs">#</div>
                                </div>
                                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1.5">Siap Diambil</p>
                                        <p class="text-lg font-bold text-gray-900">8</p>
                                        <p class="text-[10px] text-gray-400 mt-1">Menunggu pengambilan pelanggan</p>
                                    </div>
                                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 font-bold text-xs">&check;</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="lg:col-span-5 min-w-0">
                    <h3 class="text-2xl font-black text-[#0B1F3A]">Dashboard Kasir: Ringkas & Langsung ke Daftar Order</h3>
                    <p class="mt-4 leading-7 text-[#59708B]">Menampilkan 3 kartu utama Kasir (Total Pendapatan Hari Ini, Order Aktif, dan Siap Diambil) yang masing-masing tertaut langsung ke halaman pengelolaan pesanan, pembayaran, cetak struk, dan kirim nota WhatsApp.</p>
                </div>
            </div>

            {{-- 3. Preview Operator (Sesuai dashboard.blade.php @if($is_operator)) --}}
            <div x-show="activeRole === 'operator'" x-cloak class="grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-7 min-w-0 rounded-2xl border border-gray-200 bg-slate-50 overflow-hidden shadow-sm">
                    @if (file_exists(public_path('images/showcase/operator.png')))
                        <img src="{{ asset('images/showcase/operator.png') }}" alt="Dashboard Operator Laundiqo" class="w-full h-auto block">
                    @else
                        <div class="bg-white border-b border-gray-200 px-4 py-2.5 flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-800">Dashboard &mdash; Produktivitas laundry</span>
                            <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold text-[10px]">Role: Operator</span>
                        </div>
                        <div class="p-4 space-y-3">
                            {{-- 5 Kartu Status Produksi Operator --}}
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-2.5">
                                    <p class="text-base font-bold text-blue-600">4</p>
                                    <p class="text-[10px] text-blue-600 mt-0.5">Diterima</p>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-2.5">
                                    <p class="text-base font-bold text-yellow-600">6</p>
                                    <p class="text-[10px] text-yellow-600 mt-0.5">Dicuci</p>
                                </div>
                                <div class="bg-orange-50 border border-orange-200 rounded-xl p-2.5">
                                    <p class="text-base font-bold text-orange-600">3</p>
                                    <p class="text-[10px] text-orange-600 mt-0.5">Dikeringkan</p>
                                </div>
                                <div class="bg-purple-50 border border-purple-200 rounded-xl p-2.5">
                                    <p class="text-base font-bold text-purple-600">5</p>
                                    <p class="text-[10px] text-purple-600 mt-0.5">Disetrika</p>
                                </div>
                                <div class="col-span-2 sm:col-span-1 bg-green-50 border border-green-200 rounded-xl p-2.5">
                                    <p class="text-base font-bold text-green-600">8</p>
                                    <p class="text-[10px] text-green-600 mt-0.5">Siap Diambil</p>
                                </div>
                            </div>

                            {{-- Banner Biru Total Order Aktif --}}
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] opacity-90">Total Order Aktif</p>
                                    <p class="text-2xl font-bold">26</p>
                                    <p class="text-[10px] opacity-75">Order yang sedang diproses</p>
                                </div>
                            </div>

                            {{-- Quick Actions Operator --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                <div class="bg-white rounded-xl border border-gray-200 p-3">
                                    <p class="font-bold text-gray-900">Lihat Semua Order</p>
                                    <p class="text-[10px] text-gray-500">Kelola pesanan masuk</p>
                                </div>
                                <div class="bg-white rounded-xl border border-gray-200 p-3">
                                    <p class="font-bold text-gray-900">Kelola Rak</p>
                                    <p class="text-[10px] text-gray-500">Penempatan barang</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="lg:col-span-5 min-w-0">
                    <h3 class="text-2xl font-black text-[#0B1F3A]">Dashboard Operator: Fokus Antrean Produksi & Rak</h3>
                    <p class="mt-4 leading-7 text-[#59708B]">Menampilkan 5 kartu filter tahap produksi berwarna (Diterima, Dicuci, Dikeringkan, Disetrika, Siap Diambil), banner biru Total Order Aktif, serta pintasan Lihat Semua Order dan Kelola Rak persis seperti dashboard Operator.</p>
                </div>
            </div>

            {{-- 4. Preview Tracking --}}
            <div x-show="activeRole === 'tracking'" x-cloak class="grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-7 min-w-0 rounded-2xl border border-gray-200 bg-slate-50 overflow-hidden shadow-sm">
                    @if (file_exists(public_path('images/showcase/tracking.png')))
                        <img src="{{ asset('images/showcase/tracking.png') }}" alt="Halaman Tracking Pelanggan Laundiqo" class="w-full h-auto block">
                    @else
                        <div class="p-5 bg-white space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Nomor Order</p>
                                    <p class="font-mono text-sm font-bold text-gray-900">ORD-20261102-0001</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-bold text-[10px]">SIAP DIAMBIL</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5 text-center text-[10px] font-semibold">
                                <div class="p-2 rounded-lg bg-blue-600 text-white">1. Diterima</div>
                                <div class="p-2 rounded-lg bg-blue-600 text-white">2. Dicuci</div>
                                <div class="p-2 rounded-lg bg-blue-600 text-white">3. Dikeringkan</div>
                                <div class="p-2 rounded-lg bg-blue-600 text-white">4. Disetrika</div>
                                <div class="col-span-2 sm:col-span-1 p-2 rounded-lg bg-emerald-600 text-white">5. Siap Diambil</div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="lg:col-span-5 min-w-0">
                    <h3 class="text-2xl font-black text-[#0B1F3A]">Lacak Status Cucian Mandiri Tanpa Login</h3>
                    <p class="mt-4 leading-7 text-[#59708B]">Pelanggan cukup membuka link dari nota WhatsApp atau memasukkan nomor order di halaman Lacak Pesanan untuk memantau tahap cucian secara langsung.</p>
                    <div class="mt-5">
                        <a href="{{ route('tracking.index') }}" class="group marketing-button marketing-button-secondary inline-flex">
                            <span>Buka Halaman Tracking</span>
                            <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="mulai" class="marketing-section bg-white" aria-labelledby="start-title"><div class="marketing-container"><div class="mx-auto max-w-2xl text-center"><span class="marketing-eyebrow">MULAI MENGGUNAKAN</span><h2 id="start-title" class="marketing-heading">Mulai Kelola Laundry dalam 3 Langkah</h2></div><div class="mt-12 grid gap-5 md:grid-cols-3">@foreach ([['01', 'Daftarkan usaha', 'Buat akun dan profil bisnis melalui registrasi yang sudah tersedia.'], ['02', 'Atur layanan dan staf', 'Sesuaikan layanan serta pengguna sesuai izin akun.'], ['03', 'Mulai catat pesanan', 'Kasir dan operator menggunakan dashboard Laundiqo.']] as $step)<article class="role-card text-center"><span class="feature-icon">{{ $step[0] }}</span><h3>{{ $step[1] }}</h3><p>{{ $step[2] }}</p></article>@endforeach</div><div class="mt-8 text-center"><a href="{{ route('register') }}" class="group marketing-button marketing-button-primary"><span>Daftar Sekarang</span><svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg></a></div></div></section>

<section id="harga" class="marketing-section bg-[#0B1F3A] text-white" x-data="{ billing: 'monthly' }" aria-labelledby="pricing-title"><div class="marketing-container"><div class="mx-auto max-w-2xl text-center"><span class="marketing-eyebrow text-[#08C6D8]">HARGA TRANSPARAN</span><h2 id="pricing-title" class="mt-4 text-3xl font-black sm:text-4xl">Pilih Paket yang Sesuai dengan Usaha Laundrymu</h2><p class="mt-4 leading-7 text-slate-300">Pilih langganan yang jelas manfaatnya, sesuai kebutuhan operasional.</p><div class="mt-7 inline-flex rounded-xl bg-white/10 p-1" role="group" aria-label="Periode tagihan"><button type="button" class="pricing-toggle" :class="billing === 'monthly' ? 'pricing-toggle-active' : ''" @click="billing = 'monthly'">Bulanan</button><button type="button" class="pricing-toggle" :class="billing === 'yearly' ? 'pricing-toggle-active' : ''" @click="billing = 'yearly'">Tahunan</button></div></div><div class="mx-auto mt-12 grid max-w-6xl gap-5 md:grid-cols-2 lg:grid-cols-3">@forelse ($plans as $plan)<article class="pricing-card {{ $plan->slug === 'business' ? 'pricing-card-featured' : '' }}"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-bold uppercase tracking-wider text-[#087FB8]">{{ $plan->name }}</p><p class="mt-2 text-3xl font-black text-[#0B1F3A]"><span x-text="billing === 'monthly' ? 'Rp{{ number_format($plan->price_monthly, 0, ',', '.') }}' : 'Rp{{ number_format($plan->price_yearly, 0, ',', '.') }}'"></span><span class="text-sm font-medium text-slate-500" x-text="billing === 'monthly' ? '/bulan' : '/tahun'"></span></p></div>@if ($plan->slug === 'business')<span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-[#075C91]">Rekomendasi</span>@endif</div><p class="mt-4 text-sm leading-6 text-slate-600">{{ $plan->description }}</p><ul class="mt-6 space-y-3 text-sm text-slate-600">@foreach ($plan->features ?? [] as $feature)<li>&check; {{ $feature }}</li>@endforeach</ul><a href="{{ route('register', ['plan' => $plan->slug]) }}" :href="'{{ route('register') }}?plan={{ $plan->slug }}&cycle=' + billing" class="group mt-7 w-full marketing-button marketing-button-primary text-center"><span>Pilih Paket {{ $plan->name }}</span><svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg></a></article>@empty<div class="pricing-card md:col-span-2 lg:col-span-3 text-center"><h3 class="text-xl font-bold text-[#0B1F3A]">Paket tersedia melalui tim Laundiqo</h3><p class="mt-2 text-slate-600">Hubungi kami untuk informasi paket dan aktivasi layanan.</p><a href="{{ route('marketing.contact') }}" class="mt-6 inline-flex marketing-button marketing-button-primary">Hubungi Kami</a></div>@endforelse</div>@if ($trialDays)<p class="mt-8 text-center text-sm text-slate-300">Pendaftaran menyertakan trial {{ $trialDays }} hari gratis sebelum Anda memutuskan berlangganan.</p>@endif</div></section>

<section id="faq" class="marketing-section bg-white" aria-labelledby="faq-title"><div class="marketing-container max-w-3xl"><div class="text-center"><span class="marketing-eyebrow">FAQ</span><h2 id="faq-title" class="marketing-heading">Pertanyaan yang Sering Ditanyakan</h2></div><div class="mt-10 divide-y divide-[#DBEAF3] border-y border-[#DBEAF3]">@foreach (\App\Models\Setting::getPlatformFaqs() as $faq)<details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-bold text-[#0B1F3A]">{{ $faq['question'] ?? $faq[0] ?? '' }}<span class="text-2xl font-normal text-[#087FB8] group-open:rotate-45" aria-hidden="true">+</span></summary><p class="mt-3 max-w-2xl text-sm leading-6 text-[#59708B]">{{ $faq['answer'] ?? $faq[1] ?? '' }}</p></details>@endforeach</div></div></section>

<section class="marketing-section"><div class="marketing-container rounded-3xl bg-gradient-to-br from-[#075C91] via-[#087FB8] to-[#08C6D8] px-6 py-14 text-center text-white sm:px-12"><h2 class="text-3xl font-black">Siap Membuat Operasional Laundry Lebih Tertata?</h2><p class="mx-auto mt-4 max-w-xl text-blue-50">Mulai gunakan Laundiqo untuk mengelola pesanan, proses cucian, dan laporan usaha dalam satu tempat.</p><div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row"><a href="{{ route('register') }}" class="group marketing-button bg-white text-[#075C91] hover:bg-blue-50"><span>Mulai Sekarang</span><svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg></a><a href="#fitur" class="marketing-button border border-white/40 text-white hover:bg-white/10">Lihat Fitur</a></div></div></section>
@endsection
