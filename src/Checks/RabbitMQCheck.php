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
        $consumers = []; // todo implement

        return $this->okay([
            'consumers' => $consumers,
        ]);
    }
}
