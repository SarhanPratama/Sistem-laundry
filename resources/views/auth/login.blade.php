<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Dashboard — Laundiqo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes floatDelayed {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(8px); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .animate-float-slow { animation: floatSlow 5.5s ease-in-out infinite; }
        .animate-float-delayed { animation: floatDelayed 6.5s ease-in-out 1.2s infinite; }
        .animate-fade-up { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-up-delay-1 { opacity: 0; animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.08s forwards; }
        .animate-fade-up-delay-2 { opacity: 0; animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.16s forwards; }
        .animate-shake { animation: shakeError 0.45s ease-in-out; }
    </style>
</head>
<body class="skylearn-app min-h-screen bg-[#F5FAFF] flex items-center justify-center p-4 sm:p-6 lg:p-8">

    {{-- Main Container Card --}}
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl border border-[#DBEAF3] overflow-hidden grid grid-cols-1 lg:grid-cols-12 animate-fade-up">

        {{-- LEFT PANEL: Solid Navy Brand & Animated Showcase (Desktop) --}}
        <div class="hidden lg:flex lg:col-span-6 relative bg-[#0B1F3A] p-10 text-white flex-col justify-between overflow-hidden">

            {{-- Subtle Dot Pattern (Solid, No Gradient) --}}
            <div class="pointer-events-none absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 22px 22px;"></div>

            {{-- Top Logo & Back to Home --}}
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ route('marketing.home') }}" class="inline-flex items-center rounded-2xl bg-white px-4 py-2.5 shadow-sm transition transform hover:scale-105">
                    <img src="{{ asset('logo.png') }}" alt="Laundiqo - Kelola Laundry, Lebih Mudah" class="h-10 w-auto object-contain">
                </a>
                <a href="{{ route('marketing.home') }}" class="text-xs font-semibold text-slate-200 hover:text-white inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#142B49] hover:bg-[#1E3A5F] border border-white/10 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Beranda
                </a>
            </div>

            {{-- Middle Interactive Floating Cards (Solid Colors) --}}
            <div class="relative z-10 my-8 space-y-4">
                {{-- Floating Card 1: Real-time Revenue --}}
                <div class="animate-float-slow bg-[#142B49] border border-white/10 rounded-2xl p-4 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#087FB8] flex items-center justify-center text-white font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-300 font-medium">Kas Masuk Hari Ini</p>
                                <p class="text-lg font-black text-white tracking-tight">Rp 1.450.000</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-600 text-white">
                            &bull; Aktif
                        </span>
                    </div>
                </div>

                {{-- Floating Card 2: Production Pipeline --}}
                <div class="animate-float-delayed ml-6 bg-[#142B49] border border-white/10 rounded-2xl p-4 shadow-lg">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-xs font-bold text-white">Status Produksi & Rak</span>
                        <span class="text-[10px] font-mono text-slate-300">Rak A1 &bull; Siap Diambil</span>
                    </div>
                    <div class="grid grid-cols-5 gap-1.5 text-center text-[10px] font-bold">
                        <span class="py-1 rounded bg-[#0B1F3A] text-slate-300 border border-white/10">Terima</span>
                        <span class="py-1 rounded bg-[#0B1F3A] text-slate-300 border border-white/10">Cuci</span>
                        <span class="py-1 rounded bg-[#0B1F3A] text-slate-300 border border-white/10">Kering</span>
                        <span class="py-1 rounded bg-[#0B1F3A] text-slate-300 border border-white/10">Setrika</span>
                        <span class="py-1 rounded bg-[#087FB8] text-white">Siap &check;</span>
                    </div>
                </div>
            </div>

            {{-- Bottom Copywriting --}}
            <div class="relative z-10">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#142B49] border border-white/10 text-[#08C6D8] text-[11px] font-bold uppercase tracking-wider mb-3">
                    Sistem Manajemen Laundry SaaS
                </span>
                <h2 class="text-2xl font-black leading-snug text-white">
                    Satu Dashboard untuk Pemilik, Kasir, dan Operator.
                </h2>
                <p class="mt-2 text-xs leading-relaxed text-slate-300">
                    Pantau transaksi cucian, cetak struk thermal, kirim nota WhatsApp, dan kelola laporan keuangan usaha laundry Anda dari mana saja.
                </p>
            </div>
        </div>

        {{-- RIGHT PANEL: Login Form (Clean Solid Colors) --}}
        <div class="lg:col-span-6 p-7 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">

            {{-- Logo & Heading --}}
            <div class="flex flex-col items-center lg:items-start mb-7 animate-fade-up-delay-1">
                <a href="{{ route('marketing.home') }}" class="inline-flex items-center mb-4 group">
                    <img src="{{ asset('logo.png') }}" alt="Laundiqo" class="h-12 w-auto object-contain transition duration-300 group-hover:scale-105">
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-[#0B1F3A] tracking-tight text-center lg:text-left">
                    Selamat Datang Kembali
                </h1>
                <p class="text-sm text-slate-500 mt-1 text-center lg:text-left">
                    Masuk ke akun <span class="font-bold text-[#087FB8]">Laundiqo</span> untuk mengelola operasional laundry Anda.
                </p>
            </div>

            {{-- Credentials Error Alert with Shake Animation --}}
            @if ($errors->first('credentials'))
                <div class="animate-shake flex items-start gap-3 mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-700">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-red-800 text-xs uppercase tracking-wider">Gagal Masuk</p>
                        <p class="mt-0.5 text-xs text-red-600">{{ $errors->first('credentials') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}"
                  x-data="{ showPassword: false, focused: '', submitting: false }"
                  @submit="submitting = true"
                  class="space-y-5 animate-fade-up-delay-2">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-[#0B1F3A] mb-2">
                        Username
                    </label>
                    <div class="relative transition duration-200" :class="focused === 'username' ? 'scale-[1.01]' : ''">
                        <span class="absolute inset-y-0 left-3.5 flex items-center transition-colors duration-200"
                              :class="focused === 'username' ? 'text-[#087FB8]' : 'text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            autocomplete="username"
                            required
                            autofocus
                            value="{{ old('username') }}"
                            @focus="focused = 'username'"
                            @blur="focused = ''"
                            placeholder="Masukkan username Anda"
                            class="w-full pl-11 pr-4 py-3 border {{ $errors->has('username') || $errors->first('credentials') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-slate-50 focus:bg-white' }} rounded-xl text-sm text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#087FB8] focus:border-[#087FB8] transition duration-200"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#0B1F3A]">
                            Password
                        </label>
                        <a href="{{ route('marketing.contact') }}" class="text-xs font-semibold text-[#087FB8] hover:text-[#075C91] hover:underline transition">
                            Butuh bantuan akses?
                        </a>
                    </div>
                    <div class="relative transition duration-200" :class="focused === 'password' ? 'scale-[1.01]' : ''">
                        <span class="absolute inset-y-0 left-3.5 flex items-center transition-colors duration-200"
                              :class="focused === 'password' ? 'text-[#087FB8]' : ($errors->first('credentials') ? 'text-red-500' : 'text-slate-400')">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 1a5 5 0 00-5 5v2H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V10a2 2 0 00-2-2h-1V6a5 5 0 00-5-5zm3 7V6a3 3 0 10-6 0v2h6zm-3 4a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            required
                            @focus="focused = 'password'"
                            @blur="focused = ''"
                            placeholder="••••••••••"
                            class="w-full pl-11 pr-11 py-3 border {{ $errors->first('credentials') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-slate-50 focus:bg-white' }} rounded-xl text-sm text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#087FB8] focus:border-[#087FB8] transition duration-200"
                        >
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-3.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                            :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2.5 text-xs font-medium text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#087FB8] focus:ring-[#087FB8]">
                        Ingat sesi masuk saya di perangkat ini
                    </label>
                </div>

                {{-- Submit Button (Solid Color, No Gradient) --}}
                <button
                    type="submit"
                    :disabled="submitting"
                    class="group w-full flex items-center justify-center gap-2.5 py-3.5 px-5 bg-[#087FB8] hover:bg-[#075C91] text-white text-sm font-bold rounded-xl shadow-sm transition-all duration-200 transform active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#087FB8] disabled:opacity-70"
                >
                    <span x-show="!submitting">Masuk ke Dashboard</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                    <svg x-show="!submitting" class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>

            {{-- Footer Links: Register & Tracking --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 animate-fade-up-delay-2">
                <div>
                    Belum punya akun usaha?
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1 font-bold text-[#087FB8] hover:text-[#075C91] hover:underline ml-1">
                        <span>Daftar Trial 14 Hari</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                </div>
                <a href="{{ route('tracking.index') }}" class="inline-flex items-center gap-1 font-semibold text-slate-500 hover:text-[#0B1F3A] transition">
                    <span>Lacak Pesanan</span>
                </a>
            </div>

        </div>

    </div>

</body>
</html>
