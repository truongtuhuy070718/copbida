<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $food = Category::create(['name' => 'Đồ ăn', 'type' => 'product']);
        $drink = Category::create(['name' => 'Đồ uống', 'type' => 'product']);
        Category::create(['name' => 'Thuê bàn', 'type' => 'service']);

        $service = Category::where('name', 'Thuê bàn')->first();
        Product::create([
            'name' => 'Tiền giờ',
            'category_id' => $service->id,
            'price' => 40000,
            'stock' => 999999,
            'unit' => 'giờ',
            'cost' => 0,
            'min_stock' => 0,
            'track_stock' => false,
        ]);

        $products = [
            ['name' => 'Coca Cola', 'category_id' => $drink->id, 'price' => 20000, 'stock' => 100],
            ['name' => 'Pepsi', 'category_id' => $drink->id, 'price' => 20000, 'stock' => 80],
            ['name' => 'Nước suối', 'category_id' => $drink->id, 'price' => 10000, 'stock' => 200],
            ['name' => 'Bánh mì', 'category_id' => $food->id, 'price' => 25000, 'stock' => 30],
            ['name' => 'Snack', 'category_id' => $food->id, 'price' => 15000, 'stock' => 50],
        ];
        foreach ($products as $p) {
            Product::create(array_merge($p, ['unit' => 'cái', 'cost' => $p['price'] * 0.5, 'min_stock' => 10]));
        }
    }
}
