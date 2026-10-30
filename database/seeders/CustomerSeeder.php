<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['full_name' => 'Budi Santoso',      'phone_number' => '081234567890', 'address' => 'Jl. Mawar No. 12, Jakarta Selatan',    'is_active' => true],
            ['full_name' => 'Siti Rahayu',        'phone_number' => '082345678901', 'address' => 'Jl. Melati No. 5, Bandung',             'is_active' => true],
            ['full_name' => 'Ahmad Fauzi',        'phone_number' => '083456789012', 'address' => 'Jl. Kenanga No. 8, Surabaya',           'is_active' => true],
            ['full_name' => 'Dewi Lestari',       'phone_number' => '084567890123', 'address' => 'Jl. Anggrek No. 3, Yogyakarta',         'is_active' => true],
            ['full_name' => 'Rudi Hartono',       'phone_number' => '085678901234', 'address' => 'Jl. Dahlia No. 17, Semarang',           'is_active' => true],
            ['full_name' => 'Rina Wulandari',     'phone_number' => '086789012345', 'address' => 'Jl. Flamboyan No. 22, Medan',           'is_active' => true],
            ['full_name' => 'Hendra Kusuma',      'phone_number' => '087890123456', 'address' => 'Jl. Cempaka No. 9, Makassar',           'is_active' => true],
            ['full_name' => 'Yuni Astuti',        'phone_number' => '088901234567', 'address' => 'Jl. Teratai No. 14, Palembang',         'is_active' => true],
            ['full_name' => 'Doni Prasetyo',      'phone_number' => '089012345678', 'address' => 'Jl. Bougenville No. 6, Denpasar',       'is_active' => true],
            ['full_name' => 'Fitri Handayani',    'phone_number' => '081123456789', 'address' => 'Jl. Seruni No. 11, Malang',             'is_active' => true],
        ];

        foreach ($customers as $cust) {
            Customer::firstOrCreate(['phone_number' => $cust['phone_number']], $cust);
        }
    }
}
