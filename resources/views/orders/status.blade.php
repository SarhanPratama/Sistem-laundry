@extends('layouts.app')

@section('title', 'Pelacakan Status Order')
@section('page-title', 'Pelacakan Status')

@section('content')
    <div class="mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-1">Pelacakan Status Produksi</h1>
                <p class="text-gray-500 text-sm">Pantau dan perbarui tahap pengerjaan cucian serta lokasi rak secara langsung.</p>
            </div>
            @role('kasir|admin')
                <a href="{{ route('orders.create') }}"
                    class="inline-flex items-center justify-center gap-2 bg-[#0B57D0] hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition duration-200 shadow-sm w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Order Baru
                </a>
            @endrole
        </div>

        {{-- Banner read-only untuk role tanpa izin perubahan status --}}
        @if ($isReadOnly ?? false)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <p class="font-semibold">Mode pemantauan</p>
                    <p class="mt-0.5">
                        Akun Anda dapat melihat histori dan progres status laundry, namun belum memiliki tugas untuk
                        mengubah status order. Hubungi admin tenant untuk menyesuaikan akses role.
                    </p>
                </div>
            </div>
        @endif

        {{-- Orders Table Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 sm:p-6">
            @if ($orders->isEmpty())
                <div class="text-center py-16 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-base font-medium text-gray-600">No orders found.</p>
                    <p class="text-sm mt-1">Buat order baru untuk mulai memantau proses laundry.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="datatable min-w-full divide-y divide-gray-100 text-left">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3.5">No. Order</th>
                                <th class="px-4 py-3.5">Pelanggan</th>
                                <th class="px-4 py-3.5">Tahap Produksi</th>
                                <th class="px-4 py-3.5">Lokasi Rak</th>
                                <th class="px-4 py-3.5">Status Bayar</th>
                                <th class="px-4 py-3.5">Tgl Masuk</th>
                                <th class="px-4 py-3.5">Estimasi Selesai</th>
                                <th class="px-4 py-3.5 text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @php
                                $colors = [
                                    'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                    'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                                    'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
                                    'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                                    'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                                    'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
                                ];

                                $statusConfig = [
                                    'diterima' => [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-600',
                                        'label' => 'Diterima',
                                    ],
                                    'dicuci' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Dicuci'],
                                    'dikeringkan' => [
                                        'bg' => 'bg-yellow-100',
                                        'text' => 'text-yellow-600',
                                        'label' => 'Dikeringkan',
                                    ],
                                    'disetrika' => [
                                        'bg' => 'bg-purple-100',
                                        'text' => 'text-purple-600',
                                        'label' => 'Disetrika',
                                    ],
                                    'siap_diambil' => [
                                        'bg' => 'bg-orange-100',
                                        'text' => 'text-orange-600',
                                        'label' => 'Siap',
                                    ],
                                    'selesai' => [
                                        'bg' => 'bg-green-100',
                                        'text' => 'text-green-600',
                                        'label' => 'Selesai',
                                    ],
                                ];

                                $statusSequence = [
                                    'diterima',
                                    'dicuci',
                                    'dikeringkan',
                                    'disetrika',
                                    'siap_diambil',
                                    'selesai',
                                ];

                                // Produksi hanya oleh operator, pengambilan barang (selesai) hanya oleh kasir
                                $isOperator = auth()->user()->hasRole('operator');
                                $isKasir = auth()->user()->hasRole('kasir');
                            @endphp

                            @foreach ($orders as $index => $order)
                                @php
                                    $status = $statusConfig[$order->status] ?? [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-600',
                                        'label' => ucfirst($order->status),
                                    ];

                                    // Avatar initials
                                    $words = explode(' ', trim($order->customer->full_name ?? 'U'));
                                    $initials = strtoupper(
                                        substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''),
                                    );

                                    // Cycle through colors for avatar
                                    $colorKeys = array_keys($colors);
                                    $avatarColor = $colors[$colorKeys[$index % count($colorKeys)]];

                                    $currentIndex = array_search($order->status, $statusSequence);
                                    $nextStatus =
                                        $currentIndex !== false && isset($statusSequence[$currentIndex + 1])
                                            ? $statusSequence[$currentIndex + 1]
                                            : null;

                                    // Admin tenant dapat menjalankan seluruh alur operasional.
                                    $isPickup = $nextStatus === 'selesai';
                                    $isAdmin = auth()->user()->role === 'admin';
                                    $canAdvance = $nextStatus !== null
                                        && ($isAdmin || ($isPickup ? $isKasir : $isOperator));
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}"
                                            class="font-medium text-[#0B57D0] hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold {{ $avatarColor['bg'] }} {{ $avatarColor['text'] }}">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">
                                                    {{ $order->customer->full_name ?? 'Unknown Customer' }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $order->customer->phone_number ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $status['bg'] }} {{ $status['text'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($canAdvance && $nextStatus === 'siap_diambil' && isset($racks) && $racks->isNotEmpty())
                                            <select name="rack_id" form="status-form-{{ $order->id }}" required
                                                class="rounded-lg border-indigo-300 bg-indigo-50/60 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-xs font-bold text-indigo-900 py-1.5 px-2.5 shadow-sm cursor-pointer">
                                                <option value="" disabled selected>&mdash; Pilih Rak * &mdash;</option>
                                                @foreach ($racks as $rack)
                                                    <option value="{{ $rack->id }}">
                                                        Rak {{ $rack->name }}{{ $rack->location_description ? ' (' . $rack->location_description . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif ($order->rack)
                                            <span title="{{ $order->rack->location_description ?? '' }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-bold border border-purple-200">
                                                Rak {{ $order->rack->name }}
                                            </span>
                                        @elseif ($order->status === 'selesai')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                                Sudah Diambil
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-gray-500 text-xs font-medium">
                                                Proses Produksi
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($order->payment_status === 'lunas')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                                Lunas
                                            </span>
                                        @elseif ($order->payment_status === 'dp')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700">
                                                DP
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-600">
                                                Belum Bayar
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <p class="text-sm text-gray-700">{{ $order->received_date->format('d/m/Y') }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if ($order->estimated_done_date)
                                            @if ($order->status === 'selesai')
                                                <p class="text-sm text-emerald-600 font-medium">Selesai Diambil</p>
                                            @else
                                                <p
                                                    class="text-sm {{ $order->estimated_done_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                                    {{ $order->estimated_done_date->isToday() ? 'Hari ini' : $order->estimated_done_date->format('d/m/Y') }}
                                                </p>
                                            @endif
                                        @else
                                            <p class="text-sm text-gray-400">-</p>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('orders.show', $order) }}" title="View Details"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </a>
                                            @if ($canAdvance)
                                                @if ($nextStatus === 'siap_diambil' && (!isset($racks) || $racks->isEmpty()))
                                                    {{-- Jika belum ada master rak aktif, arahkan ke detail order --}}
                                                    <a href="{{ route('orders.show', $order) }}"
                                                        title="Pilih Lokasi Rak di detail order"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded bg-[#0B57D0] hover:bg-blue-800 text-white transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <form id="status-form-{{ $order->id }}" method="POST"
                                                        action="{{ route('orders.status.update', $order) }}"
                                                        onsubmit="return confirm('Ubah status order {{ $order->order_number }} dari &quot;{{ $status['label'] }}&quot; menjadi &quot;{{ ucfirst(str_replace('_', ' ', $nextStatus)) }}&quot;?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status"
                                                            value="{{ $nextStatus }}">
                                                        <button type="submit"
                                                            title="Next Status: {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded {{ $isPickup ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#0B57D0] hover:bg-blue-800' }} text-white transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M9 5l7 7-7 7"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <div class="w-8 h-8"></div>
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
    </div>
@endsection
