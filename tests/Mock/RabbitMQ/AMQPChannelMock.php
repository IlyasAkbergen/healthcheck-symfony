<?php

namespace Tests\Mock\RabbitMQ;

class AMQPChannelMock
{
    public function queue_declare(string $queueName, bool $passive): array
    {
        return [$queueName, 1, 1];
    }
}
