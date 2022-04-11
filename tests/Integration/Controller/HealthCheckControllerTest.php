<?php

declare(strict_types=1);

namespace Tests\Integration\Controller;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Esb\HealthCheckSymfony\Checks\DoctrineCheck;
use Esb\HealthCheckSymfony\Checks\KafkaCheck;
use Esb\HealthCheckSymfony\Checks\RabbitMQ\RabbitMQCheck;
use Esb\HealthCheckSymfony\Checks\RedisCheck;
use Esb\HealthCheckSymfony\Controller\HealthCheckController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/healthcheck');

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode(['status' => Status::OKAY]), $response->getContent());
    }

    /**
     * @dataProvider addCheckSuccessProvider
    */
    public function testAddCheckSuccess(HealthCheck $healthCheck, array $expectedResponse): void
    {
        $healthCheckController = new HealthCheckController();
        $healthCheckController->addCheck($healthCheck);

        $response = $healthCheckController->index();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode($expectedResponse),
            $response->getContent()
        );
    }

    public function addCheckSuccessProvider(): array
    {
        $statusOK = (new Status())->okay();

        $doctrineCheck = $this->createPartialMock(DoctrineCheck::class, [ 'handle' ]);
        $doctrineCheck
            ->method('handle')
            ->willReturn((clone $statusOK)->withName(DoctrineCheck::NAME));

        $kafkaCheck = $this->createPartialMock(KafkaCheck::class, [ 'handle' ]);
        $kafkaCheck
            ->method('handle')
            ->willReturn((clone $statusOK)->withName(KafkaCheck::NAME));

        $rabbitmqCheck = $this->createPartialMock(RabbitMQCheck::class, [ 'handle' ]);
        $rabbitmqCheck
            ->method('handle')
            ->willReturn((clone $statusOK)->withName(RabbitMQCheck::NAME));

        $redisCheck = $this->createPartialMock(RedisCheck::class, [ 'handle' ]);
        $redisCheck
            ->method('handle')
            ->willReturn((clone $statusOK)->withName(RedisCheck::NAME));

        return [
            [
                'healthCheck' => $doctrineCheck,
                'expectedResponse' => [
                    'status' => Status::OKAY,
                    $doctrineCheck->name() => [
                        'status' => Status::OKAY,
                        'message' => null,
                        'context' => null,
                    ],
                ],
            ],
            [
                'healthCheck' => $kafkaCheck,
                'expectedResponse' => [
                    'status' => Status::OKAY,
                    $kafkaCheck->name() => [
                        'status' => Status::OKAY,
                        'message' => null,
                        'context' => null,
                    ],
                ],
            ],
            [
                'healthCheck' => $rabbitmqCheck,
                'expectedResponse' => [
                    'status' => Status::OKAY,
                    $rabbitmqCheck->name() => [
                        'status' => Status::OKAY,
                        'message' => null,
                        'context' => null,
                    ],
                ],
            ],
            [
                'healthCheck' => $redisCheck,
                'expectedResponse' => [
                    'status' => Status::OKAY,
                    $redisCheck->name() => [
                        'status' => Status::OKAY,
                        'message' => null,
                        'context' => null,
                    ],
                ],
            ],
        ];
    }

    public function testAddCheckFailed(): void
    {
        self::expectException(\TypeError::class);

        $healthCheckController = new HealthCheckController();
        $healthCheckController->addCheck(new HealthCheckController());
    }
}
