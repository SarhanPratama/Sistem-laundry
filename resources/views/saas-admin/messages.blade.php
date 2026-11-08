@extends('layouts.app')

@section('title', 'Pesan Masuk & Leads - SaaS Admin')

@section('content')
<script>
    window.saasMessagesManager = function () {
        return {
            replyModalOpen: false,
            activeMsg: {
                id: null,
                name: '',
                email: '',
                business_name: '',
                message: '',
                replied_at: '',
                reply_action: ''
            },
            replySubject: '',
            replyBody: '',
            openReply(msg) {
                if (typeof msg === 'string') {
                    msg = JSON.parse(msg);
                }
                this.activeMsg = msg;
                this.replySubject = msg.reply_subject || ('Re: Pertanyaan Laundiqo — ' + msg.name);
                this.replyBody = msg.reply_message || (
                    'Halo Kak ' + msg.name + (msg.business_name ? ' (' + msg.business_name + ')' : '') + ',\n\n' +
                    'Terima kasih telah menghubungi tim Laundiqo.\n\n' +
                    'Menanggapi pertanyaan Anda:\n'
                );
                this.replyModalOpen = true;
            },
            applyTemplate(type) {
                const greeting = 'Halo Kak ' + this.activeMsg.name + (this.activeMsg.business_name ? ' (' + this.activeMsg.business_name + ')' : '') + ',\n\n';
                if (type === 'trial') {
                    this.replySubject = 'Informasi Paket & Coba Gratis 14 Hari — Laundiqo';
                    this.replyBody = greeting +
                        'Terima kasih atas ketertarikan Anda pada Laundiqo.\n\n' +
                        'Anda dapat langsung mencoba seluruh fitur operasional Laundiqo (Kasir, Antrean Operator, Tracking Pelanggan, hingga Laporan Keuangan) secara GRATIS selama 14 hari tanpa biaya di muka.\n\n' +
                        'Silakan daftar langsung melalui halaman registrasi kami, dan akun usaha laundry Anda akan langsung aktif detik itu juga.\n\n' +
                        'Jika ada hal yang ingin ditanyakan lebih lanjut, silakan balas email ini.\n\n' +
                        'Salam hangat,\nTim Support Laundiqo';
                } else if (type === 'direct') {
                    this.replySubject = 'Panduan Pendaftaran & Aktivasi Langganan — Laundiqo';
                    this.replyBody = greeting +
                        'Terima kasih telah menghubungi Laundiqo.\n\n' +
                        'Untuk langsung mengaktifkan paket berlangganan usaha laundry Anda:\n' +
                        '1. Buka halaman Daftar Usaha (/register).\n' +
                        '2. Pilih paket (Starter / Pro / Business) serta siklus tagihan (Bulanan / Tahunan).\n' +
                        '3. Pilih mode "Langsung Bayar Otomatis" untuk menyelesaikan pembayaran via QRIS, GoPay, DANA, atau Virtual Account.\n\n' +
                        'Setelah pembayaran terverifikasi, akun Anda langsung aktif penuh.\n\n' +
                        'Salam hangat,\nTim Support Laundiqo';
                } else if (type === 'custom') {
                    this.replySubject = 'Konsultasi Kebutuhan Operasional Laundry — Laundiqo';
                    this.replyBody = greeting +
                        'Terima kasih telah berkonsultasi dengan tim Laundiqo.\n\n' +
                        'Menanggapi pertanyaan Anda, sistem Laundiqo sangat mendukung manajemen staf (Admin, Kasir, Operator), pemantauan lokasi rak cucian, serta isolasi data per usaha secara aman.\n\n' +
                        'Apakah Anda berkenan menjadwalkan sesi demo singkat bersama tim kami minggu ini?\n\n' +
                        'Salam hangat,\nTim Support Laundiqo';
                }
            }
        };
    };
</script>

<div class="max-w-7xl mx-auto space-y-6" x-data="saasMessagesManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kotak Masuk Pesan & Calon Mitra (Leads)</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola pertanyaan dari halaman Kontak publik dan kirim balasan resmi langsung ke email pengirim.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-bold text-xs">
                {{ $unreadCount }} Belum Dibaca
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 font-bold text-xs">
                {{ $unrepliedCount ?? 0 }} Belum Dibalas
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-xs">
                {{ $repliedCount ?? 0 }} Sudah Dibalas
            </span>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $tabs = [
                    'all'       => 'Semua Pesan',
                    'unread'    => 'Belum Dibaca (' . $unreadCount . ')',
                    'unreplied' => 'Belum Dibalas (' . ($unrepliedCount ?? 0) . ')',
                    'replied'   => 'Sudah Dibalas (' . ($repliedCount ?? 0) . ')',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <a href="{{ route('saas-admin.messages', array_filter(['filter' => $key, 'search' => $search ?? ''])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filter ?? 'all') === $key ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('saas-admin.messages') }}" class="flex items-center gap-2 w-full sm:w-auto">
            @if (($filter ?? 'all') !== 'all')
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, email, usaha, atau isi pesan..."
                   class="w-full sm:w-64 px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition cursor-pointer">
                Cari
            </button>
            @if (!empty($search) || ($filter ?? 'all') !== 'all')
                <a href="{{ route('saas-admin.messages') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-xs rounded-xl transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Messages List --}}
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse ($messages as $msg)
                @php
                    $modalPayload = [
                        'id'            => $msg->id,
                        'name'          => $msg->name,
                        'email'         => $msg->contact,
                        'business_name' => $msg->business_name ?? '',
                        'message'       => $msg->message,
                        'reply_subject' => $msg->reply_subject ?? '',
                        'reply_message' => $msg->reply_message ?? '',
                        'replied_at'    => $msg->replied_at ? $msg->replied_at->format('d M Y, H:i') : '',
                        'reply_action'  => route('saas-admin.messages.reply', $msg),
                    ];
                @endphp
                <div class="p-5 hover:bg-gray-50/50 transition flex flex-col lg:flex-row lg:items-start justify-between gap-4 {{ !$msg->is_read ? 'bg-blue-50/25' : '' }}">
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-sm text-gray-900">{{ $msg->name }}</span>
                            @if ($msg->business_name)
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                    {{ $msg->business_name }}
                                </span>
                            @endif

                            @if (!$msg->is_read)
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-bold text-[10px] uppercase">
                                    Baru
                                </span>
                            @endif

                            @if ($msg->isReplied())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Sudah Dibalas ({{ $msg->replied_at->diffForHumans() }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10px]">
                                    Menunggu Balasan
                                </span>
                            @endif

                            <span class="text-[11px] text-gray-400">• {{ $msg->created_at->format('d M Y, H:i') }} ({{ $msg->created_at->diffForHumans() }})</span>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="font-semibold text-gray-500">Email Pengirim:</span>
                            <a href="mailto:{{ $msg->contact }}" class="font-bold text-blue-600 hover:underline inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $msg->contact }}
                            </a>
                        </div>

                        {{-- Isi Pertanyaan Pengirim --}}
                        <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3.5 border border-gray-200/80">
                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-1">Pertanyaan Pengirim:</p>
                            <p class="whitespace-pre-line">{{ $msg->message }}</p>
                        </div>

                        {{-- Riwayat Balasan Admin (Jika Sudah Dibalas) --}}
                        @if ($msg->isReplied())
                            <div class="text-xs text-emerald-900 leading-relaxed bg-emerald-50/70 rounded-xl p-3.5 border border-emerald-200/80 mt-2">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">
                                        Balasan Terkirim — {{ $msg->reply_subject }}
                                    </p>
                                    <span class="text-[10px] text-emerald-600 font-semibold">
                                        Oleh {{ $msg->repliedByUser->username ?? 'Superadmin' }} • {{ $msg->replied_at->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <p class="whitespace-pre-line text-gray-700">{{ $msg->reply_message }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap lg:flex-col items-stretch sm:items-end gap-2 shrink-0">
                        <button type="button"
                                data-msg="{{ json_encode($modalPayload) }}"
                                @click="openReply($el.dataset.msg)"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            {{ $msg->isReplied() ? 'Balas Ulang via Email' : 'Balas via Email' }}
                        </button>

                        @if (!$msg->is_read)
                            <form method="POST" action="{{ route('saas-admin.messages.read', $msg) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs transition cursor-pointer">
                                    ✓ Tandai Dibaca
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('saas-admin.messages.destroy', $msg) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Hapus pesan dari {{ addslashes($msg->name) }}?')"
                                    class="w-full px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-gray-400 text-xs">
                    Belum ada pesan masuk yang ditemukan.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $messages->links() }}
        </div>
    </div>

    {{-- MODAL BALAS PESAN VIA EMAIL --}}
    <div x-show="replyModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         style="display: none;">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="replyModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="replyModalOpen"
                 x-transition
                 class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl border border-gray-200 overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-[#0B1F3A] text-white flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-400">BALAS PESAN CALON MITRA</p>
                        <h3 class="text-base font-bold mt-0.5" x-text="'Kirim Email ke ' + activeMsg.name"></h3>
                    </div>
                    <button type="button" @click="replyModalOpen = false" class="p-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="activeMsg.reply_action" class="p-6 space-y-4">
                    @csrf

                    {{-- Info Pengirim & Kutipan Pertanyaan --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-gray-500">Penerima:</span>
                                <span class="font-bold text-gray-900" x-text="activeMsg.name"></span>
                                <span class="font-bold text-blue-600" x-text="'<' + activeMsg.email + '>'"></span>
                            </div>
                            <span x-show="activeMsg.business_name"
                                  class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 font-semibold text-[11px]"
                                  x-text="activeMsg.business_name"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pertanyaan Pengirim:</p>
                            <p class="text-gray-700 mt-0.5 whitespace-pre-line" x-text="activeMsg.message"></p>
                        </div>
                    </div>

                    {{-- Quick Templates --}}
                    <div>
                        <p class="text-[11px] font-bold text-gray-500 mb-1.5">Gunakan Template Balasan Cepat:</p>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="applyTemplate('trial')"
                                    class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-[11px] transition cursor-pointer">
                                + Info Trial Gratis 14 Hari
                            </button>
                            <button type="button" @click="applyTemplate('direct')"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold text-[11px] transition cursor-pointer">
                                + Panduan Langsung Bayar
                            </button>
                            <button type="button" @click="applyTemplate('custom')"
                                    class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold text-[11px] transition cursor-pointer">
                                + Ajakan Sesi Demo
                            </button>
                        </div>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Subjek Email</label>
                        <input type="text" name="reply_subject" x-model="replySubject" required maxlength="200"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    {{-- Reply Body --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Isi Balasan Email</label>
                        <textarea name="reply_message" x-model="replyBody" rows="7" required maxlength="5000"
                                  placeholder="Tulis balasan Anda untuk pengirim..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs text-gray-800 leading-relaxed focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-gray-100">
                        <a :href="'mailto:' + encodeURIComponent(activeMsg.email) + '?subject=' + encodeURIComponent(replySubject) + '&body=' + encodeURIComponent(replyBody)"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Buka di Aplikasi Email (Gmail/Outlook)
                        </a>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="replyModalOpen = false"
                                    class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim Balasan Email
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
