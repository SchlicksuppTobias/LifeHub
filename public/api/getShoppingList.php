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

    $uniqueId = $_GET['uniqueId'] ?? '';
    $uniqueId = trim($uniqueId);

    if ($uniqueId === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'uniqueId fehlt'
        ]);
        exit;
    }

    $listSql = "SELECT id, unique_id, title, created_at FROM shopping_lists WHERE unique_id = ? LIMIT 1";
    $stmt = $db->prepare($listSql);
    $stmt->bind_param('s', $uniqueId);
    $stmt->execute();
    $list = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$list) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Einkaufsliste nicht gefunden'
        ]);
        $db->close();
        exit;
    }

    $itemsSql = "
        SELECT id, name, amount, unit, checked, sort_order
        FROM shopping_list_items
        WHERE list_id = ?
        ORDER BY checked ASC, sort_order ASC, created_at ASC
    ";
    $itemsStmt = $db->prepare($itemsSql);
    $itemsStmt->bind_param('i', $list['id']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $row['checked'] = (bool)$row['checked'];
        $items[] = $row;
    }

    echo json_encode([
        'success' => true,
        'unique_id' => $list['unique_id'],
        'title' => $list['title'],
        'created_at' => $list['created_at'],
        'items' => $items
    ]);

    $itemsStmt->close();
    $db->close();

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
