<?php

namespace Tests\Mock\RabbitMQ;

class ConnectionMock extends \PhpAmqpLib\Connection\AbstractConnection
{
    public function __construct()
    {
    }
}
