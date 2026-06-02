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

    $userId = 1; // später aus Session/JWT

    $stmt = $db->prepare("
        SELECT *
        FROM usersData
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $user
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