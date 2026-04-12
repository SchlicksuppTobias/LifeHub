<?php

namespace Tobias\LifeHub\shared\interfaces;

interface EnvHandlerInterface
{
    public function getEnv(string $key, mixed $default = null): mixed;
}