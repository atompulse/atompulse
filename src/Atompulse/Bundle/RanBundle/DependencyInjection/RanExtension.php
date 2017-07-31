<?php

namespace Atompulse\Bundle\RanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Atompulse RAN Extension
 * @package Atompulse\Bundle\RanBundle\DependencyInjection
 *
 * This is the class that loads and manages bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class RanExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // check for optional security settings
        if (!isset($config['security'])) {
            $config['security'] = $this->prepareDefaultRanSecuritySettings();
        }
        // set processed 'ran' container parameter
        $container->setParameter('ran', $config);

        // add 'ran_security' as a parameter
        $container->setParameter('ran_security', $config['security']);

        // add RAN Services
        $selfConfigLoader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $selfConfigLoader->load('services.yml');

        // configure loader for generated files
        $generatorLoader = new Loader\YamlFileLoader($container, new FileLocator($config['generator']['output']));
        try {
            // load generated files
            $generatorLoader->load('role_access_names_gui.yml');
            $generatorLoader->load('role_access_names_system.yml');
        } catch (\Exception $e) {
            $container->setParameter('ran_sys', $this->prepareDefaultRanSystemSettings());
            $container->setParameter('ran_gui', $this->prepareDefaultRanGuiSettings());
        }

        // handle menu loading
        if (isset($config['menu']) && file_exists($config['menu']['source'])) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(dirname($config['menu']['source'])));
            $loader->load($config['menu']['source']);
            // register menu in the container
            $container->setParameter(
                'ran_menu',
                [
                    'data' => $container->getParameter($config['menu']['param']),
                    'session' => $config['menu']['session']
                ]
            );

            // security not enabled
            if (!$container->getParameter('ran_security')['enabled']) {
                $container->removeDefinition('ran.menu.builder.listener_with_authorization');
            } else {
                $container->removeDefinition('ran.menu.builder.listener_without_authorization');
            }
        } else {
            $container->setParameter('ran_menu', null);
        }

        // handle ui tree loading
        if (isset($config['ui_tree']) && file_exists($config['ui_tree']['source'])) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(dirname($config['ui_tree']['source'])));
            $loader->load($config['ui_tree']['source']);
        } else {
            $container->setParameter('ui_tree', null);
        }
    }

    /**
     * @return array
     */
    protected function prepareDefaultRanSecuritySettings()
    {
        $settings = [
            'enabled' => true,
            'override' => [],
            'ignore_inexistent_role' => true
        ];

        return $settings;
    }

    /**
     * @return array
     */
    protected function prepareDefaultRanSystemSettings()
    {
        $settings = [
            'hierarchy' => [],
            'requirements' => [],
        ];

        return $settings;
    }

    /**
     * @return array
     */
    protected function prepareDefaultRanGuiSettings()
    {
        $settings = [
        ];

        return $settings;
    }


}
