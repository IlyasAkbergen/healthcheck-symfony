<?php

namespace Tests\Mock\RabbitMQ;

use PhpAmqpLib\Connection\AbstractConnection;

class ConnectionMock extends AbstractConnection
{
    public function __construct()
    {
    }
}
