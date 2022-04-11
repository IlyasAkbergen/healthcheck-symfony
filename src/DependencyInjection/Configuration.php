<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('health_check_symfony');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('health_check_symfony');

        $rootNode
            ->children()
                ->arrayNode('checks')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('rabbitmq_queues')
                    ->defaultValue([])
                    ->prototype('array')
                    ->canBeEnabled()
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('kafka')
                    ->children()
                        ->scalarNode('group')->end()
                        ->scalarNode('broker_list')->end()
                        ->scalarNode('sasl_username')->end()
                        ->scalarNode('sasl_password')->end()
                        ->scalarNode('security_protocol')->end()
                        ->scalarNode('sasl_mechanism')->end()
                        ->scalarNode('env')->end()
                        ->arrayNode('topics')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
