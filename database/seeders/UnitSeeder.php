<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Cái', 'abbreviation' => 'cái'],
            ['name' => 'Chai', 'abbreviation' => 'chai'],
            ['name' => 'Ly', 'abbreviation' => 'ly'],
            ['name' => 'Đĩa', 'abbreviation' => 'đĩa'],
            ['name' => 'Hộp', 'abbreviation' => 'hộp'],
            ['name' => 'Gói', 'abbreviation' => 'gói'],
            ['name' => 'Kg', 'abbreviation' => 'kg'],
            ['name' => 'Giờ', 'abbreviation' => 'giờ'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
