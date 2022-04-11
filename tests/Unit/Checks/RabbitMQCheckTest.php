<?php

namespace Tests\Unit\Checks;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\RabbitMQCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Mock\RabbitMQ\AMQPChannelMock;
use Tests\Mock\RabbitMQ\ConnectionResolverMock;
use Tests\Mock\RabbitMQ\ConsumerMock;
use Tests\Mock\RabbitMQ\ConsumerResolverMock;

class RabbitMQCheckTest extends TestCase
{
    public function testReturnsConnectionNotFoundProblem(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
                  ->with('old_sound_rabbit_mq.connection.default')
                  ->willReturn(null);

        $connectionResolver = $this->createPartialMock(ConnectionResolverMock::class, [ 'resolve' ]);
        $connectionResolver->method('resolve')
            ->willReturn(null);
        $consumerResolver = $this->createPartialMock(ConsumerResolverMock::class, []);

        $rabbitCheck = new RabbitMQCheck(
            $container,
            $connectionResolver,
            $consumerResolver
        );

        $result = $rabbitCheck->handle();

        self::assertTrue($result->isProblem());
        self::assertSame('RabbitMQ connection not found.', $result->message());
    }

    public function testReturnsQueuesNotSetProblem(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
                  ->with('old_sound_rabbit_mq.connection.default')
                  ->willReturn(null);

        $connectionResolver = $this->createPartialMock(ConnectionResolverMock::class, []);
        $consumerResolver = $this->createPartialMock(ConsumerResolverMock::class, []);

        $rabbitCheck = new RabbitMQCheck(
            $container,
            $connectionResolver,
            $consumerResolver
        );

        $result = $rabbitCheck->handle();

        self::assertTrue($result->isProblem());
        self::assertSame('Queues not set.', $result->message());
    }

    /**
     * @dataProvider returnsExpectedSuccessResultProvider
    */
    public function testReturnsExpectedSuccessResult(string $queueName, int $messageCount, int $consumerCount): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
                  ->with('old_sound_rabbit_mq.connection.default')
                  ->willReturn(null);

        $connectionResolver = $this->createPartialMock(ConnectionResolverMock::class, []);

        $channelMock = $this->createMock(AMQPChannelMock::class);
        $channelMock->method('queue_declare')
            ->with($queueName)
            ->willReturn([$queueName, $messageCount, $consumerCount]);

        $consumerMock = $this->createPartialMock(ConsumerMock::class, [ 'getChannel' ]);
        $consumerMock->method('getChannel')
            ->willReturn($channelMock);

        $consumerResolver = $this->createMock(ConsumerResolverMock::class);
        $consumerResolver->method('resolve')
            ->willReturn($consumerMock);

        $rabbitCheck = new RabbitMQCheck(
            $container,
            $connectionResolver,
            $consumerResolver
        );
        $rabbitCheck->addQueue($queueName);

        $result = $rabbitCheck->handle();

        self::assertTrue($result->isOkay());
        self::assertSame(
            [
                [
                    'queue' => $queueName,
                    'messages' => $messageCount,
                    'consumers' => $consumerCount,
                ]
            ],
            $result->context()
        );
    }

    public function returnsExpectedSuccessResultProvider(): array
    {
        return [
            [
                'queueName' => 'test',
                'messageCount' => 11,
                'consumerCount' => 22
            ],
            [
                'queueName' => '',
                'messageCount' => 0,
                'consumerCount' => 0
            ],
        ];
    }
}
