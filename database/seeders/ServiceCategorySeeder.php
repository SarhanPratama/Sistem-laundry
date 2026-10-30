<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kiloan',   'description' => 'Layanan cuci berdasarkan berat (kg)', 'is_active' => true],
            ['name' => 'Satuan',   'description' => 'Layanan cuci per item / helai',        'is_active' => true],
            ['name' => 'Ekspres',  'description' => 'Layanan kilat selesai dalam 24 jam',   'is_active' => true],
            ['name' => 'Karpet',   'description' => 'Layanan cuci karpet dan permadani',    'is_active' => true],
            ['name' => 'Dry Clean','description' => 'Layanan dry cleaning profesional',     'is_active' => true],
            ['name' => 'Paket',    'description' => 'Layanan paket siap pakai',             'is_active' => true],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
