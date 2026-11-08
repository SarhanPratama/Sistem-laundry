@extends('layouts.app')

@section('title', 'Kelola Tenants Usaha - SaaS Admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="{ extendModal: false, resetModal: false, selectedTenant: null }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Usaha Laundry (Tenants)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola seluruh pelanggan bisnis, perpanjang masa aktif, reset password owner, atau pulihkan data usaha.</p>
        </div>

        <a href="{{ route('saas-admin.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-xs">
            &larr; Kembali ke Dashboard SaaS
        </a>
    </div>

    {{-- Tabs: Tenant Aktif vs Terhapus & Filter Status --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200 text-xs">
            <a href="{{ route('saas-admin.tenants', ['tab' => 'active']) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition {{ ($tab ?? 'active') !== 'trashed' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                Tenant Aktif
            </a>
            <a href="{{ route('saas-admin.tenants', ['tab' => 'trashed']) }}"
               class="px-3.5 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ ($tab ?? 'active') === 'trashed' ? 'bg-white text-rose-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <span>Terhapus</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($tab ?? 'active') === 'trashed' ? 'bg-rose-100 text-rose-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $trashedCount ?? 0 }}
                </span>
            </a>
        </div>

        @if (($tab ?? 'active') !== 'trashed')
            <div class="inline-flex flex-wrap items-center gap-1.5 text-xs">
                <a href="{{ route('saas-admin.tenants') }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ empty($status) ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    Semua
                </a>
                <a href="{{ route('saas-admin.tenants', ['status' => 'active']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ ($status ?? '') === 'active' ? 'bg-emerald-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    Aktif
                </a>
                <a href="{{ route('saas-admin.tenants', ['status' => 'trial']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ ($status ?? '') === 'trial' ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    Trial
                </a>
                <a href="{{ route('saas-admin.tenants', ['status' => 'past_due']) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition {{ ($status ?? '') === 'past_due' ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    Past Due
                </a>
            </div>
        @endif
    </div>

    {{-- Tenants DataTable Card --}}
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        @if ($tenants->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="text-base font-medium">Tidak ada data usaha laundry yang ditemukan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Usaha / Slug</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Pemilik & Kontak</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Paket & Masa Aktif</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Staf / Order</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider no-sort">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($tenants as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="font-bold text-gray-900 text-sm">{{ $t->name }}</p>
                                    <p class="text-gray-400 font-mono text-xs mt-0.5">{{ $t->slug }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-600">
                                    <p class="font-semibold text-gray-900">{{ $t->owner?->name ?? '-' }} <span class="text-gray-400 font-normal">({{ $t->owner?->username ?? '-' }})</span></p>
                                    <p class="text-gray-400 text-xs">{{ $t->phone ?? '-' }} • {{ $t->owner?->email ?? '-' }}</p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php
                                        $sub = $t->activeSubscription;
                                    @endphp
                                    <p class="font-semibold text-blue-600 text-sm">Paket {{ $sub?->plan?->name ?? 'Business' }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5">
                                        @if ($sub?->ends_at)
                                            Berakhir: {{ $sub->ends_at->format('d/m/Y') }}
                                        @elseif ($t->trial_ends_at)
                                            Trial s/d: {{ $t->trial_ends_at->format('d/m/Y') }}
                                        @else
                                            Menunggu Aktivasi
                                        @endif
                                    </p>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-center text-sm text-gray-700">
                                    <span class="font-bold">{{ $t->users_count }}</span> staf /
                                    <span class="font-bold">{{ $t->orders_count }}</span> order
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($t->trashed())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Terhapus
                                        </span>
                                    @elseif ($t->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    @elseif ($t->status === 'trial')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            Trial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ strtoupper($t->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="inline-flex flex-wrap items-center justify-center gap-1.5">
                                        @if (($tab ?? 'active') === 'trashed')
                                            <form method="POST" action="{{ route('saas-admin.tenants.restore', $t->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition cursor-pointer">
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                    @click="extendModal = true; selectedTenant = { id: {{ $t->id }}, name: '{{ addslashes($t->name) }}' }"
                                                    class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition cursor-pointer">
                                                + Perpanjang
                                            </button>

                                            <button type="button"
                                                    @click="resetModal = true; selectedTenant = { id: {{ $t->id }}, name: '{{ addslashes($t->name) }}', username: '{{ addslashes($t->owner?->username ?? 'owner') }}' }"
                                                    class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs transition cursor-pointer">
                                                Reset Password
                                            </button>

                                            <form method="POST" action="{{ route('saas-admin.tenants.suspend', $t) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        onclick="return confirm('Apakah Anda yakin ingin mengubah status aktif usaha ini?')"
                                                        class="px-2.5 py-1.5 rounded-lg {{ $t->status === 'suspended' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }} font-semibold text-xs transition cursor-pointer">
                                                    {{ $t->status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('saas-admin.tenants.destroy', $t) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Pindahkan usaha {{ addslashes($t->name) }} ke daftar Terhapus?')"
                                                        class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs transition cursor-pointer">
                                                    Hapus
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

    {{-- Modal Perpanjang Langganan --}}
    <div x-show="extendModal" x-cloak
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.outside="extendModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Perpanjang Akses Usaha</h3>
                <button type="button" @click="extendModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form method="POST" :action="'/saas-admin/tenants/' + (selectedTenant ? selectedTenant.id : '') + '/extend'" class="space-y-4">
                @csrf
                <div>
                    <p class="text-xs text-gray-500">Usaha Laundry:</p>
                    <p class="font-bold text-sm text-gray-900" x-text="selectedTenant ? selectedTenant.name : ''"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Paket:</label>
                    <select name="plan_id" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs">
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->price_monthly, 0, ',', '.') }}/bln)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Durasi Perpanjangan:</label>
                    <select name="months" required class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs">
                        <option value="1">1 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="12">12 Bulan (1 Tahun)</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="extendModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                        Konfirmasi & Perpanjang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reset Password Owner --}}
    <div x-show="resetModal" x-cloak
         class="fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl" @click.outside="resetModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Reset Password Pemilik Usaha</h3>
                <button type="button" @click="resetModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form method="POST" :action="'/saas-admin/tenants/' + (selectedTenant ? selectedTenant.id : '') + '/reset-password'" class="space-y-4">
                @csrf
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                    <p>Usaha: <strong x-text="selectedTenant ? selectedTenant.name : ''"></strong></p>
                    <p class="mt-0.5">Username Login Owner: <strong class="font-mono" x-text="selectedTenant ? selectedTenant.username : ''"></strong></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password Baru <span class="text-rose-500">*</span></label>
                    <input type="text" name="new_password" required minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Gunakan kombinasi huruf & angka, lalu informasikan ke pemilik usaha.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="resetModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
