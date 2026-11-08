<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <title>Nota {{ $order->order_number }} — {{ $order->tenant?->name ?? 'Tracking Laundry' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing-page skylearn-app min-h-screen flex flex-col bg-[#F5FAFD] text-[#0B1F3A] antialiased">

    {{-- Topbar Minimalis Portal Pelanggan --}}
    <header class="sticky top-0 z-30 border-b border-[#DBEAF3] bg-white/90 backdrop-blur-md">
        <div class="marketing-container flex h-20 items-center justify-between gap-3">
            <a href="{{ route('marketing.home') }}" class="flex items-center gap-3" aria-label="Beranda Laundiqo">
                <img src="{{ asset('logo.png') }}" alt="Laundiqo" class="h-11 w-auto object-contain" width="240" height="72">
                <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Update Otomatis Aktif
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('tracking.index') }}" class="group inline-flex items-center gap-2 rounded-xl border border-[#DBEAF3] bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-[#142B49] shadow-sm transition-all hover:border-[#087FB8] hover:text-[#087FB8]">
                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    <span>Cek Nomor Lain</span>
                </a>
                <a href="{{ route('marketing.home') }}" class="hidden md:inline-flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-[#59708B] hover:text-[#0B1F3A]">
                    Beranda
                </a>
            </div>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="flex-1 py-8 sm:py-12 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">

            {{-- Live Badge untuk Mobile --}}
            <div class="sm:hidden text-center mb-4">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Update Otomatis Aktif
                </span>
            </div>

            {{-- Main Card (Auto-morph via Live Polling) --}}
            <div id="live-tracking-card"
                 data-signature="{{ md5($order->status . '|' . $order->payment_status . '|' . $order->amount_paid . '|' . $order->rack_id . '|' . $order->updated_at . '|' . $order->statusLogs->count()) }}"
                 class="rounded-3xl border border-[#DBEAF3] bg-white shadow-xl shadow-[#0B1F3A]/5 overflow-hidden">

                {{-- Header Identitas Outlet (Tenant) & Nomor Nota --}}
                <div class="bg-gradient-to-br from-[#075C91] via-[#087FB8] to-[#08C6D8] text-white p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-white/15">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-cyan-50 backdrop-blur-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                                </svg>
                                Outlet Laundry
                            </span>
                            <h1 class="mt-2 text-2xl sm:text-3xl font-black tracking-tight text-white">
                                {{ $order->tenant?->name ?? 'Mitra Laundry Laundiqo' }}
                            </h1>
                            @if($order->tenant?->address)
                                <p class="mt-1 text-xs sm:text-sm text-blue-100">{{ $order->tenant->address }}</p>
                            @endif
                        </div>

                        @if($order->tenant?->phone)
                            <div class="text-left sm:text-right">
                                <span class="block text-[11px] uppercase tracking-wider text-blue-100">Kontak Outlet</span>
                                <p class="text-sm font-bold text-white">{{ $order->tenant->phone }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase font-bold tracking-wider text-blue-100">Nomor Transaksi / Nota</p>
                            <h2 class="mt-1 font-mono text-2xl sm:text-3xl font-black tracking-wider text-white">
                                {{ $order->order_number }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if($order->payment_status === 'lunas')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 border border-emerald-300/40 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-white">
                                    ✓ Lunas
                                </span>
                            @elseif($order->payment_status === 'sebagian')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/25 border border-amber-300/40 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-white">
                                    ⏳ Dibayar Sebagian
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/25 border border-amber-300/40 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-white">
                                    Belum Bayar
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Informasi Pelanggan & Tanggal --}}
                <div class="p-6 sm:p-8 border-b border-[#E8F2F8] bg-[#FAFDFF]">
                    <div class="grid grid-cols-2 gap-5 sm:gap-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] mb-1">Nama Pelanggan</p>
                            <p class="text-base sm:text-lg font-extrabold text-[#0B1F3A]">{{ $order->customer->masked_name ?? $order->customer->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] mb-1">Nomor Telepon</p>
                            <p class="text-base sm:text-lg font-extrabold text-[#0B1F3A]">{{ $order->customer->masked_phone ?? '********' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] mb-1">Tanggal Diterima</p>
                            <p class="text-sm sm:text-base font-bold text-[#142B49]">{{ $order->received_date->translatedFormat('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] mb-1">Estimasi Selesai</p>
                            <p class="text-sm sm:text-base font-bold text-[#087FB8]">{{ $order->estimated_done_date->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Status Saat Ini --}}
                @php
                    $bgColors = [
                        'blue' => 'bg-[#F0F7FF] border-[#DBEAF3]',
                        'yellow' => 'bg-amber-50/70 border-amber-200',
                        'orange' => 'bg-orange-50/70 border-orange-200',
                        'purple' => 'bg-purple-50/70 border-purple-200',
                        'green' => 'bg-emerald-50/80 border-emerald-200',
                        'gray' => 'bg-slate-50 border-slate-200',
                        'red' => 'bg-rose-50 border-rose-200',
                    ];
                    $currentBg = $bgColors[$currentStatus['color']] ?? 'bg-[#F0F7FF] border-[#DBEAF3]';
                @endphp
                <div class="p-6 sm:p-8 {{ $currentBg }} border-b">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#59708B] mb-3">Status Pengerjaan Saat Ini</p>
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white text-4xl shadow-sm border border-[#DBEAF3]">
                            {{ $currentStatus['icon'] }}
                        </div>
                        <div>
                            <p class="text-2xl font-black text-[#0B1F3A]">{{ $currentStatus['label'] }}</p>
                            <p class="text-xs sm:text-sm text-[#59708B] mt-1">
                                Diperbarui pada: <span class="font-semibold text-[#142B49]">{{ $order->updated_at->translatedFormat('d F Y · H:i') }}</span>
                            </p>
                        </div>
                    </div>

                    @if($order->status === 'siap_diambil' && $order->rack)
                        <div class="mt-5 rounded-2xl bg-white border-2 border-[#087FB8]/30 p-4 sm:p-5 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-[#087FB8]">Lokasi Penyimpanan Cucian</p>
                                    <div class="mt-1 flex items-center gap-2 text-lg sm:text-xl font-black text-[#0B1F3A]">
                                        <svg class="w-6 h-6 text-[#087FB8] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span>{{ $order->rack->name }}</span>
                                    </div>
                                    <p class="text-xs text-[#59708B] mt-1">Cucian Anda sudah selesai dan siap diambil. Tunjukkan halaman ini kepada kasir.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tahapan Pengerjaan (Timeline) --}}
                <div class="p-6 sm:p-8 border-b border-[#E8F2F8]">
                    <p class="text-sm font-extrabold uppercase tracking-wider text-[#0B1F3A] mb-6">Riwayat Tahapan Cucian</p>

                    <div class="space-y-5">
                        @foreach($timeline as $index => $step)
                            @php
                                $currentStatusIndex = array_search($order->status, array_column($timeline, 'status'));
                                $isCompleted = !$order->is_cancelled && $currentStatusIndex !== false && $index <= $currentStatusIndex;
                                $isCurrent = !$order->is_cancelled && $order->status === $step['status'];
                            @endphp

                            <div class="flex gap-4">
                                {{-- Timeline Dot --}}
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all {{
                                        $isCurrent ? 'bg-[#087FB8] text-white shadow-md shadow-[#087FB8]/30 ring-4 ring-[#087FB8]/15' :
                                        (($step['time'] || $isCompleted) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500')
                                    }}">
                                        @if(($step['time'] || $isCompleted) && !$isCurrent)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    @if($index < count($timeline) - 1)
                                        <div class="w-0.5 h-10 mt-1.5 {{ ($step['time'] || $isCompleted) ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                                    @endif
                                </div>

                                {{-- Timeline Content --}}
                                <div class="flex-1 pt-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-bold {{ $isCurrent ? 'text-[#087FB8] text-base' : 'text-[#0B1F3A] text-sm' }}">
                                            {{ $step['label'] }}
                                        </p>
                                        @if($step['time'])
                                            <span class="text-xs font-semibold text-[#59708B]">
                                                {{ $step['time']->translatedFormat('d M Y · H:i') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($step['time'])
                                        @if($step['status'] === 'siap_diambil' && $order->rack)
                                            <div class="mt-2 rounded-xl bg-[#F0F7FF] border border-[#DBEAF3] px-3.5 py-2.5 inline-block">
                                                <p class="text-xs font-bold text-[#075C91] flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                    </svg>
                                                    <span>Disimpan di: {{ $order->rack->name }}</span>
                                                </p>
                                            </div>
                                        @else
                                            <p class="text-xs text-emerald-700 font-medium mt-0.5">Tahap selesai diproses</p>
                                        @endif
                                    @else
                                        <p class="text-xs text-slate-400 italic mt-0.5">Menunggu giliran proses...</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Rincian Layanan --}}
                <div class="p-6 sm:p-8 border-b border-[#E8F2F8]">
                    <p class="text-sm font-extrabold uppercase tracking-wider text-[#0B1F3A] mb-4">Rincian Item Layanan</p>
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex justify-between items-center p-4 bg-[#F8FCFF] border border-[#E8F2F8] rounded-2xl">
                                <div>
                                    <p class="font-bold text-[#0B1F3A]">{{ $item->service->name ?? 'Layanan Laundry' }}</p>
                                    <p class="text-xs text-[#59708B] mt-0.5">
                                        {{ $item->service->category->name ?? 'Reguler' }} · Rp {{ number_format($item->unit_price, 0, ',', '.') }} / {{ $item->service->unit ?? 'item' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-[#59708B]">{{ (float) $item->quantity }} {{ $item->service->unit ?? '' }}</p>
                                    <p class="text-sm font-extrabold text-[#0B1F3A] mt-0.5">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Rincian Biaya & Status Pembayaran --}}
                @php
                    $remainingBalance = max(0, (float) $order->total_cost - (float) $order->amount_paid);
                @endphp
                <div class="p-6 sm:p-8 border-b border-[#E8F2F8] bg-[#FAFDFF]">
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-[#59708B]">Total Biaya Cucian</span>
                            <span class="font-extrabold text-[#0B1F3A] text-base">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-[#59708B]">Jumlah Sudah Dibayar</span>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                        </div>
                        @if($remainingBalance > 0)
                            <div class="border-t border-[#DBEAF3] pt-3 flex justify-between items-center text-base">
                                <span class="font-extrabold text-[#0B1F3A]">Sisa Tagihan Saat Diambil</span>
                                <span class="font-black text-amber-600 text-lg">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5">
                        @if($order->payment_status === 'lunas')
                            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3 text-emerald-800">
                                <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-bold text-sm">Pembayaran Sudah Lunas</p>
                                    <p class="text-xs text-emerald-700 mt-0.5">Terima kasih! Tidak ada sisa tagihan pada pesanan ini.</p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-start gap-3 text-amber-900">
                                <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-bold text-sm">Tagihan Belum Lunas (Sisa Rp {{ number_format($remainingBalance, 0, ',', '.') }})</p>
                                    <p class="text-xs text-amber-800 mt-0.5">Silakan lakukan pelunasan di kasir outlet <strong>{{ $order->tenant?->name ?? 'kami' }}</strong> saat mengambil cucian.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Bantuan & Navigasi --}}
                <div class="p-6 sm:p-8 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#59708B]">Butuh Bantuan Terkait Cucian Ini?</p>
                        <p class="text-sm text-[#142B49] mt-0.5">
                            Hubungi outlet <strong>{{ $order->tenant?->name ?? 'Laundry' }}</strong>{{ $order->tenant?->phone ? ' di ' . $order->tenant->phone : '' }}.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('tracking.index') }}" class="group inline-flex items-center gap-2 rounded-xl border border-[#DBEAF3] bg-[#F8FCFF] px-4 py-2.5 text-sm font-bold text-[#087FB8] transition-all hover:bg-[#087FB8] hover:text-white">
                            <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                            </svg>
                            <span>Cek Pesanan Lain</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

    {{-- Footer Minimalis --}}
    <footer class="border-t border-[#DBEAF3] bg-white py-5 text-center text-xs text-[#59708B]">
        <div class="marketing-container flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>Dikelola menggunakan platform {{ \App\Models\Setting::getPlatform('platform_name', 'Laundiqo') }}.</p>
            <a href="{{ route('marketing.home') }}" class="font-semibold text-[#087FB8] hover:underline">Kembali ke Halaman Utama</a>
        </div>
    </footer>

<script>
    (function () {
        async function syncTracking() {
            const card = document.getElementById('live-tracking-card');
            if (!card) return;
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'Accept': 'text/html', 'X-Live-Sync': '1' },
                    cache: 'no-store',
                });
                if (!res.ok) return;
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.getElementById('live-tracking-card');
                if (incoming && incoming.dataset.signature !== card.dataset.signature) {
                    card.dataset.signature = incoming.dataset.signature;
                    card.innerHTML = incoming.innerHTML;
                }
            } catch (e) {
                // Silent fail on network glitch
            }
        }
        setInterval(syncTracking, 4000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') syncTracking();
        });
    })();
</script>
</body>
</html>
