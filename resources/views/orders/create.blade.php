@extends('layouts.app')

@section('title', 'Order Baru')
@section('page-title', 'Order Baru')

@section('content')
<div x-data="orderForm({{ $services->toJson() }}, {{ $customers->toJson() }}, {{ request('customer_id') ?: 'null' }}, {{ $categories->toJson() }})">

    {{-- Page header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Order Baru</h1>
            <p class="text-sm text-gray-500 mt-0.5">Buat pesanan laundry baru untuk pelanggan.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('orders.store') }}" @submit="prepareSubmit">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ═══════════════════════════════════════════
                 LEFT / MAIN COLUMN  (2/3)
            ════════════════════════════════════════════ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ── 1. Pilih Pelanggan ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Pilih Pelanggan
                        </div>
                        <a href="{{ route('customers.create') }}"
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                            Pelanggan Baru
                        </a>
                    </div>

                    {{-- Search input --}}
                    <div class="relative mb-3">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               x-model="customerSearch"
                               @input="filterCustomers"
                               @focus="showDropdown = true"
                               @click.outside="showDropdown = false"
                               placeholder="Cari nama atau nomor telepon..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50">

                        {{-- Dropdown --}}
                        <div x-show="showDropdown && filteredCustomers.length > 0 && !selectedCustomer"
                             x-transition
                             class="absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                            <template x-for="c in filteredCustomers.slice(0, 8)" :key="c.id">
                                <button type="button"
                                        @click="selectCustomer(c)"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50 text-left transition">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0"
                                         x-text="initials(c.full_name)"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800" x-text="c.full_name"></p>
                                        <p class="text-xs text-gray-500" x-text="c.phone_number"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Selected customer chip --}}
                    <template x-if="selectedCustomer">
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold flex-shrink-0"
                                     x-text="initials(selectedCustomer.full_name)"></div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800" x-text="selectedCustomer.full_name"></p>
                                    <p class="text-xs text-gray-500" x-text="selectedCustomer.phone_number"></p>
                                </div>
                            </div>
                            <button type="button" @click="clearCustomer()"
                                    class="text-gray-400 hover:text-gray-600 transition p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    {{-- Hidden input --}}
                    <input type="hidden" name="customer_id" :value="selectedCustomer ? selectedCustomer.id : ''">
                    @error('customer_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── 2. Pilih Layanan ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-4">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Pilih Layanan
                    </div>

                    {{-- Category filter tabs --}}
                    <div class="flex flex-wrap gap-2 mb-5">
                        <button type="button"
                                @click="activeCategory = null"
                                :class="activeCategory === null
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'"
                                class="px-4 py-1.5 rounded-full text-sm font-medium border transition">
                            Semua
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button"
                                    @click="activeCategory = cat.id"
                                    :class="activeCategory === cat.id
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'"
                                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>

                    @error('items')
                        <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Service cards grid (scrollable, displays ~6 items / 2 rows on desktop) --}}
                    <div class="max-h-[340px] overflow-y-auto pr-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <template x-for="service in filteredServices" :key="service.id">
                            <div :class="isSelected(service.id)
                                    ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-400'
                                    : 'border-gray-200 bg-white hover:border-blue-300'"
                                 class="relative border rounded-xl p-4 cursor-pointer transition"
                                 @click="toggleService(service)">

                                {{-- Selected qty badge --}}
                                <template x-if="isSelected(service.id)">
                                    <span class="absolute top-2 right-2 min-w-[1.5rem] h-5 px-1 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                          x-text="formatQty(getItem(service.id).quantity, service.unit)"></span>
                                </template>

                                <div class="flex items-start justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-800 leading-tight pr-6" x-text="service.name"></p>
                                </div>

                                <p class="text-xs text-gray-500 mb-3 capitalize">
                                    <span x-text="service.unit"></span>
                                    &bull; Rp <span x-text="formatNumber(service.price_per_unit)"></span> / <span x-text="service.unit"></span>
                                </p>

                                {{-- Qty stepper (shown when selected) --}}
                                <template x-if="isSelected(service.id)">
                                    <div class="flex items-center gap-3 mt-2" @click.stop>
                                        <span class="text-sm font-semibold text-blue-700">
                                            Rp <span x-text="formatNumber(service.price_per_unit)"></span> / <span x-text="service.unit"></span>
                                        </span>
                                        <div class="flex items-center gap-2 ml-auto">
                                            <button type="button"
                                                    @click="decQty(service.id)"
                                                    class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition text-base font-bold">
                                                −
                                            </button>
                                            <input type="number"
                                                   :name="`items[${getItemIndex(service.id)}][quantity]`"
                                                   x-model="getItem(service.id).quantity"
                                                   @input="recalc(service.id)"
                                                   :min="service.unit === 'kg' ? 0.1 : 1"
                                                   :step="service.unit === 'kg' ? 0.1 : 1"
                                                   class="w-14 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400">
                                            <button type="button"
                                                    @click="incQty(service.id)"
                                                    class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition text-base font-bold">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Add button (shown when not selected) --}}
                                <template x-if="!isSelected(service.id)">
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-sm font-semibold text-gray-700">
                                            Rp <span x-text="formatNumber(service.price_per_unit)"></span> / <span x-text="service.unit"></span>
                                        </span>
                                        <button type="button"
                                                @click.stop="toggleService(service)"
                                                class="w-7 h-7 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-600 flex items-center justify-center transition text-lg font-bold">
                                            +
                                        </button>
                                    </div>
                                </template>

                                {{-- Hidden service_id inputs --}}
                                <template x-if="isSelected(service.id)">
                                    <input type="hidden"
                                           :name="`items[${getItemIndex(service.id)}][service_id]`"
                                           :value="service.id">
                                </template>
                            </div>
                        </template>
                        </div>
                    </div>
                </div>

                {{-- ── 3. Tanggal ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm font-semibold text-gray-700 mb-4">Tanggal</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Penerimaan <span class="text-red-500">*</span></label>
                            <input type="date" name="received_date"
                                   value="{{ old('received_date', date('Y-m-d')) }}"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('received_date') border-red-400 @enderror">
                            @error('received_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estimasi Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="estimated_done_date"
                                   value="{{ old('estimated_done_date', date('Y-m-d', strtotime('+2 days'))) }}"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('estimated_done_date') border-red-400 @enderror">
                            @error('estimated_done_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>{{-- end left column --}}

            {{-- ═══════════════════════════════════════════
                 RIGHT COLUMN — Order Summary  (1/3)
            ════════════════════════════════════════════ --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-20">

                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-5">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Detail Pesanan
                    </div>

                    {{-- Empty state --}}
                    <template x-if="items.length === 0">
                        <p class="text-sm text-gray-400 text-center py-8">Belum ada layanan dipilih.</p>
                    </template>

                    {{-- Selected items list --}}
                    <div class="space-y-4 mb-5">
                        <template x-for="item in items" :key="item.service_id">
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 min-w-0 pr-2">
                                        <p class="text-sm font-semibold text-gray-800" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Rp <span x-text="formatNumber(item.unit_price)"></span> / <span x-text="item.unit"></span>
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold text-gray-900">
                                            Rp <span x-text="formatNumber(item.subtotal)"></span>
                                        </p>
                                    </div>
                                </div>
                                {{-- Inline stepper --}}
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="decQty(item.service_id)"
                                            class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition font-bold text-base">
                                        −
                                    </button>
                                    <span class="text-sm font-semibold text-gray-700 w-10 text-center"
                                          x-text="formatQty(item.quantity, item.unit)"></span>
                                    <span class="text-xs text-gray-400" x-text="item.unit"></span>
                                    <button type="button" @click="incQty(item.service_id)"
                                            class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition font-bold text-base">
                                        +
                                    </button>
                                    <button type="button" @click="removeService(item.service_id)"
                                            class="ml-auto text-red-400 hover:text-red-600 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Totals --}}
                    <template x-if="items.length > 0">
                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal</span>
                                <span>Rp <span x-text="formatNumber(grandTotal)"></span></span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-gray-900 pt-1">
                                <span>Total</span>
                                <span class="text-blue-600">Rp <span x-text="formatNumber(grandTotal)"></span></span>
                            </div>
                        </div>
                    </template>

                    {{-- Action buttons --}}
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <a href="{{ route('orders.index') }}"
                           class="flex items-center justify-center gap-1.5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="flex items-center justify-center gap-1.5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm font-semibold text-white transition shadow-sm">
                            Simpan Order
                        </button>
                    </div>

                </div>
            </div>{{-- end right column --}}

        </div>{{-- end grid --}}
    </form>
</div>

<script>
function orderForm(services, customers, preselectedCustomerId, categories) {
    return {
        // ── data ──
        services,
        customers,
        categories,
        items: [],          // selected items: { service_id, name, unit, unit_price, quantity, subtotal }
        activeCategory: null,
        customerSearch: '',
        filteredCustomers: customers,
        selectedCustomer: null,
        showDropdown: false,

        // ── init ──
        init() {
            if (preselectedCustomerId) {
                const c = this.customers.find(c => c.id == preselectedCustomerId);
                if (c) this.selectCustomer(c);
            }
        },

        // ── computed ──
        get filteredServices() {
            if (!this.activeCategory) return this.services;
            return this.services.filter(s => s.category_id == this.activeCategory);
        },
        get grandTotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.subtotal) || 0), 0);
        },

        // ── customer helpers ──
        filterCustomers() {
            const q = this.customerSearch.toLowerCase();
            this.filteredCustomers = q
                ? this.customers.filter(c =>
                    c.full_name.toLowerCase().includes(q) ||
                    c.phone_number.includes(q))
                : this.customers;
            this.showDropdown = true;
        },
        selectCustomer(c) {
            this.selectedCustomer = c;
            this.customerSearch   = '';
            this.showDropdown     = false;
        },
        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch   = '';
        },
        initials(name) {
            const w = name.trim().split(' ');
            return (w[0][0] + (w[1] ? w[1][0] : '')).toUpperCase();
        },

        // ── service helpers ──
        isSelected(serviceId) {
            return this.items.some(i => i.service_id == serviceId);
        },
        getItem(serviceId) {
            return this.items.find(i => i.service_id == serviceId) || {};
        },
        getItemIndex(serviceId) {
            return this.items.findIndex(i => i.service_id == serviceId);
        },
        toggleService(service) {
            if (this.isSelected(service.id)) {
                this.removeService(service.id);
            } else {
                const defaultQty = service.unit === 'kg' ? 1 : 1;
                this.items.push({
                    service_id: service.id,
                    name:       service.name,
                    unit:       service.unit,
                    unit_price: parseFloat(service.price_per_unit),
                    quantity:   defaultQty,
                    subtotal:   parseFloat(service.price_per_unit) * defaultQty,
                });
            }
        },
        removeService(serviceId) {
            this.items = this.items.filter(i => i.service_id != serviceId);
        },
        // step size: 0.5 for kg, 1 for item/paket
        stepFor(serviceId) {
            const item = this.getItem(serviceId);
            return item.unit === 'kg' ? 0.5 : 1;
        },
        incQty(serviceId) {
            const item = this.getItem(serviceId);
            const step = this.stepFor(serviceId);
            item.quantity = Math.round((parseFloat(item.quantity || 0) + step) * 100) / 100;
            this.recalc(serviceId);
        },
        decQty(serviceId) {
            const item = this.getItem(serviceId);
            const step = this.stepFor(serviceId);
            const newQty = Math.round((parseFloat(item.quantity || 0) - step) * 100) / 100;
            if (newQty <= 0) {
                this.removeService(serviceId);
            } else {
                item.quantity = newQty;
                this.recalc(serviceId);
            }
        },
        recalc(serviceId) {
            const item = this.getItem(serviceId);
            const qty = parseFloat(item.quantity) || 0;
            item.subtotal = Math.round(qty * item.unit_price * 100) / 100;
        },
        // Format qty: show decimal only when needed
        formatQty(qty, unit) {
            const n = parseFloat(qty) || 0;
            if (unit === 'kg') {
                // show up to 1 decimal place, strip trailing zero
                return n % 1 === 0 ? n.toString() : n.toFixed(1);
            }
            return Math.round(n).toString();
        },

        // ── utils ──
        formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(parseFloat(value) || 0);
        },

        // ensure hidden inputs are up-to-date before submit
        prepareSubmit() {
            // inputs are bound via x-model / :name, Alpine handles reactivity
        },
    };
}
</script>
@endsection
