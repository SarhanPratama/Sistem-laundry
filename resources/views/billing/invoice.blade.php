@extends('layouts.app')

@section('title', 'Tagihan ' . $invoice->invoice_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('billing.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Halaman Langganan
        </a>

        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Tagihan
        </button>
    </div>

    {{-- Official Invoice Paper Card --}}
    <div class="bg-white rounded-3xl border border-gray-200 shadow-md p-8 sm:p-10 space-y-8 relative overflow-hidden">

        @if ($invoice->status === 'paid')
            {{-- Paid Watermark Stamp --}}
            <div class="absolute top-10 right-10 rotate-12 border-4 border-emerald-600/40 text-emerald-600/50 font-black text-3xl px-6 py-2 rounded-2xl tracking-widest uppercase select-none pointer-events-none">
                PAID / LUNAS
            </div>
        @endif

        {{-- Invoice Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-gray-100 pb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600">INVOICE RESMI SAAS</span>
                <h1 class="text-2xl font-black text-gray-900 mt-1">{{ $invoice->invoice_number }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">Dibuat pada: {{ $invoice->created_at->format('d M Y, H:i') }} WIB</p>
            </div>

            <div class="text-left sm:text-right">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status Tagihan</p>
                <div class="mt-1">
                    @if ($invoice->status === 'paid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Lunas ({{ $invoice->paid_at?->format('d M Y') }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-xs">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Pembayaran
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5">Jatuh Tempo: {{ $invoice->due_at->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Parties Information --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div>
                <p class="font-bold uppercase tracking-wider text-gray-400 mb-1">Ditagihkan Kepada:</p>
                <p class="font-extrabold text-sm text-gray-900">{{ $invoice->tenant?->name }}</p>
                <p class="text-gray-600 mt-0.5">{{ $invoice->tenant?->address ?? 'Alamat Outlet Laundry' }}</p>
                <p class="text-gray-600">Telepon / WA: {{ $invoice->tenant?->phone ?? '-' }}</p>
            </div>

            <div class="sm:text-right">
                <p class="font-bold uppercase tracking-wider text-gray-400 mb-1">Penyedia Platform SaaS:</p>
                <p class="font-extrabold text-sm text-gray-900">Platform SaaS Sistem Laundry</p>
                <p class="text-gray-600 mt-0.5">Layanan Berlangganan Cloud Manajemen Laundry</p>
                <p class="text-gray-600">Email: billing@laundrysaas.test</p>
            </div>
        </div>

        {{-- Line Items --}}
        <div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 uppercase font-semibold">
                        <th class="py-3 px-2">Deskripsi Layanan</th>
                        <th class="py-3 px-2 text-center">Siklus</th>
                        <th class="py-3 px-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-4 px-2">
                            <p class="font-bold text-gray-900 text-sm">Paket Langganan {{ $invoice->plan?->name }}</p>
                            <p class="text-gray-500 mt-0.5">Kapasitas hingga {{ $invoice->plan?->max_outlets }} outlet dan {{ $invoice->plan?->max_staff }} akun staf.</p>
                        </td>
                        <td class="py-4 px-2 text-center font-medium text-gray-600">
                            {{ $invoice->billing_cycle === 'yearly' ? '1 Tahun (Tahunan)' : '1 Bulan' }}
                        </td>
                        <td class="py-4 px-2 text-right font-bold text-gray-900 text-sm">
                            Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-900">
                        <td colspan="2" class="py-4 px-2 text-right font-black text-sm text-gray-900">TOTAL PEMBAYARAN:</td>
                        <td class="py-4 px-2 text-right font-black text-lg text-blue-600">
                            Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Payment Gateway Automatic Checkout --}}
        @if ($invoice->status === 'unpaid')
            <div class="rounded-2xl bg-gradient-to-br from-slate-50 via-blue-50/40 to-indigo-50/40 border border-blue-100 p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider mb-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
                            Payment Gateway Otomatis 24/7
                        </span>
                        <h3 class="font-extrabold text-base text-gray-900">Pembayaran Instan &amp; Aktivasi Otomatis</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Langganan usaha laundry Anda akan langsung aktif detik itu juga begitu pembayaran berhasil diverifikasi oleh sistem Payment Gateway.</p>
                    </div>
                </div>

                {{-- Supported Automatic Payment Channels --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 bg-white rounded-xl border border-gray-200/80 shadow-xs flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-black shrink-0 text-[10px]">
                            QRIS
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">QRIS Dinamis</p>
                            <p class="text-gray-500 text-[11px] mt-0.5">Scan dari BCA, Mandiri, BRI, GoPay, OVO, DANA, ShopeePay</p>
                        </div>
                    </div>
                    <div class="p-3.5 bg-white rounded-xl border border-gray-200/80 shadow-xs flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-black shrink-0 text-[10px]">
                            VA
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">Virtual Account Bank</p>
                            <p class="text-gray-500 text-[11px] mt-0.5">BCA, Mandiri Bill, BNI, BRI, Permata, BSI (Otomatis Cek)</p>
                        </div>
                    </div>
                    <div class="p-3.5 bg-white rounded-xl border border-gray-200/80 shadow-xs flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-black shrink-0 text-[10px]">
                            PAY
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">E-Wallet &amp; Instant</p>
                            <p class="text-gray-500 text-[11px] mt-0.5">Verifikasi webhook real-time tanpa perlu upload bukti</p>
                        </div>
                    </div>
                </div>

                @if (($gatewayConfigured ?? false) && $invoice->snap_token)
                    {{-- Real Midtrans Snap Popup Integration --}}
                    <form id="gateway-status-form" method="POST" action="{{ route('billing.invoice.check-status', $invoice) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="transaction_id" id="snap-transaction-id" value="">
                        <input type="hidden" name="order_id" id="snap-order-id" value="">
                        <input type="hidden" name="transaction_status" id="snap-transaction-status" value="">
                        <input type="hidden" name="status_code" id="snap-status-code" value="">
                        <input type="hidden" name="payment_type" id="snap-payment-type" value="">
                    </form>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button type="button" id="pay-snap-button"
                                class="flex-1 py-3 px-5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-blue-600/25 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Bayar Sekarang via Payment Gateway &rarr;</span>
                        </button>

                        @if ($invoice->payment_url)
                            <a href="{{ $invoice->payment_url }}" target="_blank" rel="noopener"
                               class="py-3 px-4 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs text-center transition">
                                Buka Link Checkout
                            </a>
                        @endif

                        <button type="button" onclick="document.getElementById('gateway-status-form').submit()"
                                class="py-3 px-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition">
                            Cek Status Pembayaran
                        </button>
                    </div>

                    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
                    <script>
                        function submitSnapResult(result, fallbackStatus) {
                            const r = result || {};
                            document.getElementById('snap-transaction-id').value = r.transaction_id || '';
                            document.getElementById('snap-order-id').value = r.order_id || '';
                            document.getElementById('snap-transaction-status').value = r.transaction_status || fallbackStatus || '';
                            document.getElementById('snap-status-code').value = r.status_code || (fallbackStatus === 'settlement' ? '200' : '');
                            document.getElementById('snap-payment-type').value = r.payment_type || 'midtrans_snap';
                            document.getElementById('gateway-status-form').submit();
                        }

                        function openMidtransSnapPopup() {
                            if (typeof window.snap === 'undefined') {
                                alert('Gagal memuat modul Midtrans Snap. Silakan gunakan tombol Buka Link Checkout.');
                                return;
                            }
                            window.snap.pay(@js($invoice->snap_token), {
                                onSuccess: function (result) {
                                    submitSnapResult(result, 'settlement');
                                },
                                onPending: function (result) {
                                    submitSnapResult(result, 'pending');
                                },
                                onError: function () {
                                    alert('Pembayaran gagal diproses. Silakan coba kembali.');
                                }
                            });
                        }

                        document.getElementById('pay-snap-button')?.addEventListener('click', openMidtransSnapPopup);

                        // Auto-open Midtrans Snap popup once per invoice session when arriving on an unpaid invoice page
                        window.addEventListener('load', function () {
                            const autoOpenKey = 'snap_auto_opened_{{ $invoice->id }}';
                            const hasReturnedFromSnap = window.location.search.includes('transaction_status') || window.location.search.includes('status_code');
                            if (!hasReturnedFromSnap && !sessionStorage.getItem(autoOpenKey)) {
                                sessionStorage.setItem(autoOpenKey, '1');
                                setTimeout(function () {
                                    if (typeof window.snap !== 'undefined') {
                                        openMidtransSnapPopup();
                                    }
                                }, 450);
                            }
                        });
                    </script>

                    @if (!config('services.midtrans.is_production'))
                        <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                            <span>Mode <strong>Midtrans Sandbox</strong> aktif. Ingin langsung mengetes aktivasi paket tanpa simulator eksternal?</span>
                            <form method="POST" action="{{ route('billing.invoice.pay', $invoice) }}">
                                @csrf
                                <input type="hidden" name="payment_method" value="simulated">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold text-xs transition cursor-pointer">
                                    Simulasi Lunas Instan (Sandbox) &rarr;
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    {{-- Automatic Sandbox Gateway Simulation (When MIDTRANS_SERVER_KEY is not yet set in .env) --}}
                    <form method="POST" action="{{ route('billing.invoice.pay', $invoice) }}" class="pt-2 space-y-3">
                        @csrf
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <select name="payment_method" required class="px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold text-gray-700 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="qris">QRIS Otomatis (GoPay / OVO / DANA / M-Banking)</option>
                                <option value="virtual_account">Virtual Account Otomatis (BCA / Mandiri / BNI / BRI)</option>
                                <option value="bank_transfer">Bank Transfer Instan</option>
                                <option value="simulated">Simulasi Callback Payment Gateway</option>
                            </select>

                            <button type="submit"
                                    class="flex-1 py-2.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Bayar &amp; Aktifkan Otomatis Sekarang &rarr;</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-400">
                            Mode Sandbox Otomatis aktif. Untuk menghubungkan popup live Midtrans Snap, cukup isi <code class="text-gray-600 font-mono">MIDTRANS_CLIENT_KEY</code> dan <code class="text-gray-600 font-mono">MIDTRANS_SERVER_KEY</code> pada file <code class="text-gray-600 font-mono">.env</code>.
                        </p>
                    </form>
                @endif
            </div>
        @else
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 flex items-center justify-between text-xs text-emerald-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold">
                        ✓
                    </div>
                    <div>
                        <p class="font-bold text-sm">Pembayaran Berhasil Diverifikasi</p>
                        <p class="text-[11px] text-emerald-700 mt-0.5">Metode: {{ strtoupper($invoice->payment_method ?? 'Transfer') }} • Waktu: {{ $invoice->paid_at?->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
                <a href="{{ route('billing.index') }}" class="font-bold text-emerald-800 underline hover:text-emerald-950">
                    Kembali ke Dashboard Langganan
                </a>
            </div>
        @endif

    </div>

</div>
@endsection

