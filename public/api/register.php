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
    jsonResponse(405, ['message' => 'Methode nicht erlaubt.']);
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    jsonResponse(400, ['message' => 'Ungültiges JSON.']);
}

$email    = trim((string)($body['email'] ?? ''));
$passwort = (string)($body['passwort'] ?? '');

/**
 * Validierung
 */
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(422, ['message' => 'Gültige E-Mail-Adresse erforderlich.']);
}

if (strlen($passwort) < 8) {
    jsonResponse(422, ['message' => 'Passwort muss mindestens 8 Zeichen haben.']);
}

$hashAlgo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$passwordHash = password_hash($passwort, $hashAlgo);

if ($passwordHash === false) {
    jsonResponse(500, ['message' => 'Hashing fehlgeschlagen.']);
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
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        jsonResponse(409, ['message' => 'Diese E-Mail-Adresse ist bereits registriert.']);
    }

    $stmt->close();

    $stmt = $connection->prepare(
        "INSERT INTO users (email, password_hash) VALUES (?, ?)"
    );

    $stmt->bind_param("ss", $email, $passwordHash);

    if (!$stmt->execute()) {
        throw new DatabaseException('Insert fehlgeschlagen.');
    }

    $newUserId = $stmt->insert_id;
    $stmt->close();

    $token = bin2hex(random_bytes(32));

    jsonResponse(201, [
        'message' => 'Registrierung erfolgreich.',
        'token'   => $token,
        'user_id' => $newUserId,
    ]);

} catch (DatabaseException $e) {

    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Datenbankfehler.']);

} catch (Throwable $e) {

    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Interner Fehler.']);
}