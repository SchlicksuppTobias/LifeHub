<?php

namespace Tobias\LifeHub\shared\interfaces;

interface EnvProviderInterface
{
    public function get(string $key, mixed $default = null): mixed;
}