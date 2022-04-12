<?php

namespace Tests\Mock\RabbitMQ;

class ConsumerMock extends \OldSound\RabbitMqBundle\RabbitMq\Consumer
{
    public function __construct(\PhpAmqpLib\Connection\AbstractConnection $connection)
    {
    }

    public function getChanel(): AMQPChannelMock
    {
        return new AMQPChannelMock();
    }
}
