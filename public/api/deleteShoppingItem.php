<?php

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');

$factory = new AppFactory();
$envHandler = $factory->getEnvHandler();

$host = $envHandler->getEnv('DB_HOST');
$user = $envHandler->getEnv('DB_USERNAME');
$pass = $envHandler->getEnv('DB_PASSWORD');
$dbname = $envHandler->getEnv('DB_DATABASE');

try {
    $db = new MariaDbConnection($host, $user, $pass, $dbname);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $itemId = $input['id'] ?? ($_GET['id'] ?? null);
    $uniqueId = trim($input['unique_id'] ?? ($_GET['uniqueId'] ?? ''));

    if (empty($itemId) || !ctype_digit((string)$itemId) || $uniqueId === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'id oder unique_id fehlt/ungültig'
        ]);
        exit;
    }

    $sql = "
        DELETE sli FROM shopping_list_items sli
        INNER JOIN shopping_lists sl ON sl.id = sli.list_id
        WHERE sli.id = ? AND sl.unique_id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('is', $itemId, $uniqueId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Eintrag nicht gefunden'
        ]);
        $stmt->close();
        $db->close();
        exit;
    }

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
