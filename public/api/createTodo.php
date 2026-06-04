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

    $title = trim($payload['title'] ?? '');

    if ($title === '') {
        throw new RuntimeException('Title is required');
    }

    $db = new MariaDbConnection(
        $envHandler->getEnv('DB_HOST'),
        $envHandler->getEnv('DB_USERNAME'),
        $envHandler->getEnv('DB_PASSWORD'),
        $envHandler->getEnv('DB_DATABASE')
    );

    $userId = 1;

    $stmt = $db->prepare("
        INSERT INTO todos (
            user_id,
            title,
            completed
        )
        VALUES (
            ?,
            ?,
            0
        )
    ");

    $stmt->bind_param(
        "is",
        $userId,
        $title
    );

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'todoId' => $stmt->insert_id
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