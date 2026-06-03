<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

try {

    $payload = json_decode(
        file_get_contents('php://input'),
        true
    );

    $todoId = (int)($payload['id'] ?? 0);
    $userId = 1;

    $db = new MariaDbConnection(
        $envHandler->getEnv('DB_HOST'),
        $envHandler->getEnv('DB_USERNAME'),
        $envHandler->getEnv('DB_PASSWORD'),
        $envHandler->getEnv('DB_DATABASE')
    );

    $stmt = $db->prepare("
        DELETE FROM todos
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $todoId,
        $userId
    );

    $stmt->execute();

    echo json_encode([
        'success' => true
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