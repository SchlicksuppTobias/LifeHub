<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$reader->setReadDataOnly(true);

$spreadsheet = $reader->load(__DIR__ . '/BLS_4_0_Daten_2025_DE.xlsx');
$sheet = $spreadsheet->getActiveSheet();

$testData[] = $sheet->getRowIterator(2)->current()->getCellIterator();
var_dump($testData);
exit();


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

$query = 'INSERT INTO NutritionContents
VALUES (
    $row["BLS Code"], $row["Lebensmittelbezeichnung"], $row["Food name"], $row["ENERCJ Energie (Kilojoule) [kJ/100g]"],
    $row["ENERCC Energie (Kilokalorien) [kcal/100g]"], $row["WATER Wasser [g/100g]"], $row["PROT625 Protein (Nx6,25) [g/100g]"],
    $row["FAT Fett [g/100g]"], $row["CHO Kohlenhydrate, verfügbar [g/100g]"], $row["FIBT Ballaststoffe, gesamt [g/100g]"],
    $row["ALC Alkohol (Ethanol) [g/100g]"], $row["OA Organische Säuren, gesamt [g/100g]"], $row["ASH Rohasche [g/100g]"],
    $row["VITA Vitamin A, Retinol-Äquivalent (RE) [µg/100g]"], $row["VITAA Vitamin A, Retinol-Aktivitäts-Äquivalent (RAE) [µg/100g]"],
    $row["RETOL Retinol [µg/100g]"], $row["CARTB Beta‑Carotin [µg/100g]"], $row["CAROTPAXB Carotinoide, außer Beta-Carotin [µg/100g]"],
    $row["VITD Vitamin D [µg/100g]"], $row["CHOCAL Vitamin D3 (Cholecalciferol) [µg/100g]"], $row["ERGCAL Vitamin D2 (Ergocalciferol) [µg/100g]"],
    $row["VITE Vitamin E (Alpha-Tocopherol) [mg/100g]"], $row["TOCPHA Alpha‑Tocopherol [mg/100g]"], $row["TOCPHB Beta-Tocopherol [mg/100g]"],
    $row["TOCPHG Gamma-Tocopherol [mg/100g]"], $row["TOCPHD Delta-Tocopherol [mg/100g]"], $row["TOCTRA Alpha-Tocotrienol [mg/100g]"],
    $row["VITK Vitamin K [µg/100g]"], $row["VITK1 Vitamin K1 (Phyllochinon) [µg/100g]"], $row["VITK2 Vitamin K2 (Menachinone) [µg/100g]"],
    $row["THIA Vitamin B1 (Thiamin) [mg/100g]"], $row["RIBF Vitamin B2 (Riboflavin) [mg/100g]"], $row["NIAEQ Niacin-Äquivalent [mg/100g]"],
    $row["NIA Niacin [mg/100g]"], $row["PANTAC Pantothensäure [mg/100g]"], $row["VITB6 Vitamin B6 [µg/100g]"], $row["BIOT Biotin [µg/100g]"],
    $row["FOL Folat-Äquivalent [µg/100g]"], $row["FOLFD Folat [µg/100g]"], $row["FOLAC Folsäure, synthetisch [µg/100g]"],
    $row["VITB12 Vitamin B12 (Cobalamine) [µg/100g]"], $row["VITC Vitamin C [mg/100g]"], $row["NACL Salz (Natriumchlorid) [g/100g]"],
    $row["NA Natrium [mg/100g]"], $row["CLD Chlorid [mg/100g]"], $row["K Kalium [mg/100g]"], $row["CA Calcium [mg/100g]"],
    $row["MG Magnesium [mg/100g]"], $row["P Phosphor [mg/100g]"], $row["S Schwefel [mg/100g]"], $row["FE Eisen [mg/100g]"],
    $row["ZN Zink [mg/100g]"], $row["ID Iodid [µg/100g]"], $row["CU Kupfer [µg/100g]"], $row["MN Mangan [µg/100g]"],
    $row["FD Fluorid [µg/100g]"], $row["CR Chrom [µg/100g]"], $row["MO Molybdän [µg/100g]"], $row["ACEAC Essigsäure [g/100g]"],
    $row["CITAC Zitronensäure [g/100g]"], $row["LACAC Milchsäure [g/100g]"], $row["MALAC Äpfelsäure [g/100g]"],
    $row["TARAC Weinsäure [g/100g]"], $row["POLYL Zuckeralkohole, gesamt [g/100g]"], $row["MANTL Mannit [g/100g]"],
    $row["SORTL Sorbit [g/100g]"], $row["XYLTL Xylit [g/100g]"], $row["MNSAC Monosaccharide, gesamt [g/100g]"],
    $row["GLUS Glucose [g/100g]"], $row["FRUS Fructose [g/100g]"], $row["GALS Galactose [g/100g]"],
    $row["DISAC Disaccharide, gesamt [g/100g]"], $row["SUCS Saccharose [g/100g]"], $row["MALS Maltose [g/100g]"],
    $row["LACS Lactose [g/100g]"], $row["SUGAR Zucker (Mono- und Disaccharide), gesamt [g/100g]"], $row["OLSAC Oligosaccharide, verfügbar [g/100g]"],
    $row["STARCH Stärke (Stärke, Glykogen, Dextrine) [g/100g]"], $row["FIBLMW Ballaststoffe, niedermolekular [g/100g]"], $row["FIBHMW Ballaststoffe, hochmolekular [g/100g]"],
    $row["FIBINS Ballaststoffe, wasserunlöslich [g/100g]"], $row["FIBSOL Ballaststoffe, wasserlöslich [g/100g]"], $row["FIBHMWS Ballaststoffe, hochmolekular, wasserlöslich [g/100g]"],
    $row["FIBHMWI Ballaststoffe, hochmolekular, wasserunlöslich [g/100g]"], $row["FASAT Fettsäuren, gesättigt, gesamt [g/100g]"],
    $row["F4:0 Fettsäure C4:0 (Buttersäure) [g/100g]"], $row["F6:0 Fettsäure C6:0 (Capronsäure) [g/100g]"], $row["F8:0 Fettsäure C8:0 (Caprylsäure) [g/100g]"],
    $row["F10:0 Fettsäure C10:0 (Caprinsäure) [g/100g]"], $row["F12:0 Fettsäure C12:0 (Laurinsäure) [g/100g]"], $row["F14:0 Fettsäure C14:0 (Myristinsäure) [g/100g]"],
    $row["F15:0 Fettsäure C15:0 (Pentadecylsäure) [g/100g]"], $row["F16:0 Fettsäure C16:0 (Palmitinsäure) [g/100g]"], $row["F17:0 Fettsäure C17:0 (Margarinsäure) [g/100g]"],
    $row["F18:0 Fettsäure C18:0 (Stearinsäure) [g/100g]"], $row["F20:0 Fettsäure C20:0 (Arachinsäure) [g/100g]"], $row["F22:0 Fettsäure C22:0 (Behensäure) [g/100g]"],
    $row["F24:0 Fettsäure C24:0 (Lignocerinsäure) [g/100g]"], $row["FAMS Fettsäure, einfach ungesättigt, gesamt [g/100g]"], $row["F14:1CN5 Fettsäure C14:1 n-5 cis (Myristoleinsäure) [g/100g]"],
    $row["F16:1CN7 Fettsäure C16:1 n-7 cis (Palmitoleinsäure) [g/100g]"], $row["F18:1CN7 Fettsäure C18:1 n-7 cis (Vaccensäure) [g/100g]"],
    $row["F18:1CN9 Fettsäure C18:1 n-9 cis (Ölsäure) [g/100g]"], $row["F20:1CN9 Fettsäure C20:1 n-9 cis (Gondosäure) [g/100g]"], $row["F22:1CN9 Fettsäure C22:1 n-9 cis (Erucasäure) [g/100g]"],
    $row["FAPU Fettsäuren, mehrfach ungesättigt, gesamt [g/100g]"], $row["FAPUN3 Fettsäuren, mehrfach ungesättigt n-3 (Omega-3), gesamt [g/100g]"],
    $row["F18:3CN3 Fettsäure C18:3 n-3 all-cis (Alpha-Linolensäure) [g/100g]"], $row["F18:4CN3 Fettsäure C18:4 n-3 all-cis (Stearidonsäure) [g/100g]"],
    $row["F20:5CN3 Fettsäure C20:5 n-3 all-cis (Eicosapentaensäure) [g/100g]"], $row["F22:5CN3 Fettsäure C22:5 n-3 all-cis (Docosapentaensäure) [g/100g]"],
    $row["F22:6CN3 Fettsäure C22:6 n-3 all-cis (Docosahexaensäure) [g/100g]"], $row["FAPUN6 Fettsäuren, mehrfach ungesättigt n-6 (Omega-6), gesamt [g/100g]"],
    $row["F18:2CN6 Fettsäure C18:2 n-6 cis, cis (Linolsäure) [g/100g]"], $row["F18:2C9T11 Fettsäure C18:2 n-7 cis 9, trans 11 (konjugierte Linolsäure) [g/100g]"],
    $row["F18:3CN6 Fettsäure C18:3 n-6 all-cis (Gamma-Linolensäure) [g/100g]"], $row["F20:2CN6 Fettsäure C20:2 n-6 all-cis (Eicosadiensäure) [g/100g]"],
    $row["F20:3CN6 Fettsäure C20:3 n-6 all-cis (Dihomogamma-Linolensäure) [g/100g]"], $row["F20:4CN6 Fettsäure C20:4 n-6 all-cis (Arachidonsäure) [g/100g]"],
    $row["FAX Fettsäuren, sonstige [g/100g]"], $row["CHORL Cholesterin [mg/100g]"], $row["AAE9 Aminosäuren, unentbehrlich, gesamt [g/100g]"],
    $row["ALA Alanin [g/100g]"], $row["ARG Arginin [g/100g]"], $row["ASP Asparaginsäure, inklusive Asparagin [g/100g]"], $row["CYSTE Cystein [g/100g]"],
    $row["GLU Glutaminsäure, inklusive Glutamin [g/100g]"], $row["GLY Glycin [g/100g]"], $row["HIS Histidin [g/100g]"], $row["ILE Isoleucin [g/100g]"],
    $row["LEU Leucin [g/100g]"], $row["LYS Lysin [g/100g]"], $row["MET Methionin [g/100g]"], $row["PHE Phenylalanin [g/100g]"],
    $row["PRO Prolin [g/100g]"], $row["SER Serin [g/100g]"], $row["THR Threonin [g/100g]"], $row["TRP Tryptophan [g/100g]"],
    $row["TYR Tyrosin [g/100g]"], $row["VAL Valin [g/100g]"], $row["NT Stickstoff, gesamt [g/100g]"], $row["Hinweis"]
)';

echo "<pre>";
print_r($row);