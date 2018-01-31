<?php

namespace Atompulse\Bundle\FusionIncludeBundle\DependencyInjection;

use Atompulse\Component\FusionInclude\Assets\Data\FusionAsset;
use Atompulse\Component\FusionInclude\Assets\Data\FusionIncludeNamespace;
use Atompulse\Component\FusionInclude\FusionIncludeEngine;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


/**
 * Class FusionIncludeExtension
 * @package Atompulse\Bundle\FusionIncludeBundle\DependencyInjection
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludeExtension extends ConfigurableExtension
{

    /**
     * @inheritdoc
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
//        print_r($mergedConfig);die;
        $engine = new FusionIncludeEngine();

        $namespaces = $this->processNamespaces($mergedConfig['namespaces']);
        $assets = $this->processImports($mergedConfig['imports'], $namespaces);

        foreach ($namespaces as $namespace) {
            $engine->addNamespace($namespace);
        }

        foreach ($assets as $asset) {
            $engine->addAsset($asset);
        }

//        $container->get('');
//
//        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);
//        $content = $serializer->serialize($engine, 'xml');
//
//        print $content;
//
////        var_dump($engine->getAssets());

    }

    /**
     * @param array $data
     * @return array
     */
    protected function processNamespaces(array $data) : array
    {
        $namespaces = [];

        foreach ($data as $namespace => $config) {
            $namespaces[$namespace] = new FusionIncludeNamespace($config);
        }

        return $namespaces;
    }

    /**
     * @param array $data
     * @param array $namespaces
     * @return FusionAsset
     */
    protected function processAsset(array $data, array $namespaces) : FusionAsset
    {
        // TODO: validate structure using a Configuration
        $asset = new FusionAsset();
        $asset->name = key($data);
        $asset->fromArray($data[$asset->name], false);

        foreach ($asset->files as &$file) {
            if(strpos($file, '@{') !== false) {
                $value = preg_replace_callback(
                    '/\@\{([^\@}]+)\}/',
                    function ($matches) use ($data, $namespaces) {
                        $annotatedNamespace = $matches[0];
                        $declaredNamespace = $matches[1];
                        if (array_key_exists($declaredNamespace, $namespaces)) {
                            return $namespaces[$declaredNamespace]->source;
                        } else {
                            throw new \Exception("Namespace [$declaredNamespace] referenced as [$annotatedNamespace] does not exists");
                        }
                    },
                    $file
                );

                $file = $value;
            }
        }

        return $asset;
    }

    /**
     * @param array $imports
     * @return array
     * @throws \Exception
     */
    protected function processImports(array $imports, array $namespaces)
    {
        $assets = [];
        foreach ($imports as $importFile) {
            if (file_exists($importFile)) {

                $assetData = Yaml::parse(file_get_contents($importFile));

                $asset = $this->processAsset($assetData, $namespaces);
                $assets[$asset->name] = $asset;

            } else {
                throw new \Exception("Import file [$importFile] was not found");
            }
        }

        return $assets;
    }


}