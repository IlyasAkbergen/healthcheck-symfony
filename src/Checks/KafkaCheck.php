<?php

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;

class KafkaCheck extends HealthCheck
{

    public function handle(): Status
    {
        return $this->okay();// TODO: Implement handle() method.
    }
}
