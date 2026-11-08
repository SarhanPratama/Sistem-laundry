<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Untuk laundry kecil yang baru mulai digitalisasi operasional.',
                'price_monthly' => 49000,
                'price_yearly' => 490000,
                'max_outlets' => 1,
                'max_staff' => 2,
                'features' => [
                    '1 Outlet Laundry',
                    'Maksimal 2 Akun Staf (Kasir/Operator)',
                    'Manajemen Order & Data Pelanggan',
                    'Tracking Pesanan Publik (Resi)',
                    'Laporan Pendapatan Dasar',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Paket rekomendasi untuk laundry dengan operasional harian yang aktif.',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'max_outlets' => 1,
                'max_staff' => 5,
                'features' => [
                    'Semua fitur Starter',
                    'Maksimal 5 Akun Staf (Kasir/Operator)',
                    'Pembayaran DP, Pelunasan, & Cetak Struk',
                    'Manajemen Tahapan Produksi & Lokasi Rak',
                    'Laporan Lengkap & Ekspor PDF / Excel',
                    'Dukungan Prioritas WhatsApp',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Untuk usaha laundry yang membutuhkan kapasitas staf dan skala lebih besar.',
                'price_monthly' => 199000,
                'price_yearly' => 1990000,
                'max_outlets' => 3,
                'max_staff' => 15,
                'features' => [
                    'Semua fitur Business',
                    'Hingga 3 Outlet Laundry',
                    'Hingga 15 Akun Staf',
                    'Laporan Konsolidasi Lintas Usaha',
                    'Dukungan Teknis VIP 24/7',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }
    }
}

