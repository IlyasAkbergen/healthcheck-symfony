<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Checks;

use Doctrine\ORM\EntityManager;
use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineCheck extends HealthCheck
{
    private ContainerInterface $container;

    const NAME = 'doctrine';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function handle(): Status
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager') ?? null;

        if ($entityManager === null) {
            return $this->problem('Entity Manager Not Found.');
        }

        try {
            /** * @var \Doctrine\DBAL\Connection */
            $connection = $entityManager->getConnection();
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL())->free();
        } catch (\Throwable $e) {
            return $this->problem('Could not execute query', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }
}
