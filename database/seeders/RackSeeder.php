<?php

namespace Database\Seeders;

use App\Models\Rack;
use Illuminate\Database\Seeder;

class RackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $racks = [
            ['name' => 'Rak A1', 'location_description' => 'Lorong Depan - Atas',    'is_active' => true],
            ['name' => 'Rak A2', 'location_description' => 'Lorong Depan - Tengah',  'is_active' => true],
            ['name' => 'Rak A3', 'location_description' => 'Lorong Depan - Bawah',   'is_active' => true],
            ['name' => 'Rak B1', 'location_description' => 'Lorong Tengah - Atas',   'is_active' => true],
            ['name' => 'Rak B2', 'location_description' => 'Lorong Tengah - Bawah',  'is_active' => true],
            ['name' => 'Gantungan 1', 'location_description' => 'Hanger Jas & Gaun',   'is_active' => true],
        ];

        foreach ($racks as $rack) {
            Rack::firstOrCreate(['name' => $rack['name']], $rack);
        }
    }
}
