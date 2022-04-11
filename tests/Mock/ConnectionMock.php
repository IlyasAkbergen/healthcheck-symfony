<?php

declare(strict_types=1);

namespace Tests\Mock;

class ConnectionMock
{
    public function getDatabasePlatform(): AbstractPlatformMock
    {
        return new AbstractPlatformMock();
    }

    public function executeQuery(): ExecuteQueryMock
    {
        return new ExecuteQueryMock();
    }
}
