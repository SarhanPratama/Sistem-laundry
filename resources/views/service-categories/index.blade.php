@extends('layouts.app')

@section('title', 'Kategori Layanan')
@section('page-title', 'Kategori Layanan')

@section('content')
<div class="mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kategori Layanan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola kategori untuk mengelompokkan layanan laundry.</p>
        </div>
        <a href="{{ route('service-categories.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition shadow-sm w-full sm:w-auto">
            + Tambah Kategori
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 sm:p-6">
        @if ($categories->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">Belum ada kategori.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-100 text-left">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Deskripsi</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Layanan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-center no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach ($categories as $category)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3.5 font-medium text-gray-900">
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-500 max-w-xs truncate">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">{{ $category->services_count }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($category->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                {{-- Desktop Actions --}}
                                <div class="hidden sm:inline-flex items-center space-x-1 sm:space-x-2">
                                    <a href="{{ route('service-categories.edit', $category) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition shadow-xs">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('service-categories.toggle', $category) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-white text-xs font-medium rounded-lg transition shadow-xs {{ $category->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600' }}">
                                            {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('service-categories.destroy', $category) }}" class="inline"
                                          onsubmit="return confirm('Hapus kategori ini? Layanan terkait tidak akan dihapus.')">
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
                                            <a href="{{ route('service-categories.edit', $category) }}"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-yellow-600 hover:bg-yellow-50 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('service-categories.toggle', $category) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs {{ $category->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                        <div class="py-1">
                                            <form method="POST" action="{{ route('service-categories.destroy', $category) }}"
                                                  onsubmit="return confirm('Hapus kategori ini? Layanan terkait tidak akan dihapus.')">
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
