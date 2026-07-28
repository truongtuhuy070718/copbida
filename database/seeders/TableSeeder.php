<?php

namespace Database\Seeders;

use App\Models\GameTable;
use App\Models\TableSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        TableSession::truncate();
        GameTable::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        for ($i = 1; $i <= 6; $i++) {
            GameTable::create([
                'name' => 'Tầng 1 - Bàn ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'area' => 'Tầng 1',
                'price_per_hour' => 50000,
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            GameTable::create([
                'name' => 'Tầng VIP - Bàn ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'area' => 'Tầng VIP',
                'price_per_hour' => 80000,
            ]);
        }
    }
}
