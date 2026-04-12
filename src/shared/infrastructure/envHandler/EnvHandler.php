<?php

namespace Tobias\LifeHub\shared\infrastructure\envHandler;

use Tobias\LifeHub\shared\interfaces\EnvHandlerInterface;
use Tobias\LifeHub\shared\interfaces\EnvProviderInterface;

final readonly class EnvHandler implements EnvHandlerInterface
{
    public function __construct(
        private EnvProviderInterface $provider
    ) {}

    public function getEnv(string $key, mixed $default = null): mixed
    {
        return $this->provider->get($key, $default);
    }
}