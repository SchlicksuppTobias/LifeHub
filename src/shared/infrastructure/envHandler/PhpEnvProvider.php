<?php

namespace Tobias\LifeHub\shared\infrastructure\envHandler;

use Tobias\LifeHub\shared\interfaces\EnvProviderInterface;

class PhpEnvProvider implements EnvProviderInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key]
            ?? getenv($key)
            ?? $default;
    }
}
