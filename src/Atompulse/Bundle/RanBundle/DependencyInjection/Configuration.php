<?php

namespace Atompulse\Bundle\RanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Atompulse RAN Bundle Configuration
 * @package Atompulse\Bundle\RanBundle\DependencyInjection
 *
 * This is the class that validates and merges configuration from app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
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
        $rootNode = $treeBuilder->root('ran');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('security')
                    ->children()
                        ->variableNode('override')
                            ->info('Set a list of roles that will override the security system')
                            ->example('[ROLE_ADMIN, ROLE_SUPER_ADMIN]')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('ignore_inexistent_role')
                            ->info('Set to false to throw exception when a role is checked but it does not exist')
                            ->example('true')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('generator')
                    ->cannotBeEmpty()->isRequired()
                    ->children()
                        ->scalarNode('output')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the location where the ran system will generate the yml files')
                            ->example('%kernel.root_dir%/../src/bundles/YourBundle/Resources/config/ran')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('menu')
                    ->children()
                        ->scalarNode('source')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the location of the menu yml file')
                            ->example('%kernel.root_dir%/../src/bundles/YourBundle/Resources/config/ran/menu.yml')
                        ->end()
                        ->scalarNode('param')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the parameter name (under the \'parameters:\') which holds the menu settings')
                            ->example('menu')
                        ->end()
                        ->scalarNode('session')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the name of the parameter that will be used to store the menu settings in the session')
                            ->example('application.menu')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('ui_tree')
                    ->children()
                        ->scalarNode('source')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the location of the ui tree yml file')
                            ->example('%kernel.root_dir%/../src/bundles/YourBundle/Resources/config/ran/ran_ui_tree.yml')
                        ->end()
                        ->scalarNode('param')
                            ->cannotBeEmpty()->isRequired()
                            ->info('Set the parameter name (under the \'parameters:\') which holds the ui tree settings')
                            ->example('ui_tree')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
