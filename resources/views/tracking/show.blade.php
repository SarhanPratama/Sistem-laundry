<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Laundry - {{ $appStoreName ?? 'Sistem Laundry' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-md mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Tracking Laundry</h1>
        <p class="text-gray-600">Lacak status pengerjaan laundry Anda secara real-time</p>
    </div>

    {{-- Main Card --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        {{-- Order Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
            <p class="text-sm opacity-90 mb-1">Nomor Pesanan</p>
            <h2 class="text-3xl font-bold tracking-wide mb-6">{{ $order->order_number }}</h2>

            <div class="flex items-center gap-2">
                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium {{
                    $order->payment_status === 'lunas' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'
                }}">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>

        {{-- Order Details --}}
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Nama Pelanggan</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->customer->masked_name ?? $order->customer->full_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Nomor Telepon</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->customer->masked_phone ?? '********' }}</p>
                </div>
                <div>
                <p class="text-sm text-gray-500 mb-1">Tanggal Masuk</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->received_date->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Estimasi Selesai</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->estimated_done_date->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Current Status --}}
        @php
            $bgColors = [
                'blue' => 'bg-blue-50',
                'yellow' => 'bg-yellow-50',
                'orange' => 'bg-orange-50',
                'purple' => 'bg-purple-50',
                'green' => 'bg-green-50',
                'gray' => 'bg-gray-50',
                'red' => 'bg-red-50',
            ];
            $currentBg = $bgColors[$currentStatus['color']] ?? 'bg-blue-50';
        @endphp
        <div class="p-6 {{ $currentBg }} border-b border-gray-200">
            <p class="text-sm text-gray-600 mb-3 font-medium">Status Saat Ini</p>
            <div class="flex items-center gap-4 mb-3">
                <div class="text-5xl">{{ $currentStatus['icon'] }}</div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $currentStatus['label'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">Terakhir diperbarui: {{ $order->updated_at->translatedFormat('d F Y H:i') }}</p>
                </div>
            </div>

            @if($order->status === 'siap_diambil' && $order->rack)
            <div class="mt-4 p-4 bg-white border border-indigo-100 rounded-xl shadow-sm text-center">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-1">Lokasi Pengambilan Barang</p>
                <div class="flex items-center justify-center gap-2 text-xl font-bold text-indigo-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    {{ $order->rack->name }}
                </div>
                <p class="text-sm text-gray-600 mt-2">Pesanan Anda telah selesai dan disimpan di lokasi di atas. Tunjukkan layar ini ke kasir.</p>
            </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="p-6 border-b border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-6">Tahapan Pengerjaan</p>

            <div class="space-y-6">
                @foreach($timeline as $index => $step)
                    @php
                        // Untuk progress timeline, asumsikan index yg <= dari index status saat ini sudah "completed"
                        $currentStatusIndex = array_search($order->status, array_column($timeline, 'status'));
                        $isCompleted = !$order->is_cancelled && $currentStatusIndex !== false && $index <= $currentStatusIndex;
                        $isCurrent = !$order->is_cancelled && $order->status === $step['status'];
                    @endphp

                    <div class="flex gap-4">
                        {{-- Timeline dot --}}
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm {{
                                $isCurrent ? 'bg-blue-600 text-white shadow-lg scale-125' :
                                ($step['time'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600')
                            }} transition-all">
                                {{ $index + 1 }}
                            </div>
                            @if($index < count($timeline) - 1)
                                <div class="w-0.5 h-12 bg-gray-300 mt-2"></div>
                            @endif
                        </div>

                        {{-- Timeline content --}}
                        <div class="flex-1 pt-1">
                            <p class="font-semibold text-gray-900">{{ $step['label'] }}</p>
                            @if($step['time'])
                                <p class="text-sm text-gray-600">{{ $step['time']->translatedFormat('d F Y · H:i') }}</p>

                                @if($step['status'] === 'siap_diambil' && $order->rack)
                                    <div class="mt-2 bg-indigo-50 border border-indigo-100 rounded p-3 inline-block">
                                        <p class="text-sm font-semibold text-indigo-800 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                            Lokasi Penyimpanan: {{ $order->rack->name }}
                                        </p>
                                        <p class="text-xs text-indigo-600 mt-0.5">Tunjukkan bukti resi ini ke kasir untuk mengambil pesanan Anda.</p>
                                    </div>
                                @endif

                            @else
                                <p class="text-sm text-gray-400 italic">Menunggu proses...</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Order Items --}}
        <div class="p-6 border-b border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-4">Detail Pesanan</p>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-start p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $item->service->name ?? 'Layanan' }}</p>
                            <p class="text-sm text-gray-600">Kategori: {{ $item->service->category->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ $item->quantity }} {{ $item->service->unit ?? '' }}</p>
                            <p class="text-sm text-gray-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payment & Total --}}
        <div class="p-6 border-b border-gray-200">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pajak ({{ config('services.tax_percentage', 0) }}%)</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($order->tax ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-200 pt-3 flex justify-between items-center text-xl">
                    <span class="font-bold text-gray-900">Total Biaya</span>
                    <span class="font-bold text-blue-600">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Status --}}
        <div class="p-6">
            <p class="text-sm font-semibold text-gray-700 mb-4">Status Pembayaran</p>
            @if($order->payment_status === 'lunas')
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="flex items-center gap-2 text-green-800 font-medium">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Pembayaran Sudah Lunas
                    </p>
                </div>
            @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="flex items-center gap-2 text-yellow-800 font-medium mb-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Belum Ada Pembayaran
                    </p>
                    <p class="text-sm text-yellow-700">Silakan datang ke toko kami.</p>
                </div>
            @endif
        </div>

        {{-- Footer Help --}}
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-600 mb-3">
                <strong>Ada pertanyaan?</strong> Hubungi toko kami untuk informasi lebih lanjut.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('tracking.index') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                    ← Cek Pesanan Lain
                </a>
                <a href="/" class="text-gray-500 hover:text-gray-700 font-medium text-sm">
                    Kembali ke halaman utama
                </a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
