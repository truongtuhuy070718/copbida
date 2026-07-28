<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../bootstrap/app.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$excel = new COM("Excel.Application") or die("Không mở được Excel");
$excel->Visible = false;
$wb = $excel->Workbooks->Open('c:\\Users\\hdaut\\Downloads\\DanhSachKhoHang_KV28072026-225759-020.xlsx');
$ws = $wb->Worksheets(1);

$rows = $ws->UsedRange->Rows->Count;
$cols = $ws->UsedRange->Columns->Count;

echo "Tổng dòng: $rows, Cột: $cols\n";

$headers = [];
for ($c = 1; $c <= $cols; $c++) {
    $headers[] = trim($ws->Cells(1, $c)->Value);
}
echo "Headers: " . implode(', ', $headers) . "\n";

$categoryCache = [];
$unitCache = [];
$imported = 0;

for ($r = 2; $r <= $rows; $r++) {
    $tenHang = trim($ws->Cells($r, 4)->Value);
    $maHang = trim($ws->Cells($r, 3)->Value);
    $giaVon = str_replace(',', '', $ws->Cells($r, 5)->Value);
    $tonKho = (float) str_replace(',', '', $ws->Cells($r, 6)->Value);
    $donVi = trim($ws->Cells($r, 9)->Value);
    $nhomHang = trim($ws->Cells($r, 2)->Value);

    if (empty($tenHang)) continue;

    if (!isset($categoryCache[$nhomHang])) {
        $cat = Category::firstOrCreate(['name' => $nhomHang, 'type' => 'product']);
        $categoryCache[$nhomHang] = $cat->id;
    }
    $categoryId = $categoryCache[$nhomHang];

    if (!empty($donVi) && !isset($unitCache[$donVi])) {
        $unit = Unit::firstOrCreate(['name' => $donVi], ['abbreviation' => $donVi]);
        $unitCache[$donVi] = $unit->id;
    }
    $unitId = !empty($donVi) ? $unitCache[$donVi] : null;

    Product::updateOrCreate(
        ['name' => $tenHang],
        [
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'unit' => $donVi ?: 'cái',
            'price' => (float) $giaVon,
            'cost' => (float) $giaVon * 0.5,
            'stock' => $tonKho,
            'min_stock' => 0,
            'track_stock' => true,
            'active' => true,
        ]
    );
    $imported++;
}

$wb->Close(false);
$excel->Quit();

echo "Import thành công $imported sản phẩm.\n";
