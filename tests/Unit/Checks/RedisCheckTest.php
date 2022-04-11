<?php

declare(strict_types=1);

namespace Tests\Unit\Checks;

use Esb\HealthCheckSymfony\Checks\RedisCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Mock\PredisClientMock;
use Predis\ClientInterface as PredisClientInterface;

class RedisCheckTest extends TestCase
{
    public function testReturnsPredisClientNotFoundProblem(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $redisCheck = new RedisCheck($container);

        $result = $redisCheck->handle();

        self::assertTrue($result->isProblem());
        self::assertSame($result->message(), 'Redis client not found.');
    }

    public function testReturnsExpectedResult(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $predisClient = $this->createPartialMock(PredisClientMock::class, []);

        $container->method('get')
            ->with(PredisClientInterface::class)
            ->willReturn($predisClient);

        $redisCheck = new RedisCheck($container);
        $redisCheck->setPredisClient($predisClient);
        $result = $redisCheck->handle();

        self::assertTrue($result->isOkay());
        self::assertArrayHasKey('clients', $result->context());
        self::assertArrayHasKey('stats', $result->context());
        self::assertArrayHasKey('memory', $result->context());
    }
}
