<?php

declare(strict_types=1);

namespace Tests\Mock;

class PredisClientMock
{
    public function set(string $key, $value): void
    {
    }

    public function exists(string $key): bool
    {
        return true;
    }

    public function info(): array
    {
        return [
            'Clients' => [],
            'Stats' => [],
            'Memory' => [],
        ];
    }
}
