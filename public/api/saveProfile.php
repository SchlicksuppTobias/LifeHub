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
    $db = new MariaDbConnection(
        $envHandler->getEnv('DB_HOST'),
        $envHandler->getEnv('DB_USERNAME'),
        $envHandler->getEnv('DB_PASSWORD'),
        $envHandler->getEnv('DB_DATABASE')
    );

    $data = json_decode(file_get_contents("php://input"), true);

    $userId = 1; // später Session

    $sql = "
        UPDATE usersData SET
            first_name = ?,
            last_name = ?,
            email = ?,
            phone = ?,
            street = ?,
            house_number = ?,
            postal_code = ?,
            city = ?,
            state = ?,
            country = ?,
            birth_date = ?,
            weight = ?,
            weightGoal = ?,
            gender = ?,
            height = ?,
            bio = ?,
            avatar = ?,
            updated_at = NOW()
        WHERE id = ?
    ";

    $stmt = $db->prepare($sql);

    $stmt->bind_param(
        "sssssssssssiissi",
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['street'],
        $data['house_number'],
        $data['postal_code'],
        $data['city'],
        $data['state'],
        $data['country'],
        $data['birth_date'],
        $data['weight'],
        $data['height'],
        $data['bio'],
        $data['avatar'],
        $userId
    );

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
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