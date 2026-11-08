@extends('marketing.layouts.app')

@section('title', 'Kontak — Laundiqo')

@section('content')
    <section class="marketing-section">
        <div class="marketing-container grid gap-12 lg:grid-cols-2">
            <div>
                <span class="marketing-eyebrow">KONTAK & KONSULTASI</span>
                <h1 class="marketing-heading mt-4">Punya pertanyaan tentang Laundiqo?</h1>
                <p class="marketing-lead">
                    Kirim pertanyaan atau konsultasikan kebutuhan operasional usaha laundry Anda. Tim kami akan membalas pesan Anda langsung ke alamat email yang Anda daftarkan.
                </p>
                <div class="mt-8 space-y-4 text-sm text-slate-600">
                    <p><strong class="text-[#142B49]">Metode Balasan:</strong> Dikirim langsung ke Inbox Email Anda</p>
                    <p><strong class="text-[#142B49]">Jam Layanan:</strong> {{ \App\Models\Setting::getPlatform('platform_contact_hours', 'Senin–Jumat, 09.00–17.00 WIB') }}</p>
                    @if (\App\Models\Setting::getPlatform('platform_contact_email'))
                        <p><strong class="text-[#142B49]">Email Resmi:</strong> {{ \App\Models\Setting::getPlatform('platform_contact_email') }}</p>
                    @endif
                    @if (\App\Models\Setting::getPlatform('platform_contact_whatsapp'))
                        <p><strong class="text-[#142B49]">WhatsApp:</strong> {{ \App\Models\Setting::getPlatform('platform_contact_whatsapp') }}</p>
                    @endif
                    @if (\App\Models\Setting::getPlatform('platform_contact_address'))
                        <p><strong class="text-[#142B49]">Lokasi:</strong> {{ \App\Models\Setting::getPlatform('platform_contact_address') }}</p>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                @if (session('success'))
                    <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start gap-3" role="status">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800" role="alert">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marketing.contact.submit') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required maxlength="120" class="form-input">
                    </div>

                    <div>
                        <label for="contact" class="form-label">Alamat Email Aktif</label>
                        <input id="contact" type="email" name="contact" value="{{ old('contact') }}" placeholder="nama@email.com" required maxlength="120" class="form-input">
                        <p class="mt-1.5 text-xs text-slate-500">
                            Pastikan alamat email aktif karena balasan dari tim Admin akan dikirimkan ke email ini.
                        </p>
                    </div>

                    <div>
                        <label for="business_name" class="form-label">
                            Nama Usaha Laundry <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Contoh: Berkah Clean Laundry" maxlength="120" class="form-input">
                    </div>

                    <div>
                        <label for="message" class="form-label">Pertanyaan / Pesan</label>
                        <textarea id="message" name="message" rows="5" placeholder="Tuliskan pertanyaan Anda seputar fitur, harga paket, atau konsultasi operasional laundry..." required maxlength="2000" class="form-input">{{ old('message') }}</textarea>
                    </div>

                    <button class="marketing-button marketing-button-primary w-full" type="submit">Kirim Pesan</button>
                    <p class="text-xs text-slate-500">
                        Dengan mengirim formulir ini, Anda menyetujui data digunakan untuk menanggapi pertanyaan Anda melalui email.
                    </p>
                </form>
            </div>
        </div>
    </section>
@endsection
