<?php

use Tobias\LifeHub\components\nutriSearch\NutritionController;
use Tobias\LifeHub\shared\exceptions\DatabaseException;
use Tobias\LifeHub\shared\Factory\AppFactory;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\MariaDbConnection;
use Tobias\LifeHub\shared\infrastructure\mariaDbConnection\NutritionRepository;

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
    $connection = new MariaDbConnection($host, $user, $pass, $dbname);

    $repo = new NutritionRepository($connection);
    $controller = new NutritionController($repo);

    $search = $_GET['search'] ?? '';

    $data = $controller->handle($search);
    $slim = array_map(fn($item) => [
        'id'   => $item['bls_code'],
        'name' => $item['name_de'],
    ], $data);

    echo json_encode($slim);

    //echo json_encode($data);
} catch (DatabaseException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}