<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

$host = $envHandler->getEnv('DB_HOST');
$user = $envHandler->getEnv('DB_USERNAME');
$pass = $envHandler->getEnv('DB_PASSWORD');
$dbname = $envHandler->getEnv('DB_DATABASE');

try {
    $db = new MariaDbConnection($host, $user, $pass, $dbname);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $uniqueId = trim($input['unique_id'] ?? '');
    $name = trim($input['name'] ?? '');
    $amount = isset($input['amount']) && $input['amount'] !== '' ? trim($input['amount']) : null;
    $unit = isset($input['unit']) && $input['unit'] !== '' ? trim($input['unit']) : null;

    if ($uniqueId === '' || $name === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'unique_id oder name fehlt'
        ]);
        exit;
    }

    // Liste anhand der uniqueId auflösen
    $listStmt = $db->prepare("SELECT id FROM shopping_lists WHERE unique_id = ? LIMIT 1");
    $listStmt->bind_param('s', $uniqueId);
    $listStmt->execute();
    $list = $listStmt->get_result()->fetch_assoc();
    $listStmt->close();

    if (!$list) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Einkaufsliste nicht gefunden'
        ]);
        $db->close();
        exit;
    }

    $sortStmt = $db->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 AS next_order FROM shopping_list_items WHERE list_id = ?");
    $sortStmt->bind_param('i', $list['id']);
    $sortStmt->execute();
    $nextOrder = $sortStmt->get_result()->fetch_assoc()['next_order'];
    $sortStmt->close();

    $insertSql = "
        INSERT INTO shopping_list_items (list_id, name, amount, unit, sort_order)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $db->prepare($insertSql);
    $stmt->bind_param('isssi', $list['id'], $name, $amount, $unit, $nextOrder);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'item' => [
            'id' => $stmt->insert_id,
            'name' => $name,
            'amount' => $amount,
            'unit' => $unit,
            'checked' => false,
            'sort_order' => $nextOrder
        ]
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
