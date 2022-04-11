<?php

namespace Tests\Mock\RabbitMQ;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\ConnectionResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpAmqpLib\Connection\AbstractConnection;

class ConnectionResolverMock extends ConnectionResolver
{
    public function resolve(ContainerInterface $container): ?AbstractConnection
    {
        return new ConnectionMock();
    }
}
