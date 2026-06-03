<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

try {
    $db = new MariaDbConnection(
        $envHandler->getEnv('DB_HOST'),
        $envHandler->getEnv('DB_USERNAME'),
        $envHandler->getEnv('DB_PASSWORD'),
        $envHandler->getEnv('DB_DATABASE')
    );

    $userId = 1; // später aus JWT/Session

    $stmt = $db->prepare("
        SELECT
            id,
            title,
            completed,
            created_at
        FROM todos
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    $todos = [];

    while ($row = $result->fetch_assoc()) {
        $todos[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $todos
    ]);

    $stmt->close();
    $db->close();

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}