<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisCheck extends HealthCheck
{
    private ContainerInterface $container;
    private $redis;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function setPredisClient($redis = null)
    {
        $this->redis = $redis;
    }

    public function name(): string
    {
        return 'redis';
    }

    public function handle(): Status
    {
        if (empty($this->redis)) {
            return $this->problem('Redis client not found.');
        }

        try {
            $key = 'healthcheck';
            $this->redis->set($key, $key);

            if (!$this->redis->exists($key)) {
                return $this->problem('Could not set key');
            }

            $info = $this->redis->info();
        } catch (\Throwable $e) {
            return $this->problem('Redis error');
        }

        return $this->okay([
            'clients' => $info['Clients'] ?? null,
            'stats' => $info['Stats'] ?? null,
            'memory' => $info['Memory'] ?? null,
        ]);
    }
}
