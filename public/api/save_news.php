<?php declare(strict_types=1);

use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;
use Tobias\LifeHub\shared\exceptions\DatabaseException;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';

function jsonResponse(int $status, array $payload): never
{
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, [
        'message' => 'Methode nicht erlaubt.'
    ]);
}

$raw = file_get_contents('php://input');

$body = json_decode(
    $raw,
    true
);

if (!is_array($body)) {
    jsonResponse(400, [
        'message' => 'Ungültiges JSON.'
    ]);
}

$title = trim(
    (string)($body['title'] ?? '')
);

$content = trim(
    (string)($body['content'] ?? '')
);

/**
 * Validierung
 */
if ($title === '') {
    jsonResponse(422, [
        'message' => 'Titel ist erforderlich.'
    ]);
}

if ($content === '') {
    jsonResponse(422, [
        'message' => 'Inhalt ist erforderlich.'
    ]);
}

if (mb_strlen($title) > 255) {
    jsonResponse(422, [
        'message' => 'Titel darf maximal 255 Zeichen haben.'
    ]);
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
        "INSERT INTO news
        (
            title,
            content,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            NOW()
        )"
    );

    $stmt->bind_param(
        "ss",
        $title,
        $content
    );

    if (!$stmt->execute()) {
        throw new DatabaseException(
            'News konnte nicht gespeichert werden.'
        );
    }

    $newsId = $stmt->insert_id;

    $stmt->close();

    jsonResponse(201, [
        'message' => 'News erfolgreich erstellt.',
        'news_id' => $newsId
    ]);

} catch (DatabaseException $e) {

    error_log(
        $e->getMessage()
    );

    jsonResponse(500, [
        'message' => 'Datenbankfehler.'
    ]);

} catch (Throwable $e) {

    error_log(
        $e->getMessage()
    );

    jsonResponse(500, [
        'message' => 'Interner Fehler.'
    ]);
}
