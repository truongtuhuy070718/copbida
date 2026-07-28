<?php

namespace Database\Seeders;

use App\Models\GameTable;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $areas = ['Khu A', 'Khu B', 'Khu VIP'];
        foreach ($areas as $area) {
            for ($i = 1; $i <= 4; $i++) {
                GameTable::create(['name' => $area . ' - Bàn ' . $i, 'area' => $area, 'price_per_hour' => ($area === 'Khu VIP' ? 80000 : 50000)]);
            }
        }
    }
}
