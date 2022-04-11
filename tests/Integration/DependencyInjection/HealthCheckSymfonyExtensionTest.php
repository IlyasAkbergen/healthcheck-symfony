<?php

declare(strict_types=1);

namespace Tests\Integration\DependencyInjection;

use Esb\HealthCheckSymfony\Checks\DoctrineCheck;
use Esb\HealthCheckSymfony\Checks\KafkaCheck;
use Esb\HealthCheckSymfony\Checks\RabbitMQCheck;
use Esb\HealthCheckSymfony\Checks\RedisCheck;
use Esb\HealthCheckSymfony\Controller\HealthCheckController;
use Esb\HealthCheckSymfony\DependencyInjection\HealthCheckSymfonyExtension;
use Esb\HealthCheckSymfony\Settings\KafkaSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class HealthCheckSymfonyExtensionTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testWithFullConfig(): void
    {

        $container = $this->createContainerFromFixture('filled_bundle_config');

        self::assertCount(7, $container->getDefinitions());
        self::assertArrayHasKey(HealthCheckController::class, $container->getDefinitions());
        self::assertArrayHasKey(DoctrineCheck::class, $container->getDefinitions());
        self::assertArrayHasKey(KafkaCheck::class, $container->getDefinitions());
        self::assertArrayHasKey(KafkaSettings::class, $container->getDefinitions());
        self::assertArrayHasKey(RabbitMQCheck::class, $container->getDefinitions());
        self::assertArrayHasKey(RedisCheck::class, $container->getDefinitions());
    }

    /**
     * @throws \Exception
     */
    private function createContainerFromFixture(string $fixtureFile): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->registerExtension(new HealthCheckSymfonyExtension());
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        $this->loadFixture($container, $fixtureFile);

        $container->compile();

        return $container;
    }

    /**
     * @throws \Exception
     */
    protected function loadFixture(ContainerBuilder $container, string $fixtureFile): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load($fixtureFile . '.yaml');
    }
}
