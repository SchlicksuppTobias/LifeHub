<?php

namespace Tobias\LifeHub\shared\infrastructure\mariaDbConnection;

use Tobias\LifeHub\shared\exceptions\DatabaseException;
use Tobias\LifeHub\shared\interfaces\ConnectionInterface;
use Tobias\LifeHub\shared\interfaces\NutritionRepositoryInterface;

class NutritionRawRepository implements NutritionRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $connection
    ) {}

    public function getAll(): array
    {
        $result = $this->connection->query(
            "SELECT * FROM nutrition_raw ORDER BY name_de ASC LIMIT 100"
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function search(string $term): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM nutrition_raw 
             WHERE name_de LIKE ? 
             ORDER BY name_de ASC 
             LIMIT 100"
        );

        $like = '%' . $term . '%';
        $stmt->bind_param('s', $like);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new DatabaseException('Search query failed.');
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

}