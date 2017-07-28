<?php

namespace Atompulse\Bundle\RanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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

        $selfConfigLoader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        // Add RAN Services
        $selfConfigLoader->load('services.yml');

        // configure loader for generated files
        $externalConfigLoader = new Loader\YamlFileLoader($container, new FileLocator($config['generator']['output']));

        // load generated files
        $externalConfigLoader->load('role_access_names_gui.yml');
        $externalConfigLoader->load('role_access_names_system.yml');

        // handle menu and ui_tree
        if (isset($config['menu'])) {
            $externalConfigLoader->load($config['menu']['source']);
            $container->setParameter(
                'ran_menu',
                [
                    'data' => $container->getParameter($config['menu']['param']),
                    'session' => $config['menu']['session']
                ]
            );
        } else {
            $container->setParameter('ran_menu', null);
        }
        if (isset($config['ui_tree'])) {
            $externalConfigLoader->load($config['ui_tree']['source']);
        }
    }

    /**
     * @return array
     */
    protected function prepareDefaultRanSecuritySettings()
    {
        $security = [
            'override' => [],
            'ignore_inexistent_role' => true
        ];

        return $security;
    }
}
