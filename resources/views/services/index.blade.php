@extends('layouts.app')

@section('title', 'Manajemen Layanan')
@section('page-title', 'Layanan')

@section('content')
@php
    $colorMap = [
        'blue'   => 'bg-blue-100 text-blue-700',
        'green'  => 'bg-green-100 text-green-700',
        'red'    => 'bg-red-100 text-red-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'pink'   => 'bg-pink-100 text-pink-700',
        'teal'   => 'bg-teal-100 text-teal-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'gray'   => 'bg-gray-100 text-gray-700',
    ];
@endphp
<div class="mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Layanan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola layanan dan harga laundry.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('service-categories.index') }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition flex-1 sm:flex-initial">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Kategori
            </a>
            <a href="{{ route('services.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition shadow-sm flex-1 sm:flex-initial">
                Tambah Layanan
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 sm:p-6">
        @if ($services->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">Belum ada layanan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-100 text-left">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">No</th>
                        <th class="px-4 py-3.5">Nama Layanan</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Harga / Satuan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-center no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach ($services as $service)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3.5 text-gray-500 font-medium">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3.5 font-medium text-gray-900">
                                {{ $service->name }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($service->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorMap[$service->category->color] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $service->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-gray-700 font-semibold" data-order="{{ $service->price_per_unit }}">
                                Rp {{ number_format($service->price_per_unit, 0, ',', '.') }} {{ $service->unit }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($service->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                {{-- Desktop Actions (Horizontal) --}}
                                <div class="hidden sm:inline-flex items-center space-x-1 sm:space-x-2">
                                    <a href="{{ route('services.edit', $service) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition shadow-xs">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('services.deactivate', $service) }}" class="inline"
                                          onsubmit="return confirm('{{ $service->is_active ? 'Nonaktifkan layanan ini?' : 'Aktifkan layanan ini?' }}')">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-white text-xs font-medium rounded-lg transition shadow-xs {{ $service->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600' }}">
                                            {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('services.destroy', $service) }}" class="inline"
                                          onsubmit="return confirm('Hapus layanan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition shadow-xs">
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                {{-- Mobile Actions (3-Dots Dropdown) --}}
                                <div class="sm:hidden relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 z-30 mt-1 w-36 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none text-left"
                                         style="display: none;">
                                        <div class="py-1">
                                            <a href="{{ route('services.edit', $service) }}"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-yellow-600 hover:bg-yellow-50 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('services.deactivate', $service) }}"
                                                  onsubmit="return confirm('{{ $service->is_active ? 'Nonaktifkan layanan ini?' : 'Aktifkan layanan ini?' }}')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs {{ $service->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                        <div class="py-1">
                                            <form method="POST" action="{{ route('services.destroy', $service) }}"
                                                  onsubmit="return confirm('Hapus layanan ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
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
