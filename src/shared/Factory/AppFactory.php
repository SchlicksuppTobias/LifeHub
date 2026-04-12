<?php

namespace Tobias\LifeHub\shared\Factory;

use Tobias\LifeHub\shared\infrastructure\envHandler\EnvHandler;
use Tobias\LifeHub\shared\infrastructure\envHandler\PhpEnvProvider;
use Tobias\LifeHub\shared\interfaces\EnvHandlerInterface;

class AppFactory
{
    public function getEnvHandler(): EnvHandlerInterface
    {
        $envProvider = new PhpEnvProvider();
        return new EnvHandler($envProvider);


    }
}