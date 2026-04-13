<?php

namespace Tobias\LifeHub\shared\infrastructure\mariaDbConnection;

use mysqli;
use Tobias\LifeHub\shared\exceptions\DatabaseException;
use Tobias\LifeHub\shared\interfaces\ConnectionInterface;

class MariaDbConnection implements ConnectionInterface
{
    private mysqli $connection;

    public function __construct(
        string $host,
        string $user,
        string $pass,
        string $dbname
    ) {
        $this->connection = new mysqli($host, $user, $pass, $dbname);

        if ($this->connection->connect_error) {
            throw new DatabaseException(
                'Database connection failed: ' . $this->connection->connect_error
            );
        }
    }

    public function query(string $sql): \mysqli_result
    {
        $result = $this->connection->query($sql);

        if (!$result) {
            throw new DatabaseException(
                'Query failed: ' . $this->connection->error
            );
        }

        return $result;
    }

    public function prepare(string $sql): \mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new DatabaseException(
                'Prepare failed: ' . $this->connection->error
            );
        }

        return $stmt;
    }

    public function close(): void
    {
        $this->connection->close();
    }

}