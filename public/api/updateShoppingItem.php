<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PATCH');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

$host = $envHandler->getEnv('DB_HOST');
$user = $envHandler->getEnv('DB_USERNAME');
$pass = $envHandler->getEnv('DB_PASSWORD');
$dbname = $envHandler->getEnv('DB_DATABASE');

try {
    $db = new MariaDbConnection($host, $user, $pass, $dbname);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $itemId = $input['id'] ?? null;
    $uniqueId = trim($input['unique_id'] ?? '');

    if (empty($itemId) || !ctype_digit((string)$itemId) || $uniqueId === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'id oder unique_id fehlt/ungültig'
        ]);
        exit;
    }

    // Sicherstellen, dass das Item wirklich zu dieser Liste gehört
    $checkSql = "
        SELECT sli.id
        FROM shopping_list_items sli
        INNER JOIN shopping_lists sl ON sl.id = sli.list_id
        WHERE sli.id = ? AND sl.unique_id = ?
        LIMIT 1
    ";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->bind_param('is', $itemId, $uniqueId);
    $checkStmt->execute();
    $owns = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$owns) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Eintrag nicht gefunden'
        ]);
        $db->close();
        exit;
    }

    // Dynamisch nur die übergebenen Felder updaten
    $fields = [];
    $params = [];
    $types = '';

    if (array_key_exists('checked', $input)) {
        $fields[] = 'checked = ?';
        $params[] = (int)(bool)$input['checked'];
        $types .= 'i';
    }
    if (array_key_exists('name', $input)) {
        $fields[] = 'name = ?';
        $params[] = trim($input['name']);
        $types .= 's';
    }
    if (array_key_exists('amount', $input)) {
        $fields[] = 'amount = ?';
        $params[] = $input['amount'] !== '' ? trim($input['amount']) : null;
        $types .= 's';
    }
    if (array_key_exists('unit', $input)) {
        $fields[] = 'unit = ?';
        $params[] = $input['unit'] !== '' ? trim($input['unit']) : null;
        $types .= 's';
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Keine Felder zum Aktualisieren übergeben'
        ]);
        $db->close();
        exit;
    }

    $params[] = $itemId;
    $types .= 'i';

    $sql = "UPDATE shopping_list_items SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo json_encode(['success' => true]);

    $stmt->close();
    $db->close();

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
