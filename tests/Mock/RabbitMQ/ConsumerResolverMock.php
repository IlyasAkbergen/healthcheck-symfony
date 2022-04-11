<?php

namespace Tests\Mock\RabbitMQ;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\ConsumerResolver;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Connection\AbstractConnection;

class ConsumerResolverMock extends ConsumerResolver
{
    public function resolve(AbstractConnection $connection): Consumer
    {
        return new ConsumerMock($connection);
    }
}
