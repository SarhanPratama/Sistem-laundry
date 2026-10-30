<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Laundry - {{ $appStoreName ?? 'Sistem Laundry' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">

<div class="w-full max-w-md">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white shadow-xl mb-6">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Cek Status Pesanan</h1>
        <p class="text-gray-600">Masukkan nomor pesanan Anda untuk melihat progres pengerjaan.</p>
    </div>

    {{-- Alert --}}
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Form Search --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8">
        <form action="{{ route('tracking.search') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="order_number" class="block text-sm font-semibold text-gray-700 mb-2">Nomor Pesanan</label>
                <input type="text" id="order_number" name="order_number" required
                       class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors uppercase font-mono tracking-widest text-center text-lg"
                       placeholder="CONT: ORD-20261239">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-colors focus:ring-4 focus:ring-blue-300">
                Lacak Pesanan
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="mt-10 text-center text-sm text-gray-500">
        <p>Butuh bantuan? Silakan hubungi admin kami.</p>
        <p class="mt-1">&copy; {{ date('Y') }} Sistem Laundry. All rights reserved.</p>
    </div>

</div>

</body>
</html>
