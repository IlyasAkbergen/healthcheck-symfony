<?php

namespace Tests\Mock\RabbitMQ;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\ConnectionResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConnectionResolverMock extends ConnectionResolver
{
    public function resolve(ContainerInterface $container): ?\PhpAmqpLib\Connection\AbstractConnection
    {
        return new ConnectionMock();
    }
}
