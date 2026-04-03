<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load(__DIR__ . '/BLS_4_0_Daten_2025_DE.xlsx');
$sheet = $spreadsheet->getActiveSheet();

// Header aus Excel
$header = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1')[0];

// Alle Daten ab Zeile 2
$rows = $sheet->rangeToArray('A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow());

$sqlFile = __DIR__ . '/nutrition_inserts.sql';
file_put_contents($sqlFile, "-- Inserts für NutritionContents\n");

$template = file_get_contents(__DIR__ . '/insert_template.sql'); // hier steht deine komplette $query

foreach ($rows as $rowData) {
    $row = array_combine($header, $rowData);

    // Pflichtfelder prüfen
    if (empty($row['BLS Code']) || empty($row['Lebensmittelbezeichnung']) || empty($row['Food name'])) {
        continue;
    }

    // Werte vorbereiten
    $replacements = [];
    foreach ($row as $col => $val) {
        $val = trim($val);
        if (in_array($col, ['BLS Code', 'Lebensmittelbezeichnung', 'Food name'])) {
            $replacements[$col] = "'" . addslashes($val) . "'";
        } elseif ($col === 'Hinweis') {
            $replacements[$col] = ($val === '' ? 'NULL' : "'" . addslashes($val) . "'");
        } else {
            $replacements[$col] = is_numeric($val) ? $val : 'NULL';
        }
    }

    // Template ersetzen
    $sql = $template;
    foreach ($replacements as $col => $val) {
        $sql = str_replace('$row["' . $col . '"]', $val, $sql);
    }

    file_put_contents($sqlFile, $sql . "\n", FILE_APPEND);
}

echo "Fertig! SQL-Datei: $sqlFile\n";