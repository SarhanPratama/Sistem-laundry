@extends('layouts.app')
@section('title', 'Tambah Rak')
@section('page-title', 'Tambah Rak Baru')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('racks.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Rak Penyimpanan</h1>
    </div>

    <div class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6">
        <form action="{{ route('racks.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Rak / Penamaan Lokasi <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Rak A-1" class="pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Tambahan / Petunjuk <span class="text-red-500">*</span></label>
                <input type="text" name="location_description" value="{{ old('location_description') }}" required placeholder="Contoh: Posisi lorong belakang tingkat dua" class="pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                @error('location_description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Rak ini aktif dan bisa digunakan
                </label>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('racks.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm">
                    Simpan Rak
                </button>
            </div>
        </form>
    </div>
@endsection
