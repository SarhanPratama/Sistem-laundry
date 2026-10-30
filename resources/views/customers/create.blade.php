@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')
@section('page-title', 'Tambah Pelanggan')

@section('content')
<div class="mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Pelanggan Baru</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <div class="mb-5">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="{{ old('full_name') }}"
                    class="w-full px-3 py-2 border {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan nama lengkap"
                >
                @error('full_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                    Nomor Telepon <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="phone_number"
                    name="phone_number"
                    value="{{ old('phone_number') }}"
                    class="w-full px-3 py-2 border {{ $errors->has('phone_number') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: 08123456789"
                >
                @error('phone_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    Alamat
                </label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="w-full px-3 py-2 border {{ $errors->has('address') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan alamat (opsional)"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('customers.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition duration-150">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition duration-150">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
