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

function generateUuidV4(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

try {
    $db = new MariaDbConnection($host, $user, $pass, $dbname);

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $userId = $input['user_id'] ?? null;
    $title = trim($input['title'] ?? 'Einkaufsliste');

    if (empty($userId) || !ctype_digit((string)$userId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'user_id fehlt oder ist ungültig'
        ]);
        exit;
    }

    if ($title === '') {
        $title = 'Einkaufsliste';
    }

    $uniqueId = generateUuidV4();

    $sql = "INSERT INTO shopping_lists (unique_id, user_id, title) VALUES (?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('sis', $uniqueId, $userId, $title);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'unique_id' => $uniqueId,
        'title' => $title
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
