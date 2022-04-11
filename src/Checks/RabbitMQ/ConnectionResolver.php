<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks\RabbitMQ;

use PhpAmqpLib\Connection\AbstractConnection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConnectionResolver
{
    public function resolve(ContainerInterface $container): ?AbstractConnection
    {
        return $container->get('old_sound_rabbit_mq.connection.default') ?? null;
    }
}
