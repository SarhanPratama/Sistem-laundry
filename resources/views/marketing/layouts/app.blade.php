<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Kelola pesanan, pelanggan, proses cucian, pembayaran, dan laporan usaha laundry melalui satu aplikasi berbasis web.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Laundiqo — Aplikasi Manajemen Laundry Online')">
    <meta property="og:description" content="Kelola pesanan, proses cucian, pembayaran, dan laporan usaha laundry lebih mudah bersama Laundiqo.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Laundiqo — Aplikasi Manajemen Laundry Online')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing-page skylearn-app bg-slate-50 text-slate-900 antialiased overflow-x-hidden" x-data="{ mobileMenu: false }">
    <header class="marketing-nav">
        <div class="marketing-container flex h-20 items-center justify-between">
            <a href="{{ route('marketing.home') }}" class="flex items-center" aria-label="Laundiqo - Beranda">
                <img src="{{ asset('logo.png') }}" alt="Laundiqo - Kelola Laundry, Lebih Mudah" class="h-12 w-auto object-contain" width="270" height="80">
            </a>
            <nav class="hidden items-center gap-7 lg:flex" aria-label="Navigasi utama">
                <a href="{{ route('marketing.home') }}#fitur" class="marketing-link">Fitur</a>
                <a href="{{ route('marketing.home') }}#cara-kerja" class="marketing-link">Cara Kerja</a>
                <a href="{{ route('marketing.home') }}#tampilan" class="marketing-link">Tampilan Aplikasi</a>
                <a href="{{ route('marketing.home') }}#harga" class="marketing-link">Harga</a>
                <a href="{{ route('tracking.index') }}" class="marketing-link">Lacak Pesanan</a>
                <a href="{{ route('marketing.home') }}#faq" class="marketing-link">FAQ</a>
                <a href="{{ route('marketing.contact') }}" class="marketing-link">Kontak</a>
            </nav>
            <div class="hidden items-center gap-3 lg:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="marketing-button marketing-button-primary">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="marketing-button marketing-button-secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="marketing-button marketing-button-primary">Mulai Sekarang</a>
                @endauth
            </div>
            <button type="button" class="rounded-lg p-2 text-[#142B49] lg:hidden" @click="mobileMenu = !mobileMenu" :aria-expanded="mobileMenu.toString()" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div x-show="mobileMenu" x-cloak class="border-t border-slate-100 bg-white lg:hidden">
            <nav class="marketing-container flex flex-col gap-1 py-4" aria-label="Navigasi mobile">
                <a href="{{ route('marketing.home') }}#fitur" class="mobile-marketing-link" @click="mobileMenu = false">Fitur</a>
                <a href="{{ route('marketing.home') }}#cara-kerja" class="mobile-marketing-link" @click="mobileMenu = false">Cara Kerja</a>
                <a href="{{ route('marketing.home') }}#tampilan" class="mobile-marketing-link" @click="mobileMenu = false">Tampilan Aplikasi</a>
                <a href="{{ route('marketing.home') }}#harga" class="mobile-marketing-link" @click="mobileMenu = false">Harga</a>
                <a href="{{ route('tracking.index') }}" class="mobile-marketing-link" @click="mobileMenu = false">Lacak Pesanan</a>
                <a href="{{ route('marketing.home') }}#faq" class="mobile-marketing-link" @click="mobileMenu = false">FAQ</a>
                <a href="{{ route('marketing.contact') }}" class="mobile-marketing-link">Kontak</a>
                <div class="mt-3 flex gap-2 border-t border-slate-100 pt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="marketing-button marketing-button-primary flex-1">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="marketing-button marketing-button-secondary flex-1">Masuk</a>
                        <a href="{{ route('register') }}" class="marketing-button marketing-button-primary flex-1">Mulai Sekarang</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>
    <main class="overflow-x-hidden">@yield('content')</main>
    <footer class="bg-[#142B49] text-slate-300">
        <div class="marketing-container grid gap-10 py-14 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="mb-4 inline-flex rounded-xl bg-white px-3 py-2">
                    <img src="{{ asset('logo.png') }}" alt="Laundiqo" class="h-12 w-auto object-contain" width="270" height="80">
                </div>
                <p class="max-w-sm text-sm leading-6 text-slate-400">{{ \App\Models\Setting::getPlatform('platform_tagline', 'Laundiqo — Kelola Laundry, Lebih Mudah.') }}</p>
            </div>
            <div><h2 class="footer-heading">Produk</h2><div class="space-y-3 text-sm"><a href="{{ route('marketing.home') }}#fitur" class="footer-link">Fitur</a><a href="{{ route('marketing.home') }}#cara-kerja" class="footer-link">Cara Kerja</a><a href="{{ route('marketing.home') }}#tampilan" class="footer-link">Tampilan Aplikasi</a><a href="{{ route('marketing.home') }}#harga" class="footer-link">Harga</a><a href="{{ route('tracking.index') }}" class="footer-link">Lacak Pesanan</a><a href="{{ route('marketing.home') }}#faq" class="footer-link">FAQ</a></div></div>
            <div><h2 class="footer-heading">Akun & Bantuan</h2><div class="space-y-3 text-sm"><a href="{{ route('register') }}" class="footer-link">Daftar</a><a href="{{ route('login') }}" class="footer-link">Masuk</a><a href="{{ route('marketing.contact') }}" class="footer-link">Kontak</a><a href="{{ route('marketing.privacy') }}" class="footer-link">Kebijakan Privasi</a><a href="{{ route('marketing.terms') }}" class="footer-link">Syarat Layanan</a></div></div>
        </div>
        <div class="border-t border-white/10"><div class="marketing-container py-5 text-xs text-slate-500">&copy; {{ date('Y') }} Laundiqo. Hak cipta dilindungi.</div></div>
    </footer>
</body>
</html>
