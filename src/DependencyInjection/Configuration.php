<?php

namespace CreamIO\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('user_bundle');

        $rootNode
            ->children()
            ->scalarNode('test1')->end()
            ->scalarNode('test2')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}