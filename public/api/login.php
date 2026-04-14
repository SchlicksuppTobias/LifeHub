<?php
declare(strict_types=1);

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;
use Tobias\LifeHub\shared\exceptions\DatabaseException;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';

function jsonResponse(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['error' => 'Methode nicht erlaubt']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    jsonResponse(400, ['error' => 'Ungültiges JSON']);
}

$email    = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    jsonResponse(422, ['error' => 'E-Mail und Passwort erforderlich']);
}

try {

    $factory = new AppFactory();
    $env = $factory->getEnvHandler();

    $connection = new MariaDbConnection(
        $env->getEnv('DB_HOST'),
        $env->getEnv('DB_USERNAME'),
        $env->getEnv('DB_PASSWORD'),
        $env->getEnv('DB_DATABASE')
    );

    $stmt = $connection->prepare(
        "SELECT id, email, password_hash, is_active
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        jsonResponse(401, ['error' => 'E-Mail oder Passwort ist falsch']);
    }

    if ((int)$user['is_active'] !== 1) {
        jsonResponse(403, ['error' => 'Account deaktiviert']);
    }

    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(401, ['error' => 'E-Mail oder Passwort ist falsch']);
    }

    $token = bin2hex(random_bytes(32));

    jsonResponse(200, [
        'ok'      => true,
        'token'   => $token,
        'user_id' => (int)$user['id']
    ]);

} catch (DatabaseException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['error' => 'Datenbankfehler']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['error' => 'Interner Fehler']);
}