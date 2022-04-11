<?php

namespace Tests\Mock\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use PhpAmqpLib\Connection\AbstractConnection;

class ConsumerMock extends Consumer
{
    public function __construct(AbstractConnection $connection)
    {
    }

    public function getChanel(): AMQPChannelMock
    {
        return new AMQPChannelMock();
    }
}
