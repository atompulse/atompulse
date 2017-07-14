<?php

namespace Atompulse\FusionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fusion');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                // includes configuration
                ->arrayNode('includes')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->example('true')
                        ->end()
                        ->scalarNode('parameter')
                            ->cannotBeEmpty()->isRequired()
                            ->example('fusion_includes')
                        ->end()
//                        ->scalarNode('source')
//                            ->cannotBeEmpty()->isRequired()
//                            ->example('"%kernel.root_dir%/../src/bundles/YourBundle/Resources/config/fusion/includes.yml"')
//                        ->end()
                        ->arrayNode('imports')
                            ->cannotBeEmpty()
                            ->prototype('scalar')
                                ->isRequired()
                            ->end()
                            ->example(' - "%kernel.root_dir%/../src/bundles/YourBundle/Resources/config/fusion/imports"')
                        ->end()
                        ->arrayNode('paths')
                            ->prototype('array')
                                ->isRequired(false)
                                ->cannotBeEmpty()
                                ->children()
                                    ->scalarNode('path')
                                        ->isRequired(true)->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('web')
                                        ->isRequired(true)->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('compiler')
                            ->cannotBeEmpty()->isRequired(true)
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultValue(false)
                                    ->example('true')
                                ->end()
                                ->arrayNode('settings')
                                    //->cannotBeEmpty()->isRequired(true)
                                    ->children()
                                        ->arrayNode('js')
                                            ->cannotBeEmpty()->isRequired()
                                            ->children()
                                                ->scalarNode('output')
                                                    ->cannotBeEmpty()->isRequired()
                                                    ->example('"%kernel.root_dir%/../src/bundles/YourBundle/Resources/public/compiled"')
                                                ->end()
                                                ->scalarNode('service')
                                                    ->cannotBeEmpty()->isRequired()
                                                    ->example('fusion.includes.js.compiler')
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('css')
                                            ->cannotBeEmpty()->isRequired()
                                            ->children()
                                                ->scalarNode('output')
                                                    ->cannotBeEmpty()->isRequired()
                                                    ->example('"%kernel.root_dir%/../src/bundles/YourBundle/Resources/public/compiled"')
                                                ->end()
                                                ->scalarNode('service')
                                                    ->cannotBeEmpty()->isRequired()
                                                    ->example('fusion.includes.css.compiler')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                // data configuration
                ->arrayNode('data')
//                    ->children()
//                        ->arrayNode('injection')
//                            ->cannotBeEmpty()->isRequired()
//                            ->children()
//                                ->scalarNode('tag')
//                                    ->cannotBeEmpty()->isRequired()
//                                    ->example('<head>')
//                                ->end()
//                            ->end()
//                        ->end()
//                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
