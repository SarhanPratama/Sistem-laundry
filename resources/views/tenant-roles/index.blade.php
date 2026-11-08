@extends('layouts.app')

@section('title', 'Akses & Role')
@section('page-title', 'Akses & Role')

@section('content')
<div class="mx-auto max-w-7xl">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Akses &amp; Role Staf</h1>
            <p class="mt-1 text-sm text-gray-500">Atur pembagian tugas dan hak akses fitur untuk setiap jabatan staf di laundry Anda.</p>
        </div>
        <a href="{{ route('tenant-roles.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Role Baru
        </a>
    </div>

    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($roles as $role)
            @php
                $permCount = count($role->permissions ?? []);
                $totalPerms = count($permissions);
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm flex flex-col justify-between hover:border-blue-300 transition">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $role->is_system ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-purple-50 text-purple-600 border border-purple-200' }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($role->name, 0, 2)) }}
                            </div>
                            <div>
                                <h2 class="font-bold text-gray-900 text-base leading-tight">{{ $role->name }}</h2>
                                <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $role->is_system ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ $role->is_system ? 'Role Bawaan' : 'Role Kustom' }}
                                </span>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-bold whitespace-nowrap">
                            {{ $permCount }} / {{ $totalPerms }} Modul
                        </span>
                    </div>

                    <div class="mb-5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Hak Akses Diizinkan</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($role->permissions as $permission)
                                <span class="rounded-lg bg-blue-50/80 border border-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $permissions[$permission] ?? $permission }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">Belum ada tugas yang dipilih</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                    <a href="{{ route('tenant-roles.edit', $role) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 text-xs font-bold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Atur Hak Akses
                    </a>

                    @if (!$role->is_system)
                        <form method="POST" action="{{ route('tenant-roles.destroy', $role) }}" onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500 md:col-span-2 lg:col-span-3">
                <p class="font-semibold text-gray-700">Belum ada role terdaftar.</p>
                <p class="text-xs text-gray-400 mt-1">Klik tombol "Buat Role Baru" di kanan atas untuk membuat role staf pertama Anda.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
