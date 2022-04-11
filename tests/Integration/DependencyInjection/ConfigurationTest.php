<?php

declare(strict_types=1);

namespace Tests\Integration\DependencyInjection;

use Esb\HealthCheckSymfony\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testProcessConfigurationWithDefaultConfiguration(array $givenConfig, array $expectedConfig): void
    {
        self::assertSame($expectedConfig, $this->processConfiguration($givenConfig));
    }

    private function processConfiguration(array $values): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), ['health_check_symfony' => $values]);
    }

    public function dataProvider(): array
    {
        return [
            [
                'givenConfig' => [
                    'checks' => [],
                ],
                'expectedConfig' => [
                    'checks' => [],
                    'rabbitmq_queues' => [],
                ],
            ],
            [
                'givenConfig' => [
                    'checks' => [],
                    'kafka' => [],
                ],
                'expectedConfig' => [
                    'checks' => [],
                    'kafka' => [
                        'topics' => [],
                    ],
                    'rabbitmq_queues' => [],
                ],
            ],
            [
                'givenConfig' => [
                    'checks' => [],
                    'rabbitmq_queues' => [],
                    'kafka' => [
                        'topics' => [],
                    ],
                ],
                'expectedConfig' => [
                    'checks' => [],
                    'rabbitmq_queues' => [],
                    'kafka' => [
                        'topics' => [],
                    ],
                ],
            ],
        ];
    }
}
