<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$host   = 'mariadb';
$user   = 'root';
$pass   = 'root';
$dbname = 'LifeHub';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB-Verbindung fehlgeschlagen: ' . $conn->connect_error]);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT * FROM NutritionContents
        WHERE name_de LIKE ?
        ORDER BY name_de ASC
        LIMIT 100
    ");
    $like = '%' . $search . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM NutritionContents ORDER BY name_de ASC LIMIT 1");
}

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Abfrage fehlgeschlagen: ' . $conn->error]);
    exit;
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);

$conn->close();