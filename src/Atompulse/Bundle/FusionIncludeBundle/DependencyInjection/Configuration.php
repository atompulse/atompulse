<?php

namespace Atompulse\Bundle\FusionIncludeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Atompulse\Bundle\FusionIncludeBundle\DependencyInjection
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fusion_include');

        $this->addNamespacesConfig($rootNode);
        $this->addImportsConfig($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function addNamespacesConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('namespaces')
                    ->requiresAtLeastOneElement()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('source')
                                ->isRequired()->cannotBeEmpty()
                            ->end()
                            ->scalarNode('target')
                                ->isRequired()->cannotBeEmpty()
                            ->end()
                        ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    protected function addImportsConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('imports')
                    ->prototype('scalar')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->example('%kernel.project_dir%/src/App/config/includes.yml')
                ->end()
            ->end();
    }
}
