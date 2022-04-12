<?php

namespace Tests\Mock\RabbitMQ;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\ConsumerResolver;

class ConsumerResolverMock extends ConsumerResolver
{
    public function resolve(\PhpAmqpLib\Connection\AbstractConnection $connection): \OldSound\RabbitMqBundle\RabbitMq\Consumer
    {
        return new ConsumerMock($connection);
    }
}
