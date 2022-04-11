<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use OldSound\RabbitMqBundle\RabbitMq\AMQPConnectionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;

class RabbitMQCheck extends HealthCheck
{
    const NAME = 'rabbitMQ';
    private array $queues = [];

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function handle(): Status
    {
        try {
            /** @var AMQPStreamConnection $connection */
            $connection = $this->container->get('old_sound_rabbit_mq.connection.default');
            $consumer = new Consumer($connection);
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
            return $this->problem('RabbitMQCheck failed', $this->exceptionContext($exception));
        }

        return $this->okay($info);
    }

    public function addQueue(string $queueName): void
    {
        $this->queues[] = $queueName;
    }
}
