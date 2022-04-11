<?php

declare(strict_types=1);

namespace Tests\Unit\Checks;

use Esb\HealthCheck\Status;
use Esb\HealthCheckSymfony\Checks\DoctrineCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Mock\EntityManagerMock;

final class DoctrineCheckTest extends TestCase
{
    public function testReturnsDoctrineNotFoundProblem(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(null);

        $doctrine = new DoctrineCheck($container);

        $result = $doctrine->handle();

        self::assertTrue($result->isProblem());
        self::assertSame($result->message(), 'Entity Manager Not Found.');
    }

    public function testReturnsExpectedSuccessResult(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerMock::class);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn($entityManager);

        $doctrineCheck = new DoctrineCheck($container);

        $status = $doctrineCheck->handle();

        self::assertSame(DoctrineCheck::NAME, $status->name());
        self::assertSame(Status::OKAY, $status->getStatus());
    }
}
