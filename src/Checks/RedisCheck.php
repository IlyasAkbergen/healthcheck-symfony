<?php

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Predis\ClientInterface as Redis;

class RedisCheck extends HealthCheck
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function name(): string
    {
        return 'redis';
    }

    public function handle(): Status
    {
        $key = 'healthcheck';

        try {
            $this->redis->set($key, $key);

            if (!$this->redis->exists($key)) {
                return $this->problem('Could not set key');
            }

            $info = $this->redis->info();
        } catch (\Throwable $e) {
            return $this->problem('Redis error', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay([
            'clients' => $info['Clients'] ?? null,
            'stats' => $info['Stats'] ?? null,
            'memory' => $info['Memory'] ?? null,
        ]);
    }
}
