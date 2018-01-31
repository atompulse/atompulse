<?php

namespace Atompulse\Bundle\FusionIncludeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Finder\Finder;

/**
 * Class FusionIncludeExtension
 * @package Atompulse\Bundle\FusionIncludeBundle\DependencyInjection
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludeExtension extends ConfigurableExtension
{
    // note that this method is called loadInternal and not load
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {


        print_r($mergedConfig);
        die;
    }
}