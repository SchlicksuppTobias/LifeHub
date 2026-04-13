<?php

namespace Tobias\LifeHub\shared\interfaces;

use mysqli_result;
use mysqli_stmt;

interface ConnectionInterface
{
    public function query(string $sql): mysqli_result;

    public function prepare(string $sql): mysqli_stmt;

    public function close(): void;
}