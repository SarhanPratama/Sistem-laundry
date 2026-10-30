@extends('layouts.app')
@section('title', 'Edit Rak')
@section('page-title', 'Perbarui Data Rak')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('racks.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Perbarui Rak Penyimpanan</h1>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6">
        <form action="{{ route('racks.update', $rack) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Rak / Penamaan Lokasi <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $rack->name) }}" required placeholder="Contoh: Rak A-1" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Tambahan / Petunjuk <span class="text-red-500">*</span></label>
                <input type="text" name="location_description" value="{{ old('location_description', $rack->location_description) }}" required placeholder="Contoh: Posisi lorong belakang tingkat dua" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                @error('location_description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rack->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Rak ini aktif dan bisa digunakan
                </label>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('racks.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm">
                    Perbarui Rak
                </button>
            </div>
        </form>
    </div>
@endsection
