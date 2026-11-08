<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cek status pengerjaan dan pengambilan cucian laundry Anda secara real-time menggunakan nomor transaksi nota.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <title>Lacak Status Cucian — {{ \App\Models\Setting::getPlatform('platform_name', 'Laundiqo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing-page skylearn-app min-h-screen flex flex-col bg-[#F5FAFD] text-[#0B1F3A] antialiased">

    {{-- Topbar Minimalis Portal Pelanggan --}}
    <header class="sticky top-0 z-30 border-b border-[#DBEAF3] bg-white/90 backdrop-blur-md">
        <div class="marketing-container flex h-20 items-center justify-between">
            <a href="{{ route('marketing.home') }}" class="flex items-center gap-3" aria-label="Beranda Laundiqo">
                <img src="{{ asset('logo.png') }}" alt="Laundiqo" class="h-11 w-auto object-contain" width="240" height="72">
                <span class="hidden sm:inline-flex items-center rounded-full bg-[#E8F4FA] px-3 py-1 text-xs font-bold text-[#075C91] border border-[#CBE4F2]">
                    Portal Pelanggan Cucian
                </span>
            </a>

            <a href="{{ route('marketing.home') }}" class="group inline-flex items-center gap-2 rounded-xl border border-[#DBEAF3] bg-white px-4 py-2.5 text-sm font-bold text-[#142B49] shadow-sm transition-all hover:border-[#087FB8] hover:text-[#087FB8]">
                <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 relative overflow-hidden">
        <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full bg-[#087FB8]/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-[#08C6D8]/10 blur-3xl"></div>

        <div class="w-full max-w-lg relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-8">
                <span class="marketing-eyebrow">REAL-TIME ORDER TRACKING</span>
                <h1 class="mt-3 text-3xl sm:text-4xl font-black tracking-tight text-[#0B1F3A]">
                    Cek Status Pesanan
                </h1>
                <p class="mt-3 text-sm sm:text-base text-[#59708B] leading-relaxed">
                    Masukkan nomor transaksi yang tertera pada struk nota atau pesan WhatsApp dari outlet laundry Anda.
                </p>
            </div>

            {{-- Alert Error --}}
            @if(session('error'))
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <p class="text-sm font-semibold leading-relaxed">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Form Pencarian --}}
            <div class="rounded-3xl border border-[#DBEAF3] bg-white p-7 sm:p-9 shadow-xl shadow-[#0B1F3A]/5">
                <form action="{{ route('tracking.search') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="order_number" class="block text-sm font-bold text-[#142B49] mb-2">
                            Nomor Transaksi / Nota Laundry
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="order_number"
                                name="order_number"
                                value="{{ old('order_number') }}"
                                required
                                autofocus
                                autocomplete="off"
                                class="w-full rounded-2xl border-2 border-[#DBEAF3] bg-[#F8FCFF] px-4 py-4 text-center font-mono text-lg sm:text-xl font-bold uppercase tracking-wider text-[#0B1F3A] placeholder:text-slate-400 placeholder:font-normal focus:border-[#087FB8] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#087FB8]/15 transition-all"
                                placeholder="CONTOH: MAW01-20261108-0001"
                            >
                        </div>
                        <p class="mt-2.5 text-xs text-[#59708B] flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-[#087FB8] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Format kode nota unik untuk setiap cabang/mitra laundry (Contoh: <code class="font-mono font-bold text-[#075C91]">BRK02-{{ date('Ymd') }}-0001</code>)</span>
                        </p>
                    </div>

                    <button type="submit" class="group w-full marketing-button marketing-button-primary py-4 text-base shadow-lg shadow-[#087FB8]/20">
                        <span>Lacak Status Cucian</span>
                        <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>

                {{-- Info Tahapan --}}
                <div class="mt-7 pt-6 border-t border-[#E8F2F8]">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] text-center mb-3">
                        Tahapan yang Dapat Dipantau Secara Live
                    </p>
                    <div class="grid grid-cols-5 gap-1.5 text-center text-[11px] font-semibold text-[#142B49]">
                        <div class="rounded-xl bg-[#F0F7FF] py-2 px-1 border border-[#DBEAF3]">📋 Diterima</div>
                        <div class="rounded-xl bg-[#F0F7FF] py-2 px-1 border border-[#DBEAF3]">🫧 Dicuci</div>
                        <div class="rounded-xl bg-[#F0F7FF] py-2 px-1 border border-[#DBEAF3]">☀️ Kering</div>
                        <div class="rounded-xl bg-[#F0F7FF] py-2 px-1 border border-[#DBEAF3]">👔 Setrika</div>
                        <div class="rounded-xl bg-emerald-50 text-emerald-800 py-2 px-1 border border-emerald-200">✅ Siap</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer Minimalis --}}
    <footer class="border-t border-[#DBEAF3] bg-white py-5 text-center text-xs text-[#59708B]">
        <div class="marketing-container flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::getPlatform('platform_name', 'Laundiqo') }} — Sistem Informasi Manajemen Laundry Multi-Outlet.</p>
            <p>Kendala dengan cucian Anda? Silakan hubungi kontak toko yang tertera pada struk nota.</p>
        </div>
    </footer>

</body>
</html>
