@extends('layouts.app')

@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('content')
<div class="mx-auto"
     x-data="{
         search: '',
         modalOpen: false,
         formMode: 'create',
         formAction: '{{ route('customers.store') }}',
         formCustomerId: '',
         formData: { full_name: '', phone_number: '', address: '' },
         openCreate() {
             this.formMode = 'create';
             this.formAction = '{{ route('customers.store') }}';
             this.formCustomerId = '';
             this.formData = { full_name: '', phone_number: '', address: '' };
             this.modalOpen = true;
         },
         openEdit(customer) {
             this.formMode = 'edit';
             this.formAction = '{{ url('/customers') }}/' + customer.id;
             this.formCustomerId = customer.id;
             this.formData = {
                 full_name: customer.full_name,
                 phone_number: customer.phone_number,
                 address: customer.address || ''
             };
             this.modalOpen = true;
         },
         closeModal() {
             this.modalOpen = false;
         }
     }"
     x-init="
         @if ($errors->any())
             formMode = @js(old('_form_mode', 'create'));
             formCustomerId = @js(old('_customer_id', ''));
             formAction = formMode === 'edit'
                 ? '{{ url('/customers') }}/' + formCustomerId
                 : '{{ route('customers.store') }}';
             formData = {
                 full_name: @js(old('full_name', '')),
                 phone_number: @js(old('phone_number', '')),
                 address: @js(old('address', ''))
             };
             modalOpen = true;
         @endif
     "
     @keydown.escape.window="closeModal()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pelanggan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data pelanggan laundry.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <form method="GET" action="{{ route('customers.index') }}" class="relative w-full sm:w-auto" x-on:submit.prevent>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari pelanggan..."
                    class="pl-9 pr-10 py-2.5 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64"
                    x-model.debounce="search" />
                @if ($search)
                    <a href="{{ route('customers.index', ($showTrashed ?? false) ? ['trashed' => 1] : []) }}"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
            <button type="button" @click="openCreate()"
                class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm w-full sm:w-auto">
                Tambah Pelanggan
            </button>
        </div>
    </div>

    {{-- Tab Filter Aktif vs Terhapus --}}
    <div class="mb-5 flex items-center gap-2 border-b border-gray-200">
        <a href="{{ route('customers.index') }}"
           class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ !($showTrashed ?? false) ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Data Aktif
        </a>
        <a href="{{ route('customers.index', ['trashed' => 1]) }}"
           class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ ($showTrashed ?? false) ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Terhapus
            @if (($trashedCount ?? 0) > 0)
                <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold {{ ($showTrashed ?? false) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $trashedCount }}
                </span>
            @endif
        </a>
    </div>

    @if ($customers->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <svg class="mx-auto h-14 w-14 mb-4 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-base font-medium text-gray-500">
                {{ ($showTrashed ?? false) ? 'Tidak ada data pelanggan yang dihapus.' : ($search ? 'Tidak ada pelanggan yang cocok.' : 'Belum ada pelanggan.') }}
            </p>
            @if(!$search && !($showTrashed ?? false))
                <p class="text-sm mt-1">Klik "Tambah Pelanggan" untuk memulai.</p>
            @endif
        </div>
    @else

        {{-- Customer Card Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($customers as $customer)
                @php
                    // Generate initials from name
                    $words    = explode(' ', trim($customer->full_name));
                    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                    // Avatar color based on first letter
                    $colors = [
                        'A' => 'bg-purple-100 text-purple-600',
                        'B' => 'bg-blue-100 text-blue-600',
                        'C' => 'bg-cyan-100 text-cyan-600',
                        'D' => 'bg-indigo-100 text-indigo-600',
                        'E' => 'bg-emerald-100 text-emerald-600',
                        'F' => 'bg-fuchsia-100 text-fuchsia-600',
                        'G' => 'bg-green-100 text-green-600',
                        'H' => 'bg-orange-100 text-orange-600',
                        'I' => 'bg-sky-100 text-sky-600',
                        'J' => 'bg-violet-100 text-violet-600',
                        'K' => 'bg-teal-100 text-teal-600',
                        'L' => 'bg-lime-100 text-lime-600',
                        'M' => 'bg-rose-100 text-rose-600',
                        'N' => 'bg-amber-100 text-amber-600',
                        'O' => 'bg-pink-100 text-pink-600',
                        'P' => 'bg-blue-100 text-blue-600',
                        'Q' => 'bg-purple-100 text-purple-600',
                        'R' => 'bg-red-100 text-red-600',
                        'S' => 'bg-green-100 text-green-600',
                        'T' => 'bg-teal-100 text-teal-600',
                        'U' => 'bg-indigo-100 text-indigo-600',
                        'V' => 'bg-violet-100 text-violet-600',
                        'W' => 'bg-cyan-100 text-cyan-600',
                        'X' => 'bg-orange-100 text-orange-600',
                        'Y' => 'bg-yellow-100 text-yellow-600',
                        'Z' => 'bg-emerald-100 text-emerald-600',
                    ];
                    $firstLetter = strtoupper(substr($customer->full_name, 0, 1));
                    $avatarColor = $colors[$firstLetter] ?? 'bg-gray-100 text-gray-600';

                    // Format total spent
                    $totalSpent = $customer->orders_sum_total_cost ?? 0;
                    if ($totalSpent >= 1_000_000) {
                        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000_000, 1, '.', '') . 'M';
                    } elseif ($totalSpent >= 1_000) {
                        $spentFormatted = 'Rp ' . number_format($totalSpent / 1_000, 0, '.', '') . 'K';
                    } else {
                        $spentFormatted = 'Rp ' . number_format($totalSpent, 0, ',', '.');
                    }
                @endphp

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden"
                     x-show="search === '' || $el.dataset.customerName.toLowerCase().includes(search.toLowerCase()) || $el.dataset.customerPhone.includes(search)"
                     data-customer-name="{{ $customer->full_name }}"
                     data-customer-phone="{{ $customer->phone_number }}">

                    {{-- Card Header --}}
                    <div class="p-5 pb-4">
                        <div class="flex items-center gap-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-full {{ $avatarColor }} flex items-center justify-center flex-shrink-0 text-base font-bold">
                                {{ $initials }}
                            </div>
                            {{-- Name & Phone --}}
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $customer->full_name }}</h3>
                                    @if (!$customer->is_active)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 flex-shrink-0">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $customer->phone_number }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Row --}}
                    <div class="mx-5 mb-4 grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-gray-500 font-medium mb-1">Total Order</p>
                            <p class="text-xl font-bold text-gray-900">{{ $customer->orders_count }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-gray-500 font-medium mb-1">Total Pengeluaran</p>
                            <p class="text-xl font-bold text-gray-900 font-mono">{{ $spentFormatted }}</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    @if ($showTrashed ?? false)
                        <div class="border-t border-gray-100 px-5 py-3.5 flex items-center justify-between bg-red-50/40">
                            <span class="text-xs text-red-600 font-medium">
                                Dihapus {{ $customer->deleted_at?->format('d/m/Y H:i') }}
                            </span>
                            <form method="POST" action="{{ route('customers.restore', $customer->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Pulihkan
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="px-5 pb-5 grid grid-cols-2 gap-3">
                            <a href="{{ route('customers.show', $customer) }}"
                               class="flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-150">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Lihat
                            </a>
                            <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}"
                               class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-medium text-white transition duration-150">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Order
                            </a>
                        </div>

                        {{-- Admin actions (edit / deactivate) --}}
                        @role('admin')
                        <div class="border-t border-gray-100 px-5 py-3 flex items-center gap-3 bg-gray-50/50">
                            <button type="button"
                               @click="openEdit(@js(['id' => $customer->id, 'full_name' => $customer->full_name, 'phone_number' => $customer->phone_number, 'address' => $customer->address]))"
                               class="text-xs text-gray-500 hover:text-blue-600 font-medium transition">
                                Edit
                            </button>
                            @if ($customer->is_active)
                                <form method="POST" action="{{ route('customers.deactivate', $customer) }}"
                                      class="inline"
                                      onsubmit="return confirm('Yakin ingin menonaktifkan pelanggan ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs text-gray-500 hover:text-orange-600 font-medium transition">
                                        Nonaktifkan
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Sudah nonaktif</span>
                            @endif
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                  class="inline ml-auto"
                                  onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-600 font-medium transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                        @endrole
                    @endif

                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($customers->hasPages())
            <div class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm px-3 py-3 sm:px-6 sm:py-4 overflow-x-auto">
                {{ $customers->links('pagination::tailwind') }}
            </div>
        @endif

    @endif

    {{-- Modal tambah/edit pelanggan --}}
    <div x-show="modalOpen"
         x-transition
         class="fixed inset-0 z-40 overflow-y-auto"
         style="display: none;"
         role="dialog"
         aria-modal="true"
         aria-labelledby="customer-modal-title">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen"
                 x-transition
                 class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="customer-modal-title" class="text-lg font-bold text-gray-900"
                            x-text="formMode === 'edit' ? 'Edit Pelanggan' : 'Tambah Pelanggan'"></h2>
                        <p class="mt-0.5 text-sm text-gray-500">Lengkapi data pelanggan di bawah ini.</p>
                    </div>
                    <button type="button" @click="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" x-bind:action="formAction" class="p-5 sm:p-6">
                    @csrf
                    <template x-if="formMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="_form_mode" x-bind:value="formMode">
                    <input type="hidden" name="_customer_id" x-bind:value="formCustomerId">

                    <div class="mb-4">
                        <label for="customer-full-name" class="mb-1 block text-sm font-medium text-gray-700">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input id="customer-full-name" type="text" name="full_name" x-model="formData.full_name"
                               class="w-full rounded-xl border {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Masukkan nama lengkap">
                        @error('full_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="customer-phone-number" class="mb-1 block text-sm font-medium text-gray-700">
                            Nomor Telepon <span class="text-red-500">*</span>
                        </label>
                        <input id="customer-phone-number" type="text" name="phone_number" x-model="formData.phone_number"
                               class="w-full rounded-xl border {{ $errors->has('phone_number') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Contoh: 08123456789">
                        @error('phone_number')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="customer-address" class="mb-1 block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="customer-address" name="address" x-model="formData.address" rows="3"
                                  class="w-full rounded-xl border {{ $errors->has('address') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Masukkan alamat (opsional)"></textarea>
                        @error('address')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeModal()"
                                class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                x-text="formMode === 'edit' ? 'Perbarui' : 'Simpan'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
