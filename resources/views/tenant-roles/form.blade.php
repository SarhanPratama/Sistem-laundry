@extends('layouts.app')

@section('title', $role ? 'Edit Role' : 'Buat Role')
@section('page-title', $role ? 'Edit Role' : 'Buat Role Baru')

@section('content')
@php
    $initialPermissions = array_values(old('permissions', $role?->permissions ?? []));

    $permissionMeta = [
        'view-dashboard' => [
            'title' => $permissions['view-dashboard'] ?? 'Melihat dashboard',
            'desc' => 'Membuka halaman ringkasan operasional, antrean kerja, dan statistik harian.',
            'badge' => 'Dashboard',
        ],
        'manage-customers' => [
            'title' => $permissions['manage-customers'] ?? 'Mengelola pelanggan',
            'desc' => 'Menambah pelanggan baru, memperbarui kontak, dan melihat riwayat cucian pelanggan.',
            'badge' => 'Pelanggan',
        ],
        'manage-orders' => [
            'title' => $permissions['manage-orders'] ?? 'Mengelola order',
            'desc' => 'Membuat order baru, mencetak struk thermal, kirim nota WA, dan membatalkan pesanan.',
            'badge' => 'Order & Struk',
        ],
        'manage-payments' => [
            'title' => $permissions['manage-payments'] ?? 'Mengelola pembayaran',
            'desc' => 'Mencatat pembayaran tunai/transfer, hitung kembalian otomatis, DP, dan pelunasan.',
            'badge' => 'Kasir & Bayar',
        ],
        'update-order-status' => [
            'title' => $permissions['update-order-status'] ?? 'Memperbarui status order',
            'desc' => 'Memajukan tahap pengerjaan (Dicuci, Dikeringkan, Disetrika, Siap Diambil, Selesai).',
            'badge' => 'Status Produksi',
        ],
        'manage-racks' => [
            'title' => $permissions['manage-racks'] ?? 'Mengelola rak',
            'desc' => 'Mengatur master lokasi rak penyimpanan dan penempatan cucian siap diambil.',
            'badge' => 'Lokasi Rak',
        ],
        'manage-services' => [
            'title' => $permissions['manage-services'] ?? 'Mengelola layanan dan kategori',
            'desc' => 'Menambah serta mengatur harga layanan cucian (Kg/Pcs) dan kategori layanan.',
            'badge' => 'Katalog Layanan',
        ],
        'view-reports' => [
            'title' => $permissions['view-reports'] ?? 'Melihat dan mengekspor laporan',
            'desc' => 'Menganalisis laporan pendapatan kas, piutang, serta mengekspor laporan PDF/Excel.',
            'badge' => 'Laporan Keuangan',
        ],
        'export-data' => [
            'title' => $permissions['export-data'] ?? 'Mengekspor data usaha',
            'desc' => 'Mengunduh arsip data CSV untuk pelanggan, riwayat order, dan transaksi pembayaran.',
            'badge' => 'Ekspor CSV',
        ],
        'manage-users' => [
            'title' => $permissions['manage-users'] ?? 'Mengelola pengguna',
            'desc' => 'Menambah akun staf cabang, mengatur status aktif staf, dan konfigurasi role.',
            'badge' => 'Staf & Role',
        ],
        'manage-settings' => [
            'title' => $permissions['manage-settings'] ?? 'Mengelola pengaturan usaha',
            'desc' => 'Mengubah identitas toko, nomor WhatsApp, rekening transfer, dan catatan struk.',
            'badge' => 'Pengaturan Toko',
        ],
    ];

    $permissionGroups = [
        [
            'id' => 'front_office',
            'title' => 'Operasional Kasir & Pelanggan',
            'subtitle' => 'Akses pelayanan meja depan (front-desk), penerimaan order, dan pembayaran kasir.',
            'accent' => 'blue',
            'keys' => ['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments'],
        ],
        [
            'id' => 'production',
            'title' => 'Produksi & Penyimpanan Rak',
            'subtitle' => 'Akses area workshop pencucian, pengeringan, setrika, hingga penataan rak.',
            'accent' => 'purple',
            'keys' => ['update-order-status', 'manage-racks'],
        ],
        [
            'id' => 'back_office',
            'title' => 'Manajemen Usaha, Layanan & Laporan',
            'subtitle' => 'Akses pengawasan bisnis, daftar harga layanan, laporan keuangan, dan staf.',
            'accent' => 'emerald',
            'keys' => ['manage-services', 'view-reports', 'export-data', 'manage-users', 'manage-settings'],
        ],
    ];
@endphp

<div class="mx-auto max-w-7xl"
     x-data="{
         roleName: @js(old('name', $role?->name ?? '')),
         selected: @js($initialPermissions),
         allKeys: @js(array_keys($permissions)),
         meta: @js($permissionMeta),
         toggle(key) {
             if (this.selected.includes(key)) {
                 this.selected = this.selected.filter(k => k !== key);
             } else {
                 this.selected.push(key);
             }
         },
         has(key) {
             return this.selected.includes(key);
         },
         toggleGroup(keys) {
             const allSelected = keys.every(k => this.selected.includes(k));
             if (allSelected) {
                 this.selected = this.selected.filter(k => !keys.includes(k));
             } else {
                 keys.forEach(k => {
                     if (!this.selected.includes(k)) this.selected.push(k);
                 });
             }
         },
         isGroupAllSelected(keys) {
             return keys.every(k => this.selected.includes(k));
         },
         applyPreset(type) {
             if (type === 'kasir') {
                 if (!this.roleName) this.roleName = 'Kasir Front-Desk';
                 this.selected = ['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'update-order-status', 'export-data'];
             } else if (type === 'operator') {
                 if (!this.roleName) this.roleName = 'Operator Produksi';
                 this.selected = ['view-dashboard', 'manage-orders', 'update-order-status', 'manage-racks'];
             } else if (type === 'supervisor') {
                 if (!this.roleName) this.roleName = 'Supervisor Toko';
                 this.selected = ['view-dashboard', 'manage-customers', 'manage-orders', 'manage-payments', 'update-order-status', 'manage-racks', 'manage-services', 'view-reports', 'export-data'];
             } else if (type === 'all') {
                 this.selected = [...this.allKeys];
             } else {
                 this.selected = [];
             }
         }
     }">

    {{-- Top Navigation Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('tenant-roles.index') }}"
               class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-800 hover:border-gray-300 transition shadow-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $role ? 'Edit Role & Hak Akses' : 'Buat Role Baru' }}</h1>
                    @if ($role?->is_system)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Role Bawaan
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Konfigurasikan nama jabatan staf dan modul fitur yang diizinkan.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $role ? route('tenant-roles.update', $role) : route('tenant-roles.store') }}">
        @csrf
        @if ($role) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: Sticky Role Identity, Presets & Live Summary (4/12) --}}
            <div class="lg:col-span-4 lg:sticky lg:top-6 space-y-5">

                {{-- Card 1: Identitas Role --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Identitas Role</h2>
                            <p class="text-xs text-gray-500">Nama jabatan untuk akun staf</p>
                        </div>
                    </div>

                    <div>
                        <label for="role-name" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input id="role-name"
                               name="name"
                               type="text"
                               x-model="roleName"
                               value="{{ old('name', $role?->name) }}"
                               placeholder="Contoh: Supervisor Cabang, Kurir..."
                               required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        @error('name')
                            <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quick Template Presets --}}
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Template Cepat</span>
                            <button type="button" @click="applyPreset('none')" class="text-xs font-semibold text-gray-400 hover:text-red-600 transition">
                                Reset Semua
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="applyPreset('kasir')"
                                    class="px-3 py-2 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50/60 text-left transition group">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-blue-700">Set Kasir</p>
                                <p class="text-[11px] text-gray-400">6 modul front-desk</p>
                            </button>
                            <button type="button" @click="applyPreset('operator')"
                                    class="px-3 py-2 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50/60 text-left transition group">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-purple-700">Set Operator</p>
                                <p class="text-[11px] text-gray-400">4 modul produksi</p>
                            </button>
                            <button type="button" @click="applyPreset('supervisor')"
                                    class="px-3 py-2 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/60 text-left transition group">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-emerald-700">Supervisor</p>
                                <p class="text-[11px] text-gray-400">9 modul pengawas</p>
                            </button>
                            <button type="button" @click="applyPreset('all')"
                                    class="px-3 py-2 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/60 text-left transition group">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-indigo-700">Pilih Semua</p>
                                <p class="text-[11px] text-gray-400">{{ count($permissions) }} modul penuh</p>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Ringkasan Akses & Tombol Simpan --}}
                <div class="bg-[#0B1F3A] text-white rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-300">Ringkasan Akses</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-white/10 text-white"
                              x-text="selected.length + ' / ' + allKeys.length + ' Aktif'"></span>
                    </div>

                    <p class="text-lg font-bold truncate" x-text="roleName ? roleName : 'Role Tanpa Nama'"></p>

                    {{-- Progress bar --}}
                    <div class="w-full bg-white/10 rounded-full h-2 mt-3 mb-4 overflow-hidden">
                        <div class="bg-sky-400 h-2 rounded-full transition-all duration-300"
                             :style="'width: ' + Math.round((selected.length / allKeys.length) * 100) + '%'"></div>
                    </div>

                    {{-- Active badges preview --}}
                    <div class="min-h-[60px] mb-6">
                        <template x-if="selected.length === 0">
                            <p class="text-xs text-slate-400 italic">Belum ada tugas yang dipilih. Silakan aktifkan modul di sebelah kanan.</p>
                        </template>
                        <div class="flex flex-wrap gap-1.5" x-show="selected.length > 0">
                            <template x-for="key in selected" :key="key">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/10 text-sky-100 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                    <span x-text="meta[key] ? meta[key].badge : key"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5">
                        <button type="submit"
                                class="w-full py-3 px-5 rounded-xl bg-[#087FB8] hover:bg-sky-500 text-white font-bold text-sm transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Role
                        </button>
                        <a href="{{ route('tenant-roles.index') }}"
                           class="w-full py-2.5 px-5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-200 font-semibold text-sm text-center transition">
                            Batal
                        </a>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Grouped Permission Modules with Toggle Switches (8/12) --}}
            <div class="lg:col-span-8 space-y-6">

                @foreach ($permissionGroups as $group)
                    @php
                        $groupKeys = array_values(array_filter($group['keys'], fn ($k) => isset($permissions[$k])));
                        $headerColors = [
                            'blue' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'purple' => 'bg-purple-50 text-purple-600 border-purple-100',
                            'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                        ];
                        $badgeColor = $headerColors[$group['accent']] ?? $headerColors['blue'];
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        {{-- Module Group Header --}}
                        <div class="px-6 py-4 bg-gray-50/70 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl border {{ $badgeColor }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                    @if ($group['id'] === 'front_office')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    @elseif ($group['id'] === 'production')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">{{ $group['title'] }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $group['subtitle'] }}</p>
                                </div>
                            </div>

                            <button type="button"
                                    @click="toggleGroup(@js($groupKeys))"
                                    class="self-start sm:self-center px-3 py-1.5 rounded-lg text-xs font-bold border transition whitespace-nowrap"
                                    :class="isGroupAllSelected(@js($groupKeys))
                                        ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'
                                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100'">
                                <span x-text="isGroupAllSelected(@js($groupKeys)) ? 'Batalkan Grup Ini' : 'Pilih Semua Grup'"></span>
                            </button>
                        </div>

                        {{-- Permission Items List --}}
                        <div class="divide-y divide-gray-100">
                            @foreach ($groupKeys as $key)
                                @php
                                    $item = $permissionMeta[$key] ?? ['title' => $permissions[$key], 'desc' => '', 'badge' => $key];
                                @endphp
                                <div @click="toggle('{{ $key }}')"
                                     class="p-5 flex items-center justify-between gap-4 cursor-pointer transition select-none"
                                     :class="has('{{ $key }}') ? 'bg-blue-50/40 hover:bg-blue-50/70' : 'bg-white hover:bg-gray-50'">

                                    {{-- Hidden real checkbox for form submission --}}
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $key }}"
                                           class="sr-only"
                                           :checked="has('{{ $key }}')">

                                    <div class="flex items-start gap-3.5 min-w-0">
                                        <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 transition-colors"
                                             :class="has('{{ $key }}') ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-400'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-sm font-bold transition-colors"
                                                   :class="has('{{ $key }}') ? 'text-blue-950' : 'text-gray-800'">
                                                    {{ $item['title'] }}
                                                </p>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide"
                                                      :class="has('{{ $key }}') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                                                    {{ $item['badge'] }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                                        </div>
                                    </div>

                                    {{-- Modern Toggle Switch --}}
                                    <div class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
                                         :class="has('{{ $key }}') ? 'bg-blue-600' : 'bg-gray-200'">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                              :class="has('{{ $key }}') ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </form>
</div>
@endsection
