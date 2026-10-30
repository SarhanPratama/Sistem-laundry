<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use RuntimeException;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = ServiceCategory::pluck('id', 'name');

        $category = static function (string $name) use ($categoryIds): int {
            $id = $categoryIds->get($name);

            if ($id === null) {
                throw new RuntimeException("Kategori layanan \"{$name}\" belum ada. Jalankan ServiceCategorySeeder lebih dulu.");
            }

            return $id;
        };

        $services = [
            // Kiloan
            ['category_id' => $category('Kiloan'),    'name' => 'Cuci Kiloan',           'unit' => 'kg',    'price_per_unit' => 7000,   'is_active' => true],
            ['category_id' => $category('Kiloan'),    'name' => 'Cuci + Setrika',        'unit' => 'kg',    'price_per_unit' => 10000,  'is_active' => true],
            ['category_id' => $category('Kiloan'),    'name' => 'Setrika Kiloan',        'unit' => 'kg',    'price_per_unit' => 5000,   'is_active' => true],
            // Satuan
            ['category_id' => $category('Satuan'),    'name' => 'Cuci Satuan',           'unit' => 'item',  'price_per_unit' => 15000,  'is_active' => true],
            ['category_id' => $category('Satuan'),    'name' => 'Setrika Satuan',        'unit' => 'item',  'price_per_unit' => 8000,   'is_active' => true],
            ['category_id' => $category('Satuan'),    'name' => 'Cuci Sepatu',           'unit' => 'item',  'price_per_unit' => 25000,  'is_active' => true],
            // Ekspres
            ['category_id' => $category('Ekspres'),   'name' => 'Cuci Ekspres',          'unit' => 'kg',    'price_per_unit' => 12000,  'is_active' => true],
            ['category_id' => $category('Ekspres'),   'name' => 'Cuci + Setrika Ekspres', 'unit' => 'kg',    'price_per_unit' => 15000,  'is_active' => true],
            // Karpet
            ['category_id' => $category('Karpet'),   'name' => 'Cuci Karpet Kecil',     'unit' => 'item',  'price_per_unit' => 50000,  'is_active' => true],
            ['category_id' => $category('Karpet'),   'name' => 'Cuci Karpet Besar',     'unit' => 'item',  'price_per_unit' => 100000, 'is_active' => true],
            // Dry Clean
            ['category_id' => $category('Dry Clean'), 'name' => 'Dry Clean Kemeja',     'unit' => 'item',  'price_per_unit' => 35000,  'is_active' => true],
            ['category_id' => $category('Dry Clean'), 'name' => 'Dry Clean Jas/Blazer', 'unit' => 'item',  'price_per_unit' => 75000,  'is_active' => true],
            // Paket
            ['category_id' => $category('Paket'),    'name' => 'Paket Cuci Bersih',    'unit' => 'paket', 'price_per_unit' => 50000,  'is_active' => true],
            ['category_id' => $category('Paket'),    'name' => 'Paket Keluarga',       'unit' => 'paket', 'price_per_unit' => 120000, 'is_active' => true],
        ];

        foreach ($services as $svc) {
            Service::firstOrCreate(['name' => $svc['name']], $svc);
        }
    }
}
