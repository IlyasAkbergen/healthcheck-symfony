<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks\RabbitMQ;

class ConsumerResolver
{
    public function resolve(\PhpAmqpLib\Connection\AMQPStreamConnection $connection): \OldSound\RabbitMqBundle\RabbitMq\Consumer
    {
        return new \OldSound\RabbitMqBundle\RabbitMq\Consumer($connection);
    }
}
