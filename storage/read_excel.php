<?php
require 'vendor/autoload.php';

$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load('c:/Users/hdaut/Downloads/DanhSachKhoHang_KV28072026-225759-020.xlsx');
$sheet = $spreadsheet->getActiveSheet();

echo 'Rows: ' . $sheet->getHighestRow() . PHP_EOL;
echo 'Cols: ' . $sheet->getHighestColumn() . PHP_EOL;

$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();

for ($row = 1; $row <= min(30, $highestRow); $row++) {
    for ($col = 'A'; $col <= $highestCol; $col++) {
        echo $sheet->getCell($col . $row)->getValue() . ' | ';
    }
    echo PHP_EOL;
}
