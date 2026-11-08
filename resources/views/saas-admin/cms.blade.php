@extends('layouts.app')

@section('title', 'CMS Web Profile - SaaS Admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
         faqs: {{ Js::from($cms['faqs'] ?? []) }},
         addFaq() {
             this.faqs.push({ question: '', answer: '' });
         },
         removeFaq(index) {
             if (this.faqs.length > 1) {
                 this.faqs.splice(index, 1);
             }
         }
     }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-full mb-1">
                <span>🌐 Pengaturan Konten Website Publik</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">CMS Ringan Web Profile Laundiqo</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola teks Hero utama, masa trial gratis, kontak resmi perusahaan, dan daftar FAQ tanpa mengubah kode program.</p>
        </div>

        <a href="{{ route('marketing.home') }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
            <span>Lihat Halaman Depan</span>
            <span>&nearr;</span>
        </a>
    </div>

    <form method="POST" action="{{ route('saas-admin.cms.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 1. Konten Hero & Promo Trial --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">1. Konten Banner Utama (Hero) & Masa Trial</h2>
                    <p class="text-[11px] text-gray-500">Tampil pada bagian paling atas halaman depan (Landing Page).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Teks Badge Atas (Eyebrow) <span class="text-rose-500">*</span></label>
                    <input type="text" name="hero_badge" required value="{{ old('hero_badge', $cms['hero_badge']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Durasi Free Trial (Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="trial_days" required min="1" max="90" value="{{ old('trial_days', $cms['trial_days']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Utama Hero (Headline) <span class="text-rose-500">*</span></label>
                    <input type="text" name="hero_title" required value="{{ old('hero_title', $cms['hero_title']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Subjudul / Deskripsi Hero <span class="text-rose-500">*</span></label>
                    <textarea name="hero_subtitle" rows="3" required
                              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs leading-relaxed focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('hero_subtitle', $cms['hero_subtitle']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Informasi Kontak & Footer --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-sm font-bold text-gray-900">2. Informasi Kontak Resmi & Footer</h2>
                <p class="text-[11px] text-gray-500">Tampil pada halaman Kontak (/kontak) dan bagian bawah (Footer) seluruh halaman publik.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tagline Footer <span class="text-rose-500">*</span></label>
                    <input type="text" name="tagline" required value="{{ old('tagline', $cms['tagline']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Resmi Support / Sales</label>
                    <input type="text" name="contact_email" value="{{ old('contact_email', $cms['contact_email']) }}"
                           placeholder="support@laundiqo.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp Resmi</label>
                    <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $cms['contact_whatsapp']) }}"
                           placeholder="0812-3456-7890"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Operasional Layanan <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact_hours" required value="{{ old('contact_hours', $cms['contact_hours']) }}"
                           placeholder="Senin–Jumat, 09.00–17.00 WIB"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Lokasi / Alamat Kantor</label>
                    <input type="text" name="contact_address" value="{{ old('contact_address', $cms['contact_address']) }}"
                           placeholder="Jakarta, Indonesia"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
        </div>

        {{-- 3. Daftar FAQ (Tanya Jawab) --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">3. Daftar Pertanyaan Umum (FAQ)</h2>
                    <p class="text-[11px] text-gray-500">Tampil pada bagian #faq di Landing Page.</p>
                </div>
                <button type="button" @click="addFaq()"
                        class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer">
                    + Tambah Baris FAQ
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(faq, index) in faqs" :key="index">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600" x-text="'Pertanyaan #' + (index + 1)"></span>
                            <button type="button" @click="removeFaq(index)" x-show="faqs.length > 1"
                                    class="text-xs font-semibold text-rose-600 hover:underline cursor-pointer">
                                Hapus
                            </button>
                        </div>

                        <div>
                            <input type="text" name="faq_questions[]" x-model="faq.question" required
                                   placeholder="Tulis pertanyaan..."
                                   class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <textarea name="faq_answers[]" x-model="faq.answer" rows="2" required
                                      placeholder="Tulis jawaban..."
                                      class="w-full px-3.5 py-2 rounded-xl border border-gray-300 bg-white text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit"
                    class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition cursor-pointer">
                Simpan Seluruh Konten Web Profile
            </button>
        </div>
    </form>

</div>
@endsection

