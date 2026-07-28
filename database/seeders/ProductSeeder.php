<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $food = Category::firstOrCreate(['name' => 'Đồ ăn', 'type' => 'product']);
        $drink = Category::firstOrCreate(['name' => 'Đồ uống', 'type' => 'product']);
        $service = Category::firstOrCreate(['name' => 'Thuê bàn', 'type' => 'service']);

        $unitCai = Unit::firstOrCreate(['name' => 'Cái'], ['abbreviation' => 'cái']);
        $unitChai = Unit::firstOrCreate(['name' => 'Chai'], ['abbreviation' => 'chai']);
        $unitLy = Unit::firstOrCreate(['name' => 'Ly'], ['abbreviation' => 'ly']);
        $unitDia = Unit::firstOrCreate(['name' => 'Đĩa'], ['abbreviation' => 'đĩa']);
        $unitGoi = Unit::firstOrCreate(['name' => 'Gói'], ['abbreviation' => 'gói']);
        $unitGio = Unit::firstOrCreate(['name' => 'Giờ'], ['abbreviation' => 'giờ']);

        Product::updateOrCreate(
            ['name' => 'Tiền giờ 40k'],
            [
                'category_id' => $service->id,
                'unit_id' => $unitGio->id,
                'price' => 40000,
                'stock' => 999999,
                'unit' => 'giờ',
                'cost' => 0,
                'min_stock' => 0,
                'track_stock' => false,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Tiền giờ 50k'],
            [
                'category_id' => $service->id,
                'unit_id' => $unitGio->id,
                'price' => 50000,
                'stock' => 999999,
                'unit' => 'giờ',
                'cost' => 0,
                'min_stock' => 0,
                'track_stock' => false,
            ]
        );

        $products = [
            ['name' => 'Coca Cola', 'category_id' => $drink->id, 'price' => 20000, 'stock' => 100, 'unit_id' => $unitChai->id],
            ['name' => 'Pepsi', 'category_id' => $drink->id, 'price' => 20000, 'stock' => 80, 'unit_id' => $unitChai->id],
            ['name' => 'Nước suối', 'category_id' => $drink->id, 'price' => 10000, 'stock' => 200, 'unit_id' => $unitChai->id],
            ['name' => 'Bánh mì', 'category_id' => $food->id, 'price' => 25000, 'stock' => 30, 'unit_id' => $unitCai->id],
            ['name' => 'Snack', 'category_id' => $food->id, 'price' => 15000, 'stock' => 50, 'unit_id' => $unitGoi->id],
        ];
        foreach ($products as $p) {
            Product::updateOrCreate(['name' => $p['name']], array_merge($p, ['unit' => 'cái', 'cost' => $p['price'] * 0.5, 'min_stock' => 10]));
        }
    }
}
