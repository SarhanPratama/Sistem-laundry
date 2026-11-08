@extends('layouts.app')

@section('title', 'Semua Tagihan SaaS - Platform Admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
         detailModal: false,
         selectedInvoice: null,
         openDetail(inv) {
             this.selectedInvoice = inv;
             this.detailModal = true;
         }
     }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tagihan Langganan (Invoices)</h1>
            <p class="text-sm text-gray-500 mt-1">Catatan seluruh transaksi invoice langganan tenant dan konfirmasi pelunasan manual.</p>
        </div>

        <a href="{{ route('saas-admin.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-xs">
            &larr; Kembali ke Dashboard SaaS
        </a>
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200 text-xs">
            <a href="{{ route('saas-admin.invoices') }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition {{ empty($status) ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                Semua Tagihan ({{ ($paidCount ?? 0) + ($unpaidCount ?? 0) }})
            </a>
            <a href="{{ route('saas-admin.invoices', ['status' => 'unpaid']) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ ($status ?? '') === 'unpaid' ? 'bg-white text-amber-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <span>Belum Bayar</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($status ?? '') === 'unpaid' ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $unpaidCount ?? 0 }}
                </span>
            </a>
            <a href="{{ route('saas-admin.invoices', ['status' => 'paid']) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ ($status ?? '') === 'paid' ? 'bg-white text-emerald-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <span>Lunas</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($status ?? '') === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $paidCount ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        @if ($invoices->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-base font-medium">Belum ada tagihan langganan yang tercatat.</p>
                <p class="text-sm mt-1">Tagihan akan otomatis muncul saat tenant memilih paket langganan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nomor Tagihan</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Usaha (Tenant)</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Paket & Siklus</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nominal</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider no-sort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($invoices as $inv)
                            @php
                                $latestPayment = $inv->payments->sortByDesc('id')->first();
                                $modalPayload = [
                                    'id'             => $inv->id,
                                    'invoice_number' => $inv->invoice_number,
                                    'tenant_name'    => $inv->tenant?->name ?? '-',
                                    'tenant_slug'    => $inv->tenant?->slug ?? '-',
                                    'tenant_phone'   => $inv->tenant?->phone ?? '-',
                                    'tenant_address' => $inv->tenant?->address ?? '-',
                                    'plan_name'      => 'Paket ' . ($inv->plan?->name ?? '-'),
                                    'cycle_label'    => $inv->billing_cycle === 'yearly' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)',
                                    'amount_fmt'     => 'Rp ' . number_format($inv->amount, 0, ',', '.'),
                                    'status'         => $inv->status,
                                    'payment_method' => $inv->payment_method ? strtoupper(str_replace('_', ' ', $inv->payment_method)) : '-',
                                    'provider_ref'   => $latestPayment?->provider_ref ?? '-',
                                    'created_fmt'    => $inv->created_at?->format('d M Y, H:i') . ' WIB',
                                    'due_fmt'        => $inv->due_at?->format('d M Y') ?? '-',
                                    'paid_fmt'       => $inv->paid_at ? ($inv->paid_at->format('d M Y, H:i') . ' WIB') : 'Belum dibayar',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900 font-mono block">{{ $inv->invoice_number }}</span>
                                    @if ($inv->payment_method)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-blue-50 text-blue-700 mt-0.5">
                                            {{ str_replace('_', ' ', $inv->payment_method) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900 block">{{ $inv->tenant?->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-400 font-mono">{{ $inv->tenant?->slug ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-700">
                                    <span class="font-semibold text-blue-600">Paket {{ $inv->plan?->name ?? '-' }}</span>
                                    <span class="block text-xs text-gray-500">{{ $inv->billing_cycle === 'yearly' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900" data-order="{{ (int) $inv->amount }}">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-500" data-order="{{ $inv->created_at?->timestamp ?? 0 }}">
                                    <div>Buat: <span class="font-medium text-gray-700">{{ $inv->created_at?->format('d/m/Y H:i') }}</span></div>
                                    @if ($inv->paid_at)
                                        <div class="text-emerald-600 font-semibold mt-0.5">Lunas: {{ $inv->paid_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="text-amber-600 mt-0.5">Tempo: {{ $inv->due_at?->format('d/m/Y') ?? '-' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($inv->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Lunas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                @click="openDetail({{ Js::from($modalPayload) }})"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                                            Ringkasan
                                        </button>

                                        @if ($inv->status === 'unpaid')
                                            <form method="POST" action="{{ route('saas-admin.invoices.mark-paid', $inv) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Tandai tagihan {{ $inv->invoice_number }} sebagai LUNAS dan aktifkan langganan usaha ini?')"
                                                        class="inline-flex items-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition cursor-pointer">
                                                    ✓ Tandai Lunas
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal Ringkasan Tagihan (Khusus Superadmin) --}}
    <div x-show="detailModal" x-cloak
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl" @click.outside="detailModal = false">
            <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Ringkasan Tagihan SaaS</span>
                    <h3 class="font-bold text-gray-900 text-lg font-mono mt-0.5" x-text="selectedInvoice?.invoice_number"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="selectedInvoice?.status === 'paid'"
                          class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                        LUNAS
                    </span>
                    <span x-show="selectedInvoice?.status !== 'paid'"
                          class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        BELUM BAYAR
                    </span>
                    <button type="button" @click="detailModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none ml-1">&times;</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 rounded-2xl p-4 border border-gray-100">
                <div>
                    <span class="text-gray-400 block">Nama Usaha (Tenant)</span>
                    <span class="font-bold text-gray-900 text-sm block mt-0.5" x-text="selectedInvoice?.tenant_name"></span>
                    <span class="font-mono text-[11px] text-gray-500" x-text="selectedInvoice?.tenant_slug"></span>
                </div>
                <div>
                    <span class="text-gray-400 block">Kontak & Alamat</span>
                    <span class="font-semibold text-gray-800 block mt-0.5" x-text="selectedInvoice?.tenant_phone"></span>
                    <span class="text-[11px] text-gray-500 line-clamp-2" x-text="selectedInvoice?.tenant_address"></span>
                </div>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                    <span class="text-gray-500">Paket & Siklus Langganan</span>
                    <span class="font-bold text-gray-900" x-text="(selectedInvoice?.plan_name || '') + ' — ' + (selectedInvoice?.cycle_label || '')"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                    <span class="text-gray-500">Total Nominal Tagihan</span>
                    <span class="font-black text-blue-600 text-sm" x-text="selectedInvoice?.amount_fmt"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                    <span class="text-gray-500">Metode Pembayaran</span>
                    <span class="font-semibold text-gray-800" x-text="selectedInvoice?.payment_method"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                    <span class="text-gray-500">Referensi Payment Gateway</span>
                    <span class="font-mono font-semibold text-gray-700" x-text="selectedInvoice?.provider_ref"></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100">
                    <span class="text-gray-500">Tanggal Dibuat</span>
                    <span class="text-gray-700" x-text="selectedInvoice?.created_fmt"></span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-gray-500">Waktu Pelunasan</span>
                    <span :class="selectedInvoice?.status === 'paid' ? 'text-emerald-600 font-bold' : 'text-amber-600 font-medium'"
                          x-text="selectedInvoice?.paid_fmt"></span>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" @click="detailModal = false"
                        class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer">
                    Tutup
                </button>

                <template x-if="selectedInvoice && selectedInvoice.status === 'unpaid'">
                    <form method="POST" :action="'/saas-admin/invoices/' + selectedInvoice.id + '/mark-paid'">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                            ✓ Konfirmasi Lunas Sekarang
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
