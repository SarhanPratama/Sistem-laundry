@extends('layouts.app')

@section('title', 'Kelola Paket & Harga - SaaS Admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
         createModal: false,
         editModal: false,
         editingPlan: { id: null, name: '', description: '', price_monthly: 0, price_yearly: 0, max_outlets: 1, max_staff: 5, features_text: '' },
         openEdit(plan) {
             this.editingPlan = {
                 id: plan.id,
                 name: plan.name,
                 description: plan.description || '',
                 price_monthly: plan.price_monthly,
                 price_yearly: plan.price_yearly,
                 max_outlets: plan.max_outlets,
                 max_staff: plan.max_staff,
                 features_text: (plan.features || []).join('\n')
             };
             this.editModal = true;
         }
     }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kelola Paket & Harga Langganan</h1>
            <p class="text-xs text-gray-500 mt-0.5">Perubahan harga, kuota staf, dan daftar fitur di halaman ini langsung tampil di Landing Page (#harga) & halaman Registrasi.</p>
        </div>

        <button type="button" @click="createModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition cursor-pointer">
            <span>+ Tambah Paket Baru</span>
        </button>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse ($plans as $plan)
            <div class="bg-white rounded-3xl border {{ $plan->is_active ? 'border-gray-200' : 'border-gray-200 opacity-65' }} p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-black text-gray-900">{{ $plan->name }}</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p class="text-[11px] font-mono text-gray-400 mt-0.5">slug: {{ $plan->slug }}</p>
                        </div>

                        <span class="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-700 font-bold text-[11px]">
                            {{ $plan->subscriptions_count }} Langganan
                        </span>
                    </div>

                    <p class="mt-3 text-xs text-gray-600 leading-relaxed">{{ $plan->description ?: 'Belum ada deskripsi paket.' }}</p>

                    {{-- Pricing Box --}}
                    <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-100 p-4 space-y-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-[11px] text-gray-500 font-medium">Harga Bulanan</span>
                            <span class="text-base font-black text-gray-900">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}<span class="text-[11px] font-normal text-gray-500">/bln</span></span>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-slate-200/70 pt-2">
                            <span class="text-[11px] text-gray-500 font-medium">Harga Tahunan</span>
                            <span class="text-sm font-bold text-blue-600">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}<span class="text-[11px] font-normal text-gray-500">/thn</span></span>
                        </div>
                    </div>

                    {{-- Quotas --}}
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-3 py-2">
                            <span class="text-[10px] text-gray-400 block uppercase font-bold">Maks. Outlet</span>
                            <span class="font-bold text-gray-800">{{ $plan->max_outlets }} Cabang</span>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-3 py-2">
                            <span class="text-[10px] text-gray-400 block uppercase font-bold">Maks. Staf</span>
                            <span class="font-bold text-gray-800">{{ $plan->max_staff >= 100 ? 'Unlimited (' . $plan->max_staff . ')' : $plan->max_staff . ' Akun' }}</span>
                        </div>
                    </div>

                    {{-- Features List --}}
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Fitur Paket:</p>
                        <ul class="space-y-1.5 text-xs text-gray-600">
                            @forelse ($plan->features ?? [] as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @empty
                                <li class="text-gray-400 italic">Belum ada poin fitur.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                    <button type="button"
                            @click="openEdit({{ Js::from($plan) }})"
                            class="flex-1 py-2 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer">
                        Edit Paket & Harga
                    </button>

                    <form method="POST" action="{{ route('saas-admin.plans.toggle', $plan) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="py-2 px-3 rounded-xl border {{ $plan->is_active ? 'border-gray-200 text-gray-600 hover:bg-gray-50' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} font-bold text-xs transition cursor-pointer">
                            {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="md:col-span-3 bg-white rounded-3xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
                Belum ada paket langganan yang terdaftar.
            </div>
        @endforelse
    </div>

    {{-- Modal Edit Paket --}}
    <div x-show="editModal" x-cloak
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto" @click.outside="editModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Edit Paket Langganan</h3>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form method="POST" :action="'/saas-admin/plans/' + editingPlan.id" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Paket <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editingPlan.name" required
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                    <input type="text" name="description" x-model="editingPlan.description"
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Bulanan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price_monthly" x-model="editingPlan.price_monthly" required min="0" step="1000"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Tahunan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price_yearly" x-model="editingPlan.price_yearly" required min="0" step="1000"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Maksimal Outlet <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_outlets" x-model="editingPlan.max_outlets" required min="1"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Maksimal Akun Staf <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_staff" x-model="editingPlan.max_staff" required min="1"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Daftar Fitur (1 baris = 1 fitur)</label>
                    <textarea name="features_text" x-model="editingPlan.features_text" rows="5"
                              placeholder="Order & Nota Digital Unlimited&#10;Manajemen Rak & Status Cucian&#10;Cetak Struk Thermal"
                              class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Paket Baru --}}
    <div x-show="createModal" x-cloak
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto" @click.outside="createModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Tambah Paket Langganan Baru</h3>
                <button type="button" @click="createModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('saas-admin.plans.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Paket <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Enterprise"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Slug (Opsional)</label>
                        <input type="text" name="slug" placeholder="enterprise"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                    <input type="text" name="description" placeholder="Cocok untuk jaringan waralaba laundry skala besar."
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Bulanan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price_monthly" required min="0" step="1000" value="499000"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Tahunan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price_yearly" required min="0" step="1000" value="4990000"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Maksimal Outlet <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_outlets" required min="1" value="10"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Maksimal Akun Staf <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_staff" required min="1" value="999"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Daftar Fitur (1 baris = 1 fitur)</label>
                    <textarea name="features_text" rows="4"
                              placeholder="Semua Fitur Paket Pro&#10;Dukungan Prioritas 24/7&#10;Ekspor Laporan Lengkap"
                              class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                        Tambah Paket
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

