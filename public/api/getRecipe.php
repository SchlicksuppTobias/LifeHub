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

    $id = $_GET['id'] ?? '';
    $id = trim($id);

    if ($id === '' || !ctype_digit($id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Ungültige oder fehlende ID'
        ]);
        exit;
    }

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
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $recipe = $result->fetch_assoc();

    if (!$recipe) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Rezept nicht gefunden'
        ]);
        $stmt->close();
        $db->close();
        exit;
    }

    echo json_encode($recipe);

    $stmt->close();
    $db->close();

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
