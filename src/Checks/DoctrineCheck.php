<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineCheck extends HealthCheck
{
    private ContainerInterface $container;
    private LoggerInterface $logger;

    const NAME = 'doctrine';

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function handle(): Status
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager') ?? null;

        if ($entityManager === null) {
            return $this->problem('Entity Manager Not Found.');
        }

        try {
            /** * @var \Doctrine\DBAL\Connection */
            $connection = $entityManager->getConnection();
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL())->free();
        } catch (\Throwable $e) {
            $this->logger->log(
                'error',
                $e->getMessage(),
                $this->exceptionContext($e)
            );
            return $this->problem('Could not execute query');
        }

        return $this->okay();
    }
}
