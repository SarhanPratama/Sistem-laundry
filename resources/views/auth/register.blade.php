<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Usaha Laundry — Laundiqo SaaS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $plansData = isset($plans) && $plans->isNotEmpty()
        ? $plans->mapWithKeys(fn($p) => [
            $p->slug => [
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description ?? 'Paket operasional laundry modern.',
                'price_monthly' => (float) $p->price_monthly,
                'price_yearly' => (float) $p->price_yearly,
                'max_outlets' => (int) $p->max_outlets,
                'max_staff' => (int) $p->max_staff,
                'features' => is_array($p->features) ? $p->features : [],
            ]
        ])->toArray()
        : [
            'starter' => [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Cocok untuk usaha laundry rintisan 1 outlet.',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'max_outlets' => 1,
                'max_staff' => 3,
                'features' => ['Order & Nota Digital Unlimited', 'Manajemen Rak & Status Cucian', 'Laporan Harian & Bulanan'],
            ],
            'business' => [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Pilihan terpopuler untuk laundry berkembang dengan tim kasir & operator.',
                'price_monthly' => 199000,
                'price_yearly' => 1990000,
                'max_outlets' => 2,
                'max_staff' => 10,
                'features' => ['Semua Fitur Starter', 'Multi-Role Kasir & Operator', 'Cetak Struk Thermal & WhatsApp', 'Export Laporan Excel & PDF'],
            ],
            'pro' => [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Kapasitas maksimal untuk jaringan laundry skala besar.',
                'price_monthly' => 349000,
                'price_yearly' => 3490000,
                'max_outlets' => 5,
                'max_staff' => 999,
                'features' => ['Semua Fitur Business', 'Hingga 5 Cabang & Staf Unlimited', 'Prioritas Dukungan Teknis', 'Audit Aktivitas Lengkap'],
            ],
        ];

    $defaultPlanSlug = old('plan', $selectedPlan ?? 'business');
    if (!array_key_exists($defaultPlanSlug, $plansData)) {
        $defaultPlanSlug = array_key_first($plansData);
    }
    $defaultCycle = old('billing_cycle', $selectedCycle ?? 'monthly');
    $defaultMode = old('subscription_mode', request()->query('mode', request()->has('plan') ? 'direct_pay' : 'trial'));
@endphp
<body class="min-h-screen bg-[#F6FAFD] text-[#0F172A] flex items-center justify-center p-4 sm:p-6 lg:p-10 antialiased selection:bg-[#1E88E5] selection:text-white"
      x-data="{
          plans: {{ Js::from($plansData) }},
          selectedPlan: '{{ $defaultPlanSlug }}',
          billingCycle: '{{ $defaultCycle }}',
          subscriptionMode: '{{ $defaultMode }}',
          showPass: false,
          password: '',
          passwordConfirm: '',
          get passRules() {
              return [
                  { label: '8+ karakter', valid: this.password.length >= 8 },
                  { label: '1 huruf besar', valid: /[A-Z]/.test(this.password) },
                  { label: '1 angka', valid: /[0-9]/.test(this.password) },
                  { label: '1 simbol', valid: /[^A-Za-z0-9\s]/.test(this.password) },
              ];
          },
          get passScore() {
              return this.passRules.filter(r => r.valid).length;
          },
          get currentPlan() {
              return this.plans[this.selectedPlan] || Object.values(this.plans)[0];
          },
          get currentPrice() {
              if (!this.currentPlan) return 0;
              return this.billingCycle === 'yearly' ? this.currentPlan.price_yearly : this.currentPlan.price_monthly;
          },
          formatRupiah(value) {
              return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
          }
      }">

    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-xl shadow-sky-900/5 border border-[#DBEAF3] overflow-hidden grid lg:grid-cols-12">

        {{-- Left Column: Interactive Brand Showcase & Plan Summary --}}
        <div class="lg:col-span-5 bg-[#0B1F3A] text-white p-7 sm:p-9 lg:p-10 flex flex-col justify-between relative overflow-hidden">
            {{-- Subtle Dot Pattern --}}
            <div class="absolute inset-0 opacity-10 pointer-events-none"
                 style="background-image: radial-gradient(#38BDF8 1px, transparent 1px); background-size: 20px 20px;"></div>
            {{-- Soft Glow --}}
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[#1E88E5]/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                {{-- Top Bar: Brand & Back to Home --}}
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('marketing.home') }}" class="inline-flex items-center gap-3 group">
                        <img src="{{ asset('logo.png') }}" alt="Laundiqo" class="h-10 w-auto rounded-lg bg-white p-1 shadow-sm">
                        <div>
                            <span class="font-display font-bold text-lg tracking-tight text-white block leading-none">Laundiqo</span>
                            <span class="text-[11px] font-medium text-sky-300 tracking-wide">Laundry SaaS Platform</span>
                        </div>
                    </a>
                    <a href="{{ route('marketing.home') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Beranda
                    </a>
                </div>

                {{-- Headline --}}
                <div class="mt-8">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-sky-500/15 text-sky-300 border border-sky-400/25">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Setup Instan Kurang dari 1 Menit
                    </span>
                    <h1 class="mt-3 font-display text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug text-white">
                        Kelola Outlet Laundry Lebih Rapi & Menguntungkan.
                    </h1>
                    <p class="mt-2.5 text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Begitu daftar, sistem otomatis menyiapkan kategori, layanan awal, dan rak penyimpanan agar kasir bisa langsung mencetak nota pertama.
                    </p>
                </div>

                {{-- Dynamic Live Plan Summary Card --}}
                <div class="mt-6 rounded-2xl bg-white/[0.07] backdrop-blur-md border border-white/15 p-5 shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-300 block">Ringkasan Paket Pilihan</span>
                            <div class="flex items-center gap-2 mt-1">
                                <h2 class="font-display text-xl font-extrabold text-white" x-text="'Paket ' + currentPlan.name">Paket Business</h2>
                                <span x-show="billingCycle === 'yearly'" x-cloak
                                      class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                    Hemat 2 Bulan
                                </span>
                            </div>
                        </div>

                        {{-- Dynamic Activation Mode Badge --}}
                        <span x-show="subscriptionMode === 'trial'"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Trial 14 Hari
                        </span>
                        <span x-show="subscriptionMode === 'direct_pay'" x-cloak
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Aktivasi Instan
                        </span>
                    </div>

                    <p class="mt-1.5 text-xs text-slate-300 leading-relaxed" x-text="currentPlan.description"></p>

                    {{-- Price Breakdown --}}
                    <div class="mt-4 pt-4 border-t border-white/10 flex items-baseline justify-between gap-2">
                        <div>
                            <span class="text-[11px] text-slate-400 block" x-text="subscriptionMode === 'trial' ? 'Tagihan Hari Ini:' : 'Total Pembayaran:'">Tagihan Hari Ini:</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span x-show="subscriptionMode === 'trial'" class="font-display text-2xl font-extrabold text-emerald-400">Rp 0</span>
                                <span x-show="subscriptionMode === 'direct_pay'" x-cloak class="font-display text-2xl font-extrabold text-white" x-text="formatRupiah(currentPrice)"></span>
                                <span class="text-xs text-slate-300" x-text="subscriptionMode === 'trial' ? '(Gratis 14 hari pertama)' : (billingCycle === 'yearly' ? '/ tahun' : '/ bulan')"></span>
                            </div>
                        </div>
                        <div class="text-right" x-show="subscriptionMode === 'trial'">
                            <span class="text-[10px] text-slate-400 block">Tarif normal setelah trial</span>
                            <span class="text-xs font-semibold text-slate-200" x-text="formatRupiah(currentPrice) + (billingCycle === 'yearly' ? '/thn' : '/bln')"></span>
                        </div>
                    </div>

                    {{-- Quota Pills --}}
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-white/5 border border-white/10 px-3 py-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-semibold text-slate-200" x-text="'Hingga ' + currentPlan.max_outlets + ' Outlet'"></span>
                        </div>
                        <div class="rounded-xl bg-white/5 border border-white/10 px-3 py-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-semibold text-slate-200" x-text="currentPlan.max_staff >= 100 ? 'Staf Unlimited' : ('Maks. ' + currentPlan.max_staff + ' Akun Staf')"></span>
                        </div>
                    </div>

                    {{-- Feature Checklist --}}
                    <ul class="mt-3.5 space-y-1.5 border-t border-white/10 pt-3">
                        <template x-for="(feature, idx) in (currentPlan.features || []).slice(0, 4)" :key="idx">
                            <li class="flex items-center gap-2 text-xs text-slate-200">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="feature"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- What You Get Immediately --}}
                <div class="mt-6 space-y-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Yang Langsung Siap Begitu Daftar:</p>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">Data Awal Otomatis Dibuatkan</p>
                            <p class="text-[11px] text-slate-400">Kategori Kiloan & Satuan, sampel layanan, serta Rak A1–A2 langsung tersedia.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-sky-500/15 border border-sky-400/30 flex items-center justify-center text-sky-300 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">Pembayaran Otomatis & Terenkripsi</p>
                            <p class="text-[11px] text-slate-400">Mendukung QRIS & Virtual Account otomatis via Payment Gateway tanpa unggah bukti manual.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Guarantee --}}
            <div class="relative z-10 mt-8 pt-4 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                <span>Isolasi Data Multi-Tenant Aman</span>
                <span class="text-sky-300 font-semibold">Bebas Upgrade Kapan Saja</span>
            </div>
        </div>

        {{-- Right Column: Registration Form --}}
        <div class="lg:col-span-7 p-6 sm:p-9 lg:p-11 flex flex-col justify-center bg-white">
            <div class="max-w-xl mx-auto w-full">

                {{-- Form Header --}}
                <div class="flex flex-wrap items-center justify-between gap-2 pb-5 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#1E88E5]">Registrasi Tenant Baru</span>
                        <h2 class="font-display text-2xl font-extrabold text-[#0F172A] tracking-tight mt-0.5">
                            Daftarkan Usaha Laundry Anda
                        </h2>
                    </div>
                    <div class="text-xs text-slate-500">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-bold text-[#1E88E5] hover:text-[#1565C0] underline underline-offset-4">
                            Masuk di sini
                        </a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-sm text-rose-700 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-bold text-rose-800 text-xs uppercase tracking-wider">Mohon Periksa Kembali Input Anda</p>
                            <ul class="mt-1 list-disc list-inside space-y-0.5 text-xs text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-6">
                    @csrf

                    {{-- SECTION 1: PROFIL USAHA & PAKET --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3.5">
                            <span class="w-6 h-6 rounded-full bg-[#EAF6FF] text-[#1E88E5] font-display text-xs font-extrabold flex items-center justify-center border border-[#DBEAF3]">1</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Profil Usaha & Pilihan Paket</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Nama Usaha --}}
                            <div>
                                <label for="laundry_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nama Usaha Laundry <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="laundry_name" id="laundry_name" required value="{{ old('laundry_name') }}"
                                           placeholder="Contoh: Berkah Laundry Express"
                                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('laundry_name') border-rose-400 bg-rose-50/40 @enderror">
                                </div>
                            </div>

                            {{-- Alamat Usaha --}}
                            <div>
                                <label for="laundry_address" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Alamat / Kota Outlet
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="laundry_address" id="laundry_address" value="{{ old('laundry_address') }}"
                                           placeholder="Contoh: Jl. Melati No. 45, Bandung"
                                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition">
                                </div>
                            </div>
                        </div>

                        @if (isset($plans) && $plans->isNotEmpty())
                            {{-- Plan & Cycle Interactive Picker --}}
                            <div class="mt-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <label for="plan" class="block text-xs font-semibold text-slate-700">
                                        Pilih Paket Langganan
                                    </label>

                                    {{-- Billing Cycle Pill Toggle --}}
                                    <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200 text-xs">
                                        <button type="button"
                                                @click="billingCycle = 'monthly'"
                                                :class="billingCycle === 'monthly' ? 'bg-white text-[#0F172A] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                                class="px-2.5 py-1 rounded-md transition text-[11px]">
                                            Bulanan
                                        </button>
                                        <button type="button"
                                                @click="billingCycle = 'yearly'"
                                                :class="billingCycle === 'yearly' ? 'bg-white text-[#0F172A] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                                class="px-2.5 py-1 rounded-md transition text-[11px] flex items-center gap-1">
                                            <span>Tahunan</span>
                                            <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold">-17%</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="billing_cycle" :value="billingCycle">
                                </div>

                                {{-- Interactive Plan Cards --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    @foreach ($plans as $planOption)
                                        <label @click="selectedPlan = '{{ $planOption->slug }}'"
                                               :class="selectedPlan === '{{ $planOption->slug }}'
                                                   ? 'border-[#1E88E5] bg-[#EAF6FF]/60 ring-2 ring-[#1E88E5]/20'
                                                   : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'"
                                               class="cursor-pointer rounded-xl border p-3 transition relative flex flex-col justify-between">
                                            <input type="radio" name="plan" value="{{ $planOption->slug }}" x-model="selectedPlan" class="sr-only">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-display text-xs font-extrabold text-slate-900">{{ $planOption->name }}</span>
                                                @if ($planOption->slug === 'business')
                                                    <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-[#1E88E5] text-white">Populer</span>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <span class="font-display text-sm font-extrabold text-[#0F172A]"
                                                      x-text="billingCycle === 'yearly' ? 'Rp {{ number_format($planOption->price_yearly, 0, ',', '.') }}' : 'Rp {{ number_format($planOption->price_monthly, 0, ',', '.') }}'">
                                                    Rp {{ number_format($planOption->price_monthly, 0, ',', '.') }}
                                                </span>
                                                <span class="text-[10px] text-slate-500" x-text="billingCycle === 'yearly' ? '/thn' : '/bln'">/bln</span>
                                            </div>
                                            <div class="mt-1.5 text-[11px] text-slate-500 flex items-center gap-1.5">
                                                <span>{{ $planOption->max_outlets }} Outlet</span>
                                                <span>•</span>
                                                <span>{{ $planOption->max_staff >= 100 ? 'Unlimited Staf' : $planOption->max_staff . ' Staf' }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- SECTION 2: METODE AKTIVASI --}}
                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-slate-700 mb-2">
                                    Cara Memulai Langganan
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {{-- Option 1: Trial 14 Days --}}
                                    <label @click="subscriptionMode = 'trial'"
                                           :class="subscriptionMode === 'trial'
                                               ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20'
                                               : 'border-slate-200 bg-white hover:border-slate-300'"
                                           class="cursor-pointer rounded-xl border p-3.5 flex items-start gap-3 transition">
                                        <input type="radio" name="subscription_mode" value="trial" x-model="subscriptionMode" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-xs font-bold text-slate-900">Coba Gratis 14 Hari</span>
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-700">Rp 0</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                Langsung masuk ke dashboard sekarang tanpa kartu kredit atau pembayaran di awal.
                                            </p>
                                        </div>
                                    </label>

                                    {{-- Option 2: Direct Payment Gateway --}}
                                    <label @click="subscriptionMode = 'direct_pay'"
                                           :class="subscriptionMode === 'direct_pay'
                                               ? 'border-[#1E88E5] bg-[#EAF6FF]/60 ring-2 ring-[#1E88E5]/20'
                                               : 'border-slate-200 bg-white hover:border-slate-300'"
                                           class="cursor-pointer rounded-xl border p-3.5 flex items-start gap-3 transition">
                                        <input type="radio" name="subscription_mode" value="direct_pay" x-model="subscriptionMode" class="mt-1 text-[#1E88E5] focus:ring-[#1E88E5]">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-xs font-bold text-slate-900">Langsung Bayar Otomatis</span>
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-sky-100 text-[#1E88E5]">QRIS / VA</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                Bayar via Payment Gateway (QRIS/Virtual Account) & paket aktif penuh detik itu juga.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SECTION 2: DATA PEMILIK USAHA --}}
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-3.5">
                            <span class="w-6 h-6 rounded-full bg-[#EAF6FF] text-[#1E88E5] font-display text-xs font-extrabold flex items-center justify-center border border-[#DBEAF3]">2</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Akun Pemilik Usaha (Owner)</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Nama Lengkap --}}
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nama Lengkap Pemilik <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                       placeholder="Budi Santoso"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('name') border-rose-400 bg-rose-50/40 @enderror">
                            </div>

                            {{-- Username Login --}}
                            <div>
                                <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Username Login <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="username" id="username" required value="{{ old('username') }}"
                                       placeholder="budilaundry (tanpa spasi)"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('username') border-rose-400 bg-rose-50/40 @enderror">
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Alamat Email Aktif <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                       placeholder="budi@laundry.com"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('email') border-rose-400 bg-rose-50/40 @enderror">
                            </div>

                            {{-- Nomor WhatsApp / HP --}}
                            <div>
                                <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nomor WhatsApp <span class="text-rose-500">*</span>
                                </label>
                                <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                                       placeholder="081234567890"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('phone') border-rose-400 bg-rose-50/40 @enderror">
                            </div>

                            {{-- Password --}}
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                                           x-model="password"
                                           placeholder="Buat password yang kuat"
                                           class="w-full pl-3.5 pr-16 py-2.5 rounded-xl border border-slate-200 bg-[#F8FAFC] focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition @error('password') border-rose-400 bg-rose-50/40 @enderror">
                                    <button type="button" @click="showPass = !showPass"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[11px] font-semibold text-slate-500 hover:text-slate-800 cursor-pointer"
                                            x-text="showPass ? 'Tutup' : 'Lihat'">
                                        Lihat
                                    </button>
                                </div>
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Ulangi Password <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                           x-model="passwordConfirm"
                                           placeholder="Ketik ulang password"
                                           :class="passwordConfirm.length > 0 ? (password === passwordConfirm ? 'border-emerald-400 bg-emerald-50/20' : 'border-amber-400 bg-amber-50/20') : 'border-slate-200 bg-[#F8FAFC]'"
                                           class="w-full px-3.5 py-2.5 rounded-xl border focus:bg-white text-sm text-slate-900 focus:ring-2 focus:ring-[#1E88E5]/20 focus:border-[#1E88E5] focus:outline-none transition">
                                </div>
                                <div x-show="passwordConfirm.length > 0" x-cloak class="mt-1.5 flex items-center gap-1.5 text-[11px] font-medium">
                                    <template x-if="password === passwordConfirm">
                                        <span class="text-emerald-600 inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Konfirmasi password cocok
                                        </span>
                                    </template>
                                    <template x-if="password !== passwordConfirm">
                                        <span class="text-amber-600 inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Password belum sama
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Live Interactive Password Rules Checklist --}}
                            <div class="sm:col-span-2 rounded-2xl bg-[#F8FAFC] border border-slate-200/80 p-3.5">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <template x-for="(rule, index) in passRules" :key="index">
                                        <div class="flex items-center gap-2.5 select-none">
                                            <span :class="rule.valid
                                                      ? 'bg-[#1E88E5] border-[#1565C0] text-white shadow-xs shadow-blue-500/20 scale-105'
                                                      : 'bg-white border-slate-300 text-transparent'"
                                                  class="w-5 h-5 rounded-md border flex items-center justify-center transition-all duration-200 shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                            <span :class="rule.valid ? 'text-[#0F172A] font-semibold' : 'text-slate-500 font-medium'"
                                                  class="text-xs transition-colors duration-200"
                                                  x-text="rule.label"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit CTA --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 px-5 bg-[#1E88E5] hover:bg-[#1565C0] text-white font-bold rounded-xl text-sm shadow-md shadow-blue-600/20 transition transform active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                            <span x-show="subscriptionMode === 'trial'" x-text="'Daftar & Mulai Trial 14 Hari Paket ' + currentPlan.name">
                                Daftar & Mulai Trial 14 Hari
                            </span>
                            <span x-show="subscriptionMode === 'direct_pay'" x-cloak x-text="'Daftar & Lanjut Pembayaran (' + formatRupiah(currentPrice) + ')'">
                                Daftar & Lanjut Pembayaran Otomatis
                            </span>
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>

                        <p class="mt-3 text-center text-[11px] text-slate-500 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Data usaha Anda terenkripsi penuh & langsung siap digunakan setelah tombol diklik.</span>
                        </p>
                    </div>
                </form>

            </div>
        </div>

    </div>

</body>
</html>
