<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Psr\Log\LoggerInterface;

class RedisCheck extends HealthCheck
{
    public const NAME = 'redis';

    private LoggerInterface $logger;
    private $redis;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setPredisClient($redis = null)
    {
        $this->redis = $redis;
    }

    public function name(): string
    {
        return self::NAME;
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
            $this->logger->log(
                'error',
                $e->getMessage(),
                $this->exceptionContext($e)
            );

            return $this->problem('Redis error');
        }

        return $this->okay([
            'clients' => $info['Clients'] ?? null,
            'stats' => $info['Stats'] ?? null,
            'memory' => $info['Memory'] ?? null,
        ]);
    }
}
