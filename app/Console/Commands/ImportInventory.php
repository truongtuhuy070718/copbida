<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Console\Command;

class ImportInventory extends Command
{
    protected $signature = 'import:inventory {file}';
    protected $description = 'Import inventory from JSON file';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error('File không tồn tại: ' . $file);
            return 1;
        }

        $this->info('Đang xóa dữ liệu kho cũ...');
        Product::where('name', 'not like', 'Tiền giờ%')->delete();
        Category::where('name', '!=', 'Thuê bàn')->delete();

        $this->info('Đang đọc file JSON...');
        $json = file_get_contents($file);
        if (substr($json, 0, 3) === "\xEF\xBB\xBF") {
            $json = substr($json, 3);
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $this->error('Không đọc được dữ liệu JSON');
            return 1;
        }

        $categoryCache = [];
        $unitCache = [];
        $imported = 0;

        foreach ($data as $row) {
            $tenHang = trim($row['ten'] ?? '');
            $giaVon = (float) str_replace(',', '', $row['gia'] ?? '0');
            $tonKho = (float) str_replace(',', '', $row['ton'] ?? '0');
            $donVi = trim($row['dvt'] ?? '');
            $nhomHang = trim($row['nhom'] ?? 'Khác');

            if (empty($tenHang)) continue;

            if (!isset($categoryCache[$nhomHang])) {
                $cat = Category::firstOrCreate(['name' => $nhomHang, 'type' => 'product']);
                $categoryCache[$nhomHang] = $cat->id;
            }
            $categoryId = $categoryCache[$nhomHang];

            $unitId = null;
            if (!empty($donVi) && !isset($unitCache[$donVi])) {
                $unit = Unit::firstOrCreate(['name' => $donVi], ['abbreviation' => $donVi]);
                $unitCache[$donVi] = $unit->id;
            }
            if (!empty($donVi)) {
                $unitId = $unitCache[$donVi];
            }

            Product::create([
                'name' => $tenHang,
                'category_id' => $categoryId,
                'unit_id' => $unitId,
                'unit' => $donVi ?: 'cái',
                'price' => max($giaVon, 0),
                'cost' => max($giaVon * 0.5, 0),
                'stock' => $tonKho,
                'min_stock' => 0,
                'track_stock' => true,
                'active' => true,
            ]);
            $imported++;
        }

        $this->info("Import thành công $imported sản phẩm.");
        return 0;
    }
}
