<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\DependencyInjection;

use Esb\HealthCheckSymfony\Checks\RabbitMQ\RabbitMQCheck;
use Esb\HealthCheckSymfony\Controller\HealthCheckController;
use Esb\HealthCheckSymfony\Settings\KafkaSettings;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HealthCheckSymfonyExtension extends Extension
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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
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
        $loader->load('services.xml');

        $healthCheckController = $container->findDefinition(HealthCheckController::class);

        foreach ($config['checks'] as $healthCheckConfig) {
            $healthCheckDefinition = new Reference($healthCheckConfig['id']);
            $healthCheckController->addMethodCall('addCheck', [$healthCheckDefinition]);
        }

        if ($queues = $config['rabbitmq_queues'] ?? false) {
            foreach ($queues as $rabbitMqQueue) {
                $rabbitMqCheck = $container->findDefinition(RabbitMQCheck::class);
                $rabbitMqCheck->addMethodCall('addQueue', [$rabbitMqQueue['name']]);
            }
        }

        if ($kafkaConfig = $config['kafka'] ?? false) {
            $kafkaSettings = $container->findDefinition(KafkaSettings::class);
            $kafkaSettings->addMethodCall('setGroup', [$kafkaConfig['group']]);
            $kafkaSettings->addMethodCall('setBrokerList', [$kafkaConfig['broker_list']]);
            $kafkaSettings->addMethodCall('setSaslUsername', [$kafkaConfig['sasl_username']]);
            $kafkaSettings->addMethodCall('setSaslPassword', [$kafkaConfig['sasl_password']]);
            $kafkaSettings->addMethodCall('setSecurityProtocol', [$kafkaConfig['security_protocol']]);
            $kafkaSettings->addMethodCall('setSaslMechanism', [$kafkaConfig['sasl_mechanism']]);
            $kafkaSettings->addMethodCall('setEnv', [$kafkaConfig['env']]);
            foreach ($kafkaConfig['topics'] as $topic) {
                $kafkaSettings->addMethodCall('addTopic', [$topic['name']]);
            }
        }
    }
}
