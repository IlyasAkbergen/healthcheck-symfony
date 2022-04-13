<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks\RabbitMQ;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RabbitMQCheck extends HealthCheck
{
    const NAME = 'rabbitMQ';
    private array $queues = [];

    private ContainerInterface $container;
    private ConnectionResolver $connectionResolver;
    private ConsumerResolver $consumerResolver;

    public function __construct(
        ContainerInterface $container,
        ConnectionResolver $connectionResolver,
        ConsumerResolver $consumerResolver
    ) {
        $this->container = $container;
        $this->connectionResolver = $connectionResolver;
        $this->consumerResolver = $consumerResolver;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function handle(): Status
    {
        try {
            /** @var \PhpAmqpLib\Connection\AMQPStreamConnection\AMQPStreamConnection $connection */
            $connection = $this->connectionResolver->resolve($this->container);

            if (!$connection) {
                return $this->problem('RabbitMQ connection not found.');
            }

            $consumer = $this->consumerResolver->resolve($connection);

            if (empty($this->queues)) {
                return $this->problem('Queues not set.');
            }

            foreach ($this->queues as $queueName) {
                $consumer->setQueueOptions([ 'name' => $queueName ]);
                [ $queueName, $messageCount, $consumerCount ] = $consumer
                    ->getChannel()
                    ->queue_declare($queueName, true);

                $info[] = [
                    'queue' => $queueName,
                    'messages' => $messageCount,
                    'consumers' => $consumerCount,
                ];
            }
        } catch (\Throwable $exception) {
            return $this->problem('RabbitMQCheck failed');
        }

        return isset($info)
            ? $this->okay($info)
            : $this->problem('Undefined problem');
    }

    public function addQueue(string $queueName): void
    {
        $this->queues[] = $queueName;
    }
}
