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

    // POST-Daten holen
    $userId = $_POST['user_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $imagePath = $_POST['image_path'] ?? null;
    $ingredients = $_POST['ingredients'] ?? null;
    $instructions = $_POST['instructions'] ?? null;

    $prepTimeValue = $_POST['prep_time_value'] ?? null;
    $prepTimeUnit = $_POST['prep_time_unit'] ?? null;

    $cookTimeValue = $_POST['cook_time_value'] ?? null;
    $cookTimeUnit = $_POST['cook_time_unit'] ?? null;

    $restTimeValue = $_POST['rest_time_value'] ?? null;
    $restTimeUnit = $_POST['rest_time_unit'] ?? null;

    // Validierung (minimal)
    if (!$userId || !$title || !$ingredients || !$instructions) {
        throw new Exception("Missing required fields");
    }

    $sql = "
        INSERT INTO recipes_unverified
        (user_id, title, image_path, ingredients, instructions,
         prep_time_value, prep_time_unit,
         cook_time_value, cook_time_unit,
         rest_time_value, rest_time_unit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $db->prepare($sql);

    $stmt->bind_param(
        "issssisisis",
        $userId,
        $title,
        $imagePath,
        $ingredients,
        $instructions,
        $prepTimeValue,
        $prepTimeUnit,
        $cookTimeValue,
        $cookTimeUnit,
        $restTimeValue,
        $restTimeUnit
    );

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Recipe saved successfully',
        'recipe_id' => $stmt->insert_id
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