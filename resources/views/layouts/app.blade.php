<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laundiqo') - Laundiqo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (request()->routeIs('services.index', 'service-categories.index', 'racks.index', 'racks.show', 'users.index', 'orders.status', 'saas-admin.invoices', 'saas-admin.tenants', 'billing.index'))
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <style>
        /* Styling Global DataTables agar rapi dan selaras dengan Tailwind CSS */
        .dataTables_wrapper {
            font-family: inherit;
            color: #374151;
            width: 100%;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin: 0;
            padding: 1rem 1.25rem 1rem;
            min-height: 3.75rem;
            display: flex;
            align-items: center;
        }
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4b5563;
            white-space: nowrap;
        }
        .dataTables_wrapper .dataTables_length select {
            padding: 0.35rem 2rem 0.35rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #fff;
            font-size: 0.875rem;
            outline: none;
            margin: 0;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.45rem 0.85rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            background-color: #fff;
            font-size: 0.875rem;
            outline: none;
            margin: 0;
            width: 14rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
        .dataTables_wrapper .dataTables_info {
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.35rem 0.75rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid transparent !important;
            margin: 0 0.15rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #2563eb !important;
            color: #fff !important;
            border-color: #2563eb !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3f4f6 !important;
            color: #111827 !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #e5e7eb !important;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e5e7eb !important;
        }
        @media (max-width: 640px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                justify-content: space-between;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: min(12rem, 55vw);
            }
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none;
                text-align: center;
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        </style>
    @endif
    @stack('styles')
</head>
<body class="skylearn-app bg-white min-h-screen"
      x-data="{
          sidebarOpen: false,
          logoutModalOpen: false,
          confirmModalOpen: false,
          confirmMessage: '',
          pendingForm: null,
          requestConfirmation(form, message) {
              this.pendingForm = form;
              this.confirmMessage = message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
              this.confirmModalOpen = true;
          },
          cancelConfirmation() {
              this.confirmModalOpen = false;
              this.pendingForm = null;
          },
          submitConfirmation() {
              const form = this.pendingForm;
              this.confirmModalOpen = false;
              this.pendingForm = null;
              if (form) form.submit();
          },
          normalizeConfirmations() {
              this.$nextTick(() => {
                  document.querySelectorAll('[onsubmit*=&quot;confirm(&quot;], [onclick*=&quot;confirm(&quot;]').forEach((element) => {
                      const attribute = element.getAttribute(element.hasAttribute('onsubmit') ? 'onsubmit' : 'onclick') || '';
                      const match = attribute.match(/confirm\((['&quot;])([\s\S]*)\1\)/);
                      const message = match ? match[2] : 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                      const attributeName = element.hasAttribute('onsubmit') ? 'onsubmit' : 'onclick';
                      element.removeAttribute(attributeName);

                      if (attributeName === 'onsubmit') {
                          element.addEventListener('submit', (event) => {
                              event.preventDefault();
                              this.requestConfirmation(event.currentTarget, message);
                          });
                      } else {
                          element.addEventListener('click', (event) => {
                              event.preventDefault();
                              event.stopPropagation();
                              this.requestConfirmation(element.form, message);
                          });
                      }
                  });
              });
          }
      }"
      x-init="normalizeConfirmations()"
      @keydown.escape.window="sidebarOpen = false; logoutModalOpen = false; cancelConfirmation()">

{{-- ── MOBILE OVERLAY ── --}}
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-900/50 z-30 lg:hidden"
     @click="sidebarOpen = false"
     style="display: none;">
</div>

<div class="flex min-h-screen">

    {{-- ── SIDEBAR (Fixed & Non-scrollable with page) ── --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-60 h-screen bg-white border-r border-gray-200 flex flex-col
                  transform transition-transform duration-200 ease-in-out
                  lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Brand --}}
        <div class="flex items-center justify-between px-5 pt-6 pb-5 border-b border-gray-100">
            <div>
                <a href="{{ route('dashboard')}}">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $appStoreCity ?? 'Laundry' }}</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight">{{ $appStoreName ?? 'Sistem Laundry' }}</p>
                    @if (isset($currentTenant))
                        <div id="sidebar-tenant-badge" class="mt-1.5 flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase
                                {{ $currentTenant->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($currentTenant->status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $currentTenant->status === 'active' ? 'bg-emerald-500' : ($currentTenant->status === 'trial' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                {{ $currentTenant->status === 'trial' ? 'Trial ' . $currentTenant->daysRemaining() . 'h' : $currentTenant->status }}
                            </span>
                            @if (isset($currentSubscription) && $currentSubscription->plan)
                                <span class="text-[10px] text-gray-500 font-bold uppercase">{{ $currentSubscription->plan->name }}</span>
                            @endif
                        </div>
                    @endif
                </a>
            </div>
            {{-- Close button (mobile only) --}}
            <button @click="sidebarOpen = false"
                    class="lg:hidden p-1 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            @if (!auth()->user()->isSuperAdmin())
            {{-- Dashboard — kasir, operator, admin --}}
            @role('kasir|operator|admin')
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard
            </a>
            @endrole

            {{-- Pelanggan — admin dan kasir --}}
            @role('kasir|admin')
            <a href="{{ route('customers.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pelanggan
            </a>
            @endrole

            {{-- Order — admin dan kasir --}}
            @role('kasir|admin')
            <a href="{{ route('orders.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('orders.index', 'orders.show', 'orders.edit') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Order
            </a>
            @endrole

                {{-- Pembayaran — admin dan kasir --}}
                @role('kasir|admin')
            <a href="{{ route('payments.list') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('payments.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Pembayaran
            </a>
            @endrole

            {{-- Pelacakan Status — kasir, operator, dan admin (admin read-only) --}}
            @role('kasir|operator|admin')
            <a href="{{ route('orders.status') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('orders.status') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pelacakan Status
            </a>
            @endrole

            {{-- ── Menu khusus Admin ── --}}
            @role('admin')

            {{-- Kategori Layanan --}}
            <a href="{{ route('service-categories.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('service-categories.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                Kategori Layanan
            </a>

            {{-- Layanan --}}
            <a href="{{ route('services.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('services.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Layanan
            </a>

            {{-- Laporan --}}
            {{-- Laporan --}}
            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Laporan
            </a>

            {{-- Pengguna --}}
            <a href="{{ route('users.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Pengguna
            </a>

            {{-- Pengaturan Toko --}}
            <a href="{{ route('settings.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Pengaturan Usaha
            </a>

            {{-- Langganan & Billing (tenant admin only) --}}
            @if (!auth()->user()->isSuperAdmin())
            <a href="{{ route('billing.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('billing.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Langganan & Billing
                </div>
                @if (isset($currentTenant))
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold {{ request()->routeIs('billing.*') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600' }}">
                        {{ $currentTenant->daysRemaining() }}h
                    </span>
                @endif
            </a>
            @endif

            {{-- Ekspor Data --}}
            <a href="{{ route('export.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('export.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Ekspor Data
            </a>

            @endrole

            @if (auth()->user()->hasTenantPermission('manage-users'))
            <a href="{{ route('tenant-roles.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('tenant-roles.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5a2.25 2.25 0 110 4.5 2.25 2.25 0 010-4.5zm0 7.5a6 6 0 00-6 6h12a6 6 0 00-6-6zM19.5 6.75h1.25a1.25 1.25 0 010 2.5H19.5m-15-2.5H3.25a1.25 1.25 0 000 2.5H4.5"/>
                </svg>
                Akses & Role
            </a>
            @endif

            {{-- ── Menu khusus Operator ── --}}
            @role('operator|admin')

            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Produksi</p>
            </div>

            {{-- Lihat Rak — admin dan operator --}}
            <a href="{{ route('racks.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                   {{ request()->routeIs('racks.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Lokasi Rak
            </a>
            @endrole
            @endif

            @if (auth()->check() && auth()->user()->isSuperAdmin())
                @php
                    $superadminUnreadLeads = \App\Models\ContactMessage::where('is_read', false)->count();
                    $superadminUnpaidInvoices = \App\Models\BillingInvoice::where('status', 'unpaid')->count();
                @endphp
                <div class="pt-2 pb-1">
                    <p class="px-3 text-[11px] font-bold text-blue-600 uppercase tracking-wider">Manajemen Platform SaaS</p>
                </div>

                {{-- 1. Dashboard Ringkasan --}}
                <a href="{{ route('saas-admin.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.index') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Dashboard SaaS
                </a>

                {{-- 2. Kelola Tenant Usaha --}}
                <a href="{{ route('saas-admin.tenants') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.tenants*') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Kelola Tenant Usaha
                </a>

                {{-- 3. Tagihan & Pembayaran --}}
                <a href="{{ route('saas-admin.invoices') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.invoices*') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span>Tagihan Langganan</span>
                    </div>
                    <span id="sidebar-badge-unpaid-invoices"
                          style="{{ $superadminUnpaidInvoices > 0 ? '' : 'display: none;' }}"
                          class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ request()->routeIs('saas-admin.invoices*') ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">
                        {{ $superadminUnpaidInvoices }}
                    </span>
                </a>

                {{-- 4. Paket & Harga --}}
                <a href="{{ route('saas-admin.plans') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.plans*') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Paket & Harga
                </a>

                {{-- 5. Pesan Masuk / Leads --}}
                <a href="{{ route('saas-admin.messages') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.messages*') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Pesan Masuk</span>
                    </div>
                    <span id="sidebar-badge-unread-leads"
                          style="{{ $superadminUnreadLeads > 0 ? '' : 'display: none;' }}"
                          class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ request()->routeIs('saas-admin.messages*') ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">
                        {{ $superadminUnreadLeads }}
                    </span>
                </a>

                <div class="pt-3 pb-1">
                    <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Konten & Website</p>
                </div>

                {{-- 6. CMS Web Profile --}}
                <a href="{{ route('saas-admin.cms') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('saas-admin.cms*') ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    CMS Web Profile
                </a>

                {{-- 7. Buka Landing Page --}}
                <a href="{{ route('marketing.home') }}" target="_blank" rel="noopener"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <span>Lihat Web Publik</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif

            @if (!auth()->user()->isSuperAdmin() && !in_array(auth()->user()->role, ['admin', 'kasir', 'operator'], true))
                @if (auth()->user()->hasTenantPermission('view-dashboard'))
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Dashboard</a>
                @endif
                @if (auth()->user()->hasTenantPermission('manage-customers'))
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Pelanggan</a>
                @endif
                @if (auth()->user()->hasTenantPermission('manage-orders'))
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Order</a>
                @endif
                @if (auth()->user()->hasTenantPermission('manage-payments'))
                    <a href="{{ route('payments.list') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Pembayaran</a>
                @endif
                @if (auth()->user()->hasTenantPermission('manage-services'))
                    <a href="{{ route('services.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Layanan</a>
                @endif
                @if (auth()->user()->hasTenantPermission('view-reports'))
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Laporan</a>
                @endif
                @if (auth()->user()->hasTenantPermission('manage-racks'))
                    <a href="{{ route('racks.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">Lokasi Rak</a>
                @endif
            @endif

        </nav>

        {{-- Bottom: User info + Logout --}}
        <div class="px-3 pb-5 border-t border-gray-100 pt-3">
            <div class="flex items-center gap-3 px-3 py-2 mb-1">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-blue-600">
                        {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->username }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->getRoleNames()->first() ?? 'pengguna' }}</p>
                </div>
            </div>

            <a href="{{ route('profile.edit') }}"
               class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profil
            </a>

            <button type="button"
                    @click="logoutModalOpen = true"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition mt-1">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </div>

    </aside>

    {{-- ── MAIN AREA ── --}}
    <div class="flex-1 flex flex-col min-w-0 lg:pl-60">

        {{-- Top Navbar (Mobile & Desktop) with Real-Time Notifications --}}
        <header class="bg-white/90 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-3 flex items-center justify-between sticky top-0 z-30 print:hidden">
            {{-- Left: Mobile Hamburger + Brand / Desktop Live Context --}}
            <div class="flex items-center gap-3">
                <button type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Buka menu navigasi">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex flex-col lg:hidden">
                    <span class="text-xs font-bold text-gray-400 leading-none">{{ $appStoreCity ?? 'Laundry' }}</span>
                    <span class="text-base font-bold text-gray-900 leading-tight">{{ $appStoreName ?? 'Sistem Laundry' }}</span>
                </div>

                <div class="hidden lg:flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-[11px] font-bold text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Sync Aktif
                    </span>
                    <span class="text-xs font-medium text-gray-400">•</span>
                    <span class="text-xs font-semibold text-gray-600">
                        {{ auth()->user()->isSuperAdmin() ? 'Panel Kontrol SaaS Laundiqo' : ($appStoreName ?? 'Operasional Laundry') }}
                    </span>
                </div>
            </div>

            {{-- Right: Real-Time Notification Bell + User Pill --}}
            <div class="flex items-center gap-2.5 sm:gap-3">
                {{-- Notification Bell Component --}}
                <div class="relative" x-data="realtimeNotifications()">
                    <button type="button"
                            @click="toggleDropdown()"
                            class="relative p-2 rounded-xl text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 border border-transparent hover:border-blue-100 transition focus:outline-none"
                            aria-label="Notifikasi Real-Time">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>

                        {{-- Unread Badge + Ping --}}
                        <span x-show="unreadCount > 0" style="display: none;" class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[16px] items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-extrabold text-white leading-none h-4 min-w-[16px]"
                                  x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    {{-- Notification Dropdown Panel --}}
                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         style="display: none;"
                         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-gray-200/90 overflow-hidden z-50">

                        {{-- Header --}}
                        <div class="px-4 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-800">Notifikasi Live</h3>
                                <span x-show="unreadCount > 0"
                                      class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700"
                                      x-text="unreadCount + ' baru'"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                {{-- Sound Toggle --}}
                                <button type="button"
                                        @click="toggleSound()"
                                        :title="soundEnabled ? 'Suara Notifikasi Aktif (Klik untuk bisukan)' : 'Suara Notifikasi Bisu (Klik untuk aktifkan)'"
                                        class="p-1.5 rounded-lg text-xs transition"
                                        :class="soundEnabled ? 'text-blue-600 bg-blue-50 hover:bg-blue-100' : 'text-gray-400 hover:bg-gray-200'">
                                    <svg x-show="soundEnabled" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                    </svg>
                                    <svg x-show="!soundEnabled" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                    </svg>
                                </button>

                                {{-- Mark All Read --}}
                                <button type="button"
                                        x-show="unreadCount > 0"
                                        @click="markAllRead()"
                                        class="text-[11px] font-bold text-blue-600 hover:text-blue-800 px-2 py-1 rounded-lg hover:bg-blue-50 transition">
                                    Tandai Dibaca
                                </button>
                            </div>
                        </div>

                        {{-- Notification List --}}
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            <template x-if="notifications.length === 0">
                                <div class="px-4 py-10 text-center">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-gray-700">Belum Ada Notifikasi</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Aktivitas terbaru akan muncul otomatis secara real-time.</p>
                                </div>
                            </template>

                            <template x-for="item in notifications" :key="item.id">
                                <a :href="item.url"
                                   @click="handleItemClick(item)"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition"
                                   :class="item.is_unread ? 'bg-blue-50/40' : 'bg-white'">
                                    {{-- Contextual Tone Icon --}}
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                                         :class="{
                                             'bg-emerald-100 text-emerald-600': item.tone === 'emerald',
                                             'bg-amber-100 text-amber-600': item.tone === 'amber',
                                             'bg-rose-100 text-rose-600': item.tone === 'rose',
                                             'bg-blue-100 text-blue-600': !item.tone || item.tone === 'blue'
                                         }">
                                        {{-- Payment / Ready Check Icon --}}
                                        <svg x-show="item.tone === 'emerald'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{-- Warning / Unpaid Invoice Icon --}}
                                        <svg x-show="item.tone === 'amber'" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{-- Lead / Message Icon --}}
                                        <svg x-show="item.tone === 'rose'" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{-- Order / Default Info Icon --}}
                                        <svg x-show="!item.tone || item.tone === 'blue'" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-xs font-bold text-gray-900 leading-snug" x-text="item.title"></p>
                                            <span x-show="item.is_unread" class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 mt-1"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                        <p class="text-[10px] font-semibold text-gray-400 mt-1" x-text="item.time_ago"></p>
                                    </div>
                                </a>
                            </template>
                        </div>

                        {{-- Footer --}}
                        <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Sinkronisasi otomatis tiap 4 detik
                            </span>
                            <button type="button" @click="fetchNotifications(false)" class="text-blue-600 hover:underline font-semibold">
                                Segarkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- User Profile Pill --}}
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-xl hover:bg-gray-100 transition border border-transparent hover:border-gray-200">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xs font-bold text-blue-600">
                            {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-bold text-gray-800 leading-none">{{ auth()->user()->username }}</p>
                        <p class="text-[10px] text-gray-400 capitalize mt-0.5">{{ auth()->user()->isSuperAdmin() ? 'Superadmin' : (auth()->user()->getRoleNames()->first() ?? 'Staf') }}</p>
                    </div>
                </a>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6">

            {{-- Modern Floating Toast Notifications (Sonner-Style, Top-Right) --}}
            <div class="fixed top-4 right-4 z-[60] flex flex-col gap-3 w-[calc(100vw-2rem)] sm:w-96 pointer-events-none print:hidden"
                 x-data="{
                     dynamicToasts: [],
                     addToast(detail) {
                         const id = Date.now() + Math.random();
                         this.dynamicToasts.push({
                             id,
                             type: detail.type || 'success',
                             title: detail.title || (detail.type === 'error' ? 'Gagal' : (detail.type === 'warning' ? 'Peringatan' : 'Berhasil')),
                             message: detail.message || '',
                             url: detail.url || null,
                         });
                         setTimeout(() => {
                             this.dynamicToasts = this.dynamicToasts.filter(t => t.id !== id);
                         }, detail.duration || 5000);
                     }
                 }"
                 @notify.window="addToast($event.detail)">

                @if (session('success'))
                    <div x-data="{
                             show: true,
                              paused: false,
                             progress: 100,
                             interval: null,
                             startTimer() {
                                 const step = 100 / (4500 / 50);
                                 this.interval = setInterval(() => {
                                     if (!this.paused) {
                                         this.progress -= step;
                                         if (this.progress <= 0) {
                                             this.show = false;
                                             clearInterval(this.interval);
                                         }
                                     }
                                 }, 50);
                             }
                         }"
                         x-init="startTimer()"
                         x-show="show"
                         @mouseenter="paused = true"
                         @mouseleave="paused = false"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                         x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                         x-transition:leave-end="translate-x-8 opacity-0 scale-95"
                         role="status"
                         class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md border border-emerald-200/80 rounded-2xl shadow-xl shadow-emerald-900/5 p-4 flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">Berhasil</p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5 leading-snug">{{ session('success') }}</p>
                        </div>
                        <button type="button" @click="show = false"
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition flex-shrink-0"
                                aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-100">
                            <div class="h-full bg-emerald-500 transition-all duration-75" :style="'width: ' + Math.max(0, progress) + '%'"></div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{
                             show: true,
                             paused: false,
                             progress: 100,
                             interval: null,
                             startTimer() {
                                 const step = 100 / (6000 / 50);
                                 this.interval = setInterval(() => {
                                     if (!this.paused) {
                                         this.progress -= step;
                                         if (this.progress <= 0) {
                                             this.show = false;
                                             clearInterval(this.interval);
                                         }
                                     }
                                 }, 50);
                             }
                         }"
                         x-init="startTimer()"
                         x-show="show"
                         @mouseenter="paused = true"
                         @mouseleave="paused = false"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                         x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                         x-transition:leave-end="translate-x-8 opacity-0 scale-95"
                         role="alert"
                         class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md border border-red-200/80 rounded-2xl shadow-xl shadow-red-900/5 p-4 flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-200/60 text-red-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-red-700">Gagal / Perhatian</p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5 leading-snug">{{ session('error') }}</p>
                        </div>
                        <button type="button" @click="show = false"
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition flex-shrink-0"
                                aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-100">
                            <div class="h-full bg-red-500 transition-all duration-75" :style="'width: ' + Math.max(0, progress) + '%'"></div>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div x-data="{
                             show: true,
                             paused: false,
                             progress: 100,
                             interval: null,
                             startTimer() {
                                 const step = 100 / (5500 / 50);
                                 this.interval = setInterval(() => {
                                     if (!this.paused) {
                                         this.progress -= step;
                                         if (this.progress <= 0) {
                                             this.show = false;
                                             clearInterval(this.interval);
                                         }
                                     }
                                 }, 50);
                             }
                         }"
                         x-init="startTimer()"
                         x-show="show"
                         @mouseenter="paused = true"
                         @mouseleave="paused = false"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                         x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                         x-transition:leave-end="translate-x-8 opacity-0 scale-95"
                         role="status"
                         class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md border border-amber-200/80 rounded-2xl shadow-xl shadow-amber-900/5 p-4 flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-amber-700">Peringatan</p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5 leading-snug">{{ session('warning') }}</p>
                        </div>
                        <button type="button" @click="show = false"
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition flex-shrink-0"
                                aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-100">
                            <div class="h-full bg-amber-500 transition-all duration-75" :style="'width: ' + Math.max(0, progress) + '%'"></div>
                        </div>
                    </div>
                @endif

                {{-- Dynamic Client-Side Toasts (via window.dispatchEvent(new CustomEvent('notify', { detail: { type, message, url } }))) --}}
                <template x-for="toast in dynamicToasts" :key="toast.id">
                    <div x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                         x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                         @click="if (toast.url) window.location.href = toast.url"
                         :class="toast.url ? 'cursor-pointer hover:border-blue-300' : ''"
                         class="pointer-events-auto relative overflow-hidden bg-white/95 backdrop-blur-md border border-gray-200 rounded-2xl shadow-xl p-4 flex items-start gap-3.5 transition">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                             :class="toast.type === 'error' ? 'bg-red-50 text-red-600 border border-red-200/60' : (toast.type === 'warning' ? 'bg-amber-50 text-amber-600 border border-amber-200/60' : 'bg-emerald-50 text-emerald-600 border border-emerald-200/60')">
                            <svg x-show="toast.type !== 'error'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider"
                               :class="toast.type === 'error' ? 'text-red-700' : (toast.type === 'warning' ? 'text-amber-700' : 'text-emerald-700')"
                               x-text="toast.title"></p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5 leading-snug" x-text="toast.message"></p>
                        </div>
                        <button type="button" @click.stop="dynamicToasts = dynamicToasts.filter(t => t.id !== toast.id)"
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            @if (session('subscription_warning'))
                <div class="mb-4 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm shadow-sm">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-xs">{{ session('subscription_warning') }}</p>
                        <a href="{{ route('billing.index') }}" class="inline-block px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition whitespace-nowrap text-center">
                            Perpanjang Sekarang
                        </a>
                    </div>
                </div>
            @endif

            @if (session('read_only_alert'))
                <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl text-sm shadow-sm">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="font-bold text-xs uppercase tracking-wide text-red-700">Mode Read-Only Aktif</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ session('read_only_alert') }}</p>
                        </div>
                        <a href="{{ route('billing.index') }}" class="inline-block px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition whitespace-nowrap text-center shadow-sm">
                            Aktifkan Langganan &rarr;
                        </a>
                    </div>
                </div>
            @endif

            <div id="live-page-content"
                 data-live-sync-enabled="{{ request()->routeIs('orders.create', 'orders.edit', 'customers.create', 'customers.edit', 'services.create', 'services.edit', 'service-categories.create', 'service-categories.edit', 'racks.create', 'racks.edit', 'users.create', 'users.edit', 'tenant-roles.create', 'tenant-roles.edit', 'saas-admin.cms', 'settings.index', 'profile.edit') ? '0' : '1' }}">
                @yield('content')
            </div>

        </main>

    </div>
</div>

{{-- ── MODAL KONFIRMASI AKSI ── --}}
<div x-show="confirmModalOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     role="dialog"
     aria-modal="true"
     aria-labelledby="confirm-action-title">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
         @click="cancelConfirmation()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="confirmModalOpen"
             x-transition
             class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.8L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="confirm-action-title" class="text-lg font-bold text-gray-900">Konfirmasi tindakan</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500" x-text="confirmMessage"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="cancelConfirmation()"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" @click="submitConfirmation()"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700">
                    Ya, lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── MODAL KONFIRMASI LOGOUT ── --}}
<div x-show="logoutModalOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-sm"
         @click="logoutModalOpen = false"></div>

    {{-- Dialog Container --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="logoutModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">

            <div class="flex items-center gap-4 mb-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Konfirmasi Logout</h3>
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin keluar dari sistem?</p>
                </div>
            </div>

            <p class="text-xs text-gray-400 mb-6 bg-gray-50 p-3 rounded-lg border border-gray-100">
                Sesi login aktif Anda akan diakhiri. Pastikan semua perubahan data telah tersimpan sebelum keluar.
            </p>

            <div class="flex items-center justify-end gap-3">
                <button type="button"
                        @click="logoutModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition">
                    Batal
                </button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition shadow-sm">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if (request()->routeIs('services.index', 'service-categories.index', 'racks.index', 'racks.show', 'users.index', 'orders.status', 'saas-admin.invoices', 'saas-admin.tenants', 'billing.index'))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" defer></script>
    <script>
        window.initAppDataTables = function(savedStates = []) {
            if (!window.jQuery || !$.fn || !$.fn.DataTable || !$('.datatable').length) {
                return;
            }
            $('.datatable').each(function(idx) {
                if (!$.fn.DataTable.isDataTable(this)) {
                    const dt = $(this).DataTable({
                        responsive: true,
                        pageLength: 10,
                        columnDefs: [
                            { targets: 'no-sort', orderable: false }
                        ],
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                            infoFiltered: "(difilter dari total _MAX_ data)",
                            zeroRecords: "Tidak ada data yang cocok ditemukan",
                            paginate: {
                                first: "Awal",
                                last: "Akhir",
                                next: "Berikutnya",
                                previous: "Sebelumnya"
                            }
                        }
                    });
                    const st = savedStates[idx];
                    if (st) {
                        if (st.pageLen) dt.page.len(st.pageLen);
                        if (st.order && st.order.length) dt.order(st.order);
                        if (typeof st.search === 'string') dt.search(st.search);
                        dt.draw(false);
                        if (typeof st.page === 'number' && st.page > 0) {
                            dt.page(st.page).draw('page');
                        }
                    }
                }
            });
        };

        window.addEventListener('DOMContentLoaded', function() {
            window.initAppDataTables();
        });
    </script>
@endif

<script>
    window.realtimeNotifications = function () {
        const userId = '{{ auth()->id() }}';
        const storageKey = 'laundiqo_toasted_notifs_v2_' + userId;
        const soundKey = 'laundiqo_notif_sound_' + userId;
        const csrfToken = '{{ csrf_token() }}';

        return {
            open: false,
            unreadCount: 0,
            notifications: [],
            soundEnabled: localStorage.getItem(soundKey) !== '0',
            pollTimer: null,
            initialized: false,
            lastSignature: null,
            pendingPageSync: false,
            isSyncingPage: false,

            init() {
                if (this.initialized) return;
                this.initialized = true;

                this.fetchNotifications(true);
                this.pollTimer = setInterval(() => {
                    if (this.pendingPageSync && this.canSafeSyncPage()) {
                        this.syncPageContent();
                    }
                    this.fetchNotifications(false);
                }, 3500);

                window.addEventListener('focus', () => {
                    this.fetchNotifications(false);
                });

                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        this.fetchNotifications(false);
                    }
                });

                document.addEventListener('focusout', () => {
                    if (this.pendingPageSync) {
                        setTimeout(() => {
                            if (this.pendingPageSync && this.canSafeSyncPage()) {
                                this.syncPageContent();
                            }
                        }, 250);
                    }
                });
            },

            toggleDropdown() {
                this.open = !this.open;
            },

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
                localStorage.setItem(soundKey, this.soundEnabled ? '1' : '0');
                if (this.soundEnabled) {
                    this.playChime();
                }
            },

            playChime() {
                if (!this.soundEnabled) return;
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const now = ctx.currentTime;

                    // Soft two-tone bell chime (E5 -> B5)
                    [659.25, 987.77].forEach((freq, idx) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, now + idx * 0.11);
                        gain.gain.setValueAtTime(0.0001, now + idx * 0.11);
                        gain.gain.exponentialRampToValueAtTime(0.08, now + idx * 0.11 + 0.02);
                        gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.11 + 0.28);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now + idx * 0.11);
                        osc.stop(now + idx * 0.11 + 0.3);
                    });
                } catch (e) {
                    // Ignore autoplay restriction errors
                }
            },

            canSafeSyncPage() {
                const container = document.getElementById('live-page-content');
                if (!container || container.dataset.liveSyncEnabled === '0') {
                    return false;
                }
                // Do not interrupt active Midtrans Snap popup
                if (document.getElementById('snap-midtrans')) {
                    return false;
                }
                // Do not interrupt if user is actively typing/selecting inside the page content
                const active = document.activeElement;
                if (active && container.contains(active) && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)) {
                    return false;
                }
                // Do not interrupt if any modal dialog is currently visible on screen
                const visibleModals = Array.from(document.querySelectorAll('[role="dialog"], #live-page-content .fixed.inset-0'))
                    .filter(el => el.offsetParent !== null && window.getComputedStyle(el).display !== 'none');
                if (visibleModals.length > 0) {
                    return false;
                }
                return true;
            },

            async syncPageContent() {
                if (this.isSyncingPage) return;
                const container = document.getElementById('live-page-content');
                if (!container || !this.canSafeSyncPage()) {
                    this.pendingPageSync = true;
                    return;
                }

                this.isSyncingPage = true;
                this.pendingPageSync = false;

                try {
                    const scrollY = window.scrollY;
                    const searchInput = container.querySelector('input[name="search"]');
                    const savedSearchVal = searchInput ? searchInput.value : null;

                    // Preserve DataTables state if present
                    const savedDtStates = [];
                    if (window.jQuery && $.fn && $.fn.DataTable) {
                        $('#live-page-content .datatable').each(function(idx) {
                            if ($.fn.DataTable.isDataTable(this)) {
                                const dt = $(this).DataTable();
                                savedDtStates[idx] = {
                                    search: dt.search(),
                                    pageLen: dt.page.len(),
                                    page: dt.page(),
                                    order: dt.order(),
                                };
                            }
                        });
                    }

                    const res = await fetch(window.location.href, {
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Live-Sync': '1',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (!res.ok) return;
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.getElementById('live-page-content');

                    if (!newContainer || !this.canSafeSyncPage()) {
                        this.pendingPageSync = true;
                        return;
                    }

                    // Cleanly destroy existing DataTable instances before DOM swap
                    if (window.jQuery && $.fn && $.fn.DataTable) {
                        $('#live-page-content .datatable').each(function() {
                            if ($.fn.DataTable.isDataTable(this)) {
                                $(this).DataTable().destroy();
                            }
                        });
                    }

                    container.innerHTML = newContainer.innerHTML;

                    // Also sync sidebar tenant status badge if present
                    const curTenantBadge = document.getElementById('sidebar-tenant-badge');
                    const newTenantBadge = doc.getElementById('sidebar-tenant-badge');
                    if (curTenantBadge && newTenantBadge) {
                        curTenantBadge.innerHTML = newTenantBadge.innerHTML;
                    }

                    // Restore search input value for Alpine-filtered lists
                    if (savedSearchVal !== null) {
                        const restoredInput = container.querySelector('input[name="search"]');
                        if (restoredInput) {
                            restoredInput.value = savedSearchVal;
                            restoredInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }

                    // Re-initialize DataTables with preserved state
                    if (window.initAppDataTables) {
                        window.initAppDataTables(savedDtStates);
                    }

                    // Re-bind custom confirmation modals
                    if (document.body._x_dataStack && document.body._x_dataStack[0]?.normalizeConfirmations) {
                        document.body._x_dataStack[0].normalizeConfirmations();
                    }

                    window.scrollTo({ top: scrollY, behavior: 'instant' });
                } catch (e) {
                    // Ignore transient network errors
                } finally {
                    this.isSyncingPage = false;
                }
            },

            async fetchNotifications(isInitial = false) {
                try {
                    const res = await fetch('{{ route('notifications.poll') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    const prevRaw = localStorage.getItem(storageKey);
                    const hadState = prevRaw !== null;
                    let toastedIds = [];
                    try {
                        toastedIds = prevRaw ? JSON.parse(prevRaw) : [];
                    } catch (e) {
                        toastedIds = [];
                    }

                    const incoming = Array.isArray(data.notifications) ? data.notifications : [];
                    this.notifications = incoming;
                    this.unreadCount = Number(data.unread_count || 0);

                    // Update Superadmin sidebar badges dynamically if present
                    if (data.sidebar_badges) {
                        const invBadge = document.getElementById('sidebar-badge-unpaid-invoices');
                        if (invBadge) {
                            const count = Number(data.sidebar_badges.unpaid_invoices || 0);
                            invBadge.textContent = count;
                            invBadge.style.display = count > 0 ? '' : 'none';
                        }
                        const leadBadge = document.getElementById('sidebar-badge-unread-leads');
                        if (leadBadge) {
                            const count = Number(data.sidebar_badges.unread_leads || 0);
                            leadBadge.textContent = count;
                            leadBadge.style.display = count > 0 ? '' : 'none';
                        }
                    }

                    // Live DOM Sync when underlying database state changes
                    if (data.state_signature) {
                        if (this.lastSignature !== null && this.lastSignature !== data.state_signature) {
                            if (this.canSafeSyncPage()) {
                                this.syncPageContent();
                            } else {
                                this.pendingPageSync = true;
                            }
                        }
                        this.lastSignature = data.state_signature;
                    }

                    const nowEpoch = Math.floor(Date.now() / 1000);
                    // Trigger toast if this item is unread & not yet toasted (on first ever load, only toast recent items <= 10 mins old)
                    const newItems = incoming.filter(item => {
                        if (!item.is_unread || toastedIds.includes(item.id)) {
                            return false;
                        }
                        if (!hadState) {
                            return (nowEpoch - Number(item.timestamp || 0)) <= 600;
                        }
                        return true;
                    });

                    if (newItems.length > 0) {
                        newItems.slice(0, 2).forEach(item => {
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    type: item.tone === 'amber' ? 'warning' : 'success',
                                    title: item.title,
                                    message: item.message,
                                    url: item.url,
                                    duration: 6000,
                                }
                            }));
                        });
                        this.playChime();
                    }

                    // Persist all current IDs into localStorage so each notification only toasts once
                    const mergedIds = Array.from(new Set([...toastedIds, ...incoming.map(i => i.id)])).slice(-80);
                    localStorage.setItem(storageKey, JSON.stringify(mergedIds));
                } catch (e) {
                    // Silent fail on transient network error
                }
            },

            async markAllRead() {
                try {
                    const res = await fetch('{{ route('notifications.read-all') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    if (res.ok) {
                        this.unreadCount = 0;
                        this.notifications = this.notifications.map(n => ({ ...n, is_unread: false }));
                    }
                } catch (e) {
                    // Ignore
                }
            },

            handleItemClick(item) {
                if (item.is_unread) {
                    this.markAllRead();
                }
                this.open = false;
            }
        };
    };
</script>

@stack('scripts')
</body>
</html>
