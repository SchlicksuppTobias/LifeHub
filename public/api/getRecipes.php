<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

$host = $envHandler->getEnv('DB_HOST');
$user = $envHandler->getEnv('DB_USERNAME');
$pass = $envHandler->getEnv('DB_PASSWORD');
$dbname = $envHandler->getEnv('DB_DATABASE');

try {
    $db = new MariaDbConnection($host, $user, $pass, $dbname);

    $search = $_GET['search'] ?? '';
    $search = trim($search);

    // Basis SQL
    $sql = "
        SELECT
            id,
            user_id,
            title,
            image_path,
            ingredients,
            instructions,
            prep_time_value,
            prep_time_unit,
            cook_time_value,
            cook_time_unit,
            rest_time_value,
            rest_time_unit,
            created_at
        FROM recipes_unverified
    ";

    $params = [];
    $types = '';

    // 🔍 SEARCH LOGIK
    if (!empty($search)) {
        $sql .= " WHERE title LIKE ? ";
        $params[] = "%{$search}%";
        $types .= 's';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($sql);

    // Wenn Suche aktiv → Parameter binden
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    $recipes = [];

    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }

    echo json_encode($recipes);

    $stmt->close();
    $db->close();

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}