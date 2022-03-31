<?php

namespace Esb\HealthCheckSymfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('healthcheck-symfony');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('healthcheck-symfony');

        $rootNode
            ->children()
                ->arrayNode('checks')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
