<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumerResolver
{
    public function resolve(AMQPStreamConnection $connection): Consumer
    {
        return new Consumer($connection);
    }
}
