@extends('layouts.app')

@section('title', 'Edit Layanan')
@section('page-title', 'Edit Layanan')

@section('content')
<div class="mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('services.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Layanan</h1>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('services.update', $service) }}">
            @csrf @method('PUT')

            {{-- Kategori --}}
            <div class="mb-5">
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="category_id" name="category_id" required
                            class="flex-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('service-categories.create') }}"
                       class="px-3 py-2.5 text-xs text-blue-600 hover:text-blue-800 border border-blue-200 rounded-xl whitespace-nowrap transition">
                        + Kategori Baru
                    </a>
                </div>
                @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Nama --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Layanan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}"
                       class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Satuan --}}
            <div class="mb-5">
                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">
                    Satuan <span class="text-red-500">*</span>
                </label>
                <select id="unit" name="unit"
                        class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('unit') ? 'border-red-400' : 'border-gray-200' }}">
                    <option value="">-- Pilih Satuan --</option>
                    <option value="kg"    {{ old('unit', $service->unit) === 'kg'    ? 'selected' : '' }}>Kilogram (kg)</option>
                    <option value="item"  {{ old('unit', $service->unit) === 'item'  ? 'selected' : '' }}>Item / Satuan</option>
                    <option value="paket" {{ old('unit', $service->unit) === 'paket' ? 'selected' : '' }}>Paket</option>
                </select>
                @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Harga --}}
            <div class="mb-6">
                <label for="price_per_unit" class="block text-sm font-medium text-gray-700 mb-1">
                    Harga per Satuan (Rp) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="price_per_unit" name="price_per_unit"
                       value="{{ old('price_per_unit', $service->price_per_unit) }}" min="0.01" step="0.01"
                       class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('price_per_unit') ? 'border-red-400' : 'border-gray-200' }}">
                @error('price_per_unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
                    Perbarui Layanan
                </button>
                <a href="{{ route('services.index') }}"
                   class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
