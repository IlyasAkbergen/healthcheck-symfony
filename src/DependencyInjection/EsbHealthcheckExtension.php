<?php

namespace Esb\HealthCheckSymfony\DependencyInjection;

use Esb\HealthCheck\HealthCheckService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class EsbHealthcheckExtension extends Extension
{
    /**
     * @param array<array> $configs
     *
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('controller.xml');

        $this->loadHealthChecks($config, $loader, $container);
    }

    /**
     * @param array<array> $config
     *
     * @throws \Exception
     */
    private function loadHealthChecks(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $loader->load('checks.xml');

        $healthCheckService = $container->findDefinition(HealthCheckService::class);

        foreach ($config['checks'] as $healthCheckConfig) {
            $healthCheckDefinition = new Reference($healthCheckConfig['id']);
            $healthCheckService->addMethodCall('addCheck', [$healthCheckDefinition]);
        }
    }
}
