<?php

namespace Tobias\LifeHub\shared\exceptions;

use RuntimeException;
use Throwable;

class DatabaseException extends RuntimeException
{
    private array $context;

    public function __construct(
        string $message = 'Database error occurred.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}