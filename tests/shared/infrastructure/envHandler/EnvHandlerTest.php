<?php

namespace shared\infrastructure\envHandler;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tobias\LifeHub\shared\infrastructure\envHandler\EnvHandler;
use Tobias\LifeHub\shared\interfaces\EnvHandlerInterface;
use Tobias\LifeHub\shared\interfaces\EnvProviderInterface;

final class EnvHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testImplementsInterface(): void
    {
        $provider = $this->createStub(EnvProviderInterface::class);

        $handler = new EnvHandler($provider);

        $this->assertInstanceOf(EnvHandlerInterface::class, $handler);
    }

    /**
     * @throws Exception
     */
    public function testGetEnvReturnsValueFromProvider(): void
    {
        $provider = $this->createStub(EnvProviderInterface::class);

        $provider->method('get')
            ->willReturn('localhost');

        $handler = new EnvHandler($provider);

        $this->assertSame('localhost', $handler->getEnv('DB_HOST'));
    }

    /**
     * @throws Exception
     */
    public function testGetEnvReturnsDefaultWhenProviderReturnsNull(): void
    {
        $provider = $this->createStub(EnvProviderInterface::class);

        $provider->method('get')
            ->willReturn('default-value');

        $handler = new EnvHandler($provider);

        $this->assertSame(
            'default-value',
            $handler->getEnv('UNKNOWN_KEY', 'default-value')
        );
    }

    /**
     * @throws Exception
     */
    public function testGetEnvPassesCorrectArguments(): void
    {
        $provider = $this->createMock(EnvProviderInterface::class);

        $provider->expects($this->once())
            ->method('get')
            ->with('APP_ENV', 'prod')
            ->willReturn('dev');

        $handler = new EnvHandler($provider);

        $handler->getEnv('APP_ENV', 'prod');
    }
}