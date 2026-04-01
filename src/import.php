<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$reader->setReadDataOnly(true);

$spreadsheet = $reader->load(__DIR__ . '/BLS_4_0_Daten_2025_DE.xlsx');
$sheet = $spreadsheet->getActiveSheet();

// Header (Zeile 1)
$header = [];
$headerRow = $sheet->getRowIterator(1,1)->current();
$cellIterator = $headerRow->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);

foreach ($cellIterator as $cell) {
    $header[] = $cell->getValue();
}

// Erste Datenzeile (Zeile 2)
$dataRow = $sheet->getRowIterator(2,2)->current();
$cellIterator = $dataRow->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);

$data = [];
foreach ($cellIterator as $cell) {
    $data[] = $cell->getValue();
}

// Kombinieren: Spaltenname => Wert
$row = array_combine($header, $data);

echo "<pre>";
print_r($row);