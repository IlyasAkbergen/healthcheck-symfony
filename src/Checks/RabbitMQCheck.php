<?php

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;

class RabbitMQCheck extends HealthCheck
{
    public function name(): string
    {
        return 'rabbitMQ';
    }

    public function handle(): Status
    {
        // TODO: Implement status() method.
    }
}
