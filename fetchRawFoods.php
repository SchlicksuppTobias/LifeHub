<?php

$host = "localhost";
$user = "root";
$pass = "root";
$db   = "LifeHub";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Verbindungsfehler: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

/*
|--------------------------------------------------------------------------
| Query: eigenständiges Wort 'roh'
|--------------------------------------------------------------------------
*/
$query = "
SELECT name_de, bls_code
FROM NutritionContents
WHERE
    (
        LEFT(bls_code, 1) IN ('K','O','F','G','L','M','N','B','H','T','U','R')
            OR name_de REGEXP '[[:<:]]roh[[:>:]]'
        )
  AND LOWER(name_de) NOT REGEXP
      'zubereitet|gegart|frittiert|fertiggericht|konserve|tiefkühl|pizza|lasagne|burger|menü|gebraten|gebacken|grill|braten|schmortopf|eintopf|auflauf|suppe|saucen|soßen|dressings|gewürzmischungen'
  AND LOWER(name_de) NOT REGEXP
      '(fertig|mix|pulver|sauce|soße|dress|gewürzmisch)';
";

$result = $mysqli->query($query);

if (!$result) {
    die("Fehler bei der Abfrage: " . $mysqli->error);
}

/*
|--------------------------------------------------------------------------
| INSERT Datei erzeugen
|--------------------------------------------------------------------------
*/
$sqlOutput  = "-- Auto generated INSERT file\n\n";

$count = 0;

while ($row = $result->fetch_assoc()) {
    $name = $mysqli->real_escape_string($row['name_de']);
    $bls  = $mysqli->real_escape_string($row['bls_code']);

    $sqlOutput .= "INSERT INTO nutrition_raw (name_de, bls_code) 
VALUES ('$name', '$bls');\n";

    $count++;
}

/*
|--------------------------------------------------------------------------
| Datei schreiben
|--------------------------------------------------------------------------
*/
$filename = "nutrition_raw_inserts.sql";

if (file_put_contents($filename, $sqlOutput) === false) {
    die("Fehler beim Schreiben der Datei.");
}

echo "$filename wurde erfolgreich erstellt mit $count INSERTS.";

$result->free();
$mysqli->close();