<?php

namespace Atompulse\Bundle\FusionBundle\DependencyInjection;

use Atompulse\Bundle\FusionBundle\Assets\Data\FusionImportData;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Finder\Finder;

/**
 * Class FusionExtension
 * @package Atompulse\Bundle\FusionBundle\DependencyInjection
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionExtension extends Extension
{
    /**
     * @var ContainerBuilder
     */
    protected $container = null;

    /**
     * @var YamlParser
     */
    protected $yamlParser = null;

    protected $importPaths = null;

    protected $fusionIncludesMap = null;

    protected $includesEnabled = false;

    protected $includesConfigured = false;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $this->yamlParser = new YamlParser();

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // add the config to the container
        $this->container->setParameter('fusion', $config);

        if (isset($config['includes']) && $config['includes']['enabled']) {
            // save state
            $this->includesEnabled = true;
            // get imports paths
            $this->importPaths = $this->container->getParameter('fusion')['includes']['imports'];
            $this->importParameter = $this->container->getParameter('fusion')['includes']['parameter'];
            // load imports specifications
            $imports = $this->collectImports($this->importPaths, $this->importParameter);
            // add assets paths
            $this->fusionIncludesMap['assets_paths'] = $config['includes']['paths'];
            // process the imports
            $this->processImports($imports);
            // save state
            $this->includesConfigured = true;
        }
        // add the state of fusion
        $this->container->setParameter('fusion_includes_enabled', $this->includesEnabled);
        // add the state of fusion configuration
        $this->container->setParameter('fusion_includes_configured', $this->includesConfigured);
        // add the fusion map to container
        $this->container->setParameter('fusion_includes_map', $this->fusionIncludesMap);

        // TODO: Compile the fusion map to deliverables and set a fusion_map_compiled into the container
        // the fusion map should be already compiled in the extension and then just loaded

        // Add Fusion Services
        $selfConfigLoader = new Loader\YamlFileLoader($this->container, new FileLocator(__DIR__.'/../Resources/config'));

        $selfConfigLoader->load('services.yml');
    }

    /**
     * Collect imports from specified locations
     * @param array $importPaths
     * @param string $parameter
     * @return array
     * @throws \Exception
     */
    protected function collectImports(array $importPaths, string $parameter)
    {
        $imports = [];
        foreach ($importPaths as $alias => $importPath) {
            $defaultImportFile = $importPath . DIRECTORY_SEPARATOR . $parameter;
            $importResource = is_file($importPath) ? $importPath : $defaultImportFile;
            if (file_exists($importResource)) {
                try {
                    $importData = $this->yamlParser->parse(file_get_contents($importResource));
                    $imports[$alias] = new FusionImportData($importData);
                } catch (\Exception $e) {
                    new \Exception("Unable parse import data structure in file [$importResource] from path [$importPath]");
                }
            } else {
                throw new \Exception("Import file [$importResource] from path [$importPath] was not found");
            }
        }

        return $imports;
    }

    /**
     * @param array $imports
     * @throws \Exception
     */
    protected function processImports(array $imports)
    {
        /**
         * @var FusionImportData $importData
         */
        foreach ($imports as $importAlias => $importData) {
            if ($importData->groups) {
                $this->resolveGroups($importData->groups);
            }
            if ($importData->controllers) {
                $this->resolveControllers($importData->controllers);
            }
            if ($importData->global) {
                $this->resolveGlobals($importData->global);
            }
            if ($importData->includes) {
                $this->resolveGlobals($importData->includes);
            }
        }
    }

    /**
     * Resolve Groups
     * @param $groupsConfig
     * @throws \Exception
     */
    protected function resolveGroups($groupsConfig)
    {
        //print_r($this->fusionMap);
        //print_r($groupsConfig);

        foreach ($groupsConfig as $group => $groupDefinition) {

            if (count($groupDefinition)) {

                $groupMap = ['js'=>[], 'css'=>[]];

                foreach ($groupDefinition as $defCnt => $definition) {
                    // check definition complexity (array like or simple string)
                    if (is_array($definition)) {

                        $assetEntry = key($definition);
                        // check definition style (with alias or simple numeric key)
                        if (is_numeric($assetEntry)) {
                            $assetAlias = false;
                            $assetDefinition = $definition[0];
                        } else {
                            $assetAlias = $assetEntry;
                            $assetDefinition = $definition[$assetEntry];
                        }

                        // do not support expression like: - alias: [include_path]
                        if (is_array($assetDefinition)) {
                            throw new \Exception("Asset definition is not supported <".var_export($assetDefinition, true)."> for group [$group]");
                        }


                        $asset = $this->resolveAssetPath($assetDefinition);
                        $assetMeta = pathinfo($asset['path']);
                        // single asset
                        if (isset($assetMeta['extension'])) {
                            $groupMap[$assetMeta['extension']][] = $asset;
                        }
                        // many assets in folder and subfolders
                        else {
                            $assets = $this->findAssetsInPath($asset['path'], $asset['web']);

                            if (count($assets['js'])) {
                                foreach ($assets['js'] as $assetAlias => $asset) {
                                    $groupMap['js'][] = $asset;
                                }
                            }
                            if (count($assets['css'])) {
                                foreach ($assets['css'] as $assetAlias => $asset) {
                                    $groupMap['css'][] = $asset;
                                }
                            }
                        }
                    }
                    // simple string
                    else {
                        $assetDefinition = $definition;
                        $asset = $this->resolveAssetPath($assetDefinition);
                        $assetMeta = pathinfo($asset['path']);
                        $assetAlias = $assetMeta['filename'];

                        // single asset
                        if (isset($assetMeta['extension'])) {
                            $groupMap[$assetMeta['extension']][] = $asset;
                        }
                        // many assets in folder and subfolders
                        else {
                            $assets = $this->findAssetsInPath($asset['path'], $asset['web']);

                            if (count($assets['js'])) {
                                foreach ($assets['js'] as $assetAlias => $asset) {
                                    $groupMap['js'][] = $asset;
                                }
                            }
                            if (count($assets['css'])) {
                                foreach ($assets['css'] as $assetAlias => $asset) {
                                    $groupMap['css'][] = $asset;
                                }
                            }
                        }
                    }
                }
            }

            $this->fusionIncludesMap['groups'][$group] = $groupMap;
        }
    }

    /**
     * Resolve Controllers includes
     * @param $controllersConfig
     * @throws \Exception
     */
    protected function resolveControllers($controllersConfig)
    {
        foreach ($controllersConfig as $controller => $config) {
            if (is_array($config)) {
                // make sure controller class has been specified
                if (!isset($config['class'])) {
                    throw new \Exception("Controller class for [$controller] must be declared");
                }
                // inline config
                if (!isset($config['import'])) {
                    $this->fusionIncludesMap['controllers'][$controller] = [
                        'class' => $config['class'],
                        'includes' => $this->processControllerConfig($config)
                    ];
                } // external file config
                else {
                    if ($this->importPaths) {
                        // syntax for import is [aliasOfPath, filename.yml]
                        $importPath = $config['import'][0];
                        // check if the aliasOfPath was declared in 'imports'
                        if (isset($this->importPaths[$importPath])) {
                            // build full path import file
                            $importFile = $this->importPaths[$importPath] . '/' . $config['import'][1];

                            if (file_exists($importFile)) {

                                $controllerConfig = $this->yamlParser->parse(file_get_contents($importFile));

                                $controllerIncludes = $this->processControllerConfig($controllerConfig);

                                $this->fusionIncludesMap['controllers'][$controller] = [
                                    'class' => $config['class'],
                                    'includes' => $controllerIncludes
                                ];

                            } else {
                                throw new \Exception ("Import file [$importFile] was not found");
                            }
                        } else {
                            throw new \Exception ("Import path alias [$importPath] is being referenced by [$controller]
                                but it wasn't found in current 'imports' <".var_export($this->importPaths, true).">");
                        }
                    } else {
                        throw new \Exception("Import paths ['fusion.includes.imports] contains no entries
                            but an import reference <".var_export($config['import'], true)."> was found for [$controller]");
                    }
                }
            } else {
                throw new \Exception("Invalid configuration <".var_export($config, true)."> found for [$controller]");
            }
        }
    }

    /**
     * Resolve Global includes
     * @param array $global
     * @throws \Exception
     */
    protected function resolveGlobals(array $global)
    {
        $this->fusionIncludesMap['global'] =  $this->processIncludes($global);
    }

    private function processControllerConfig($controllerConfig)
    {
        $controllerAssets = [
            'all' => [
                'js'=>[],
                'css'=>[],
                'groups'=>[]
            ],
            'actions' => []
        ];

        $allControllerIncludes = isset($controllerConfig['includes']['all']) ?
            $controllerConfig['includes']['all'] : false;
        $controllerActions = isset($controllerConfig['includes']['actions']) ?
            $controllerConfig['includes']['actions'] : false;
        $excludes = isset($controllerConfig['excludes']) ?
            $controllerConfig['excludes'] : false;

        if ($allControllerIncludes) {
            $controllerAssets['all'] = $this->processIncludes($allControllerIncludes);
        }

        if ($controllerActions) {
            foreach ($controllerActions as $action => $includes) {
                $controllerAssets['actions'][$action] = $this->processIncludes($includes);
            }
        }

        //TODO: process excludes

        return $controllerAssets;
    }

    /**
     * Process Supported Includes
     * @param $includes
     * @return array
     * @throws \Exception
     */
    private function processIncludes($includes)
    {
        /**
         * Processed Assets Structure
         */
        $processedAssets = [
            'js' => [],
            'css' => [],
            'groups' => []
        ];

        foreach ($includes as $definition) {
            // check definition complexity (array like or simple string)
            if (is_array($definition)) {
                $assetEntry = key($definition);
                // check definition style (with alias or simple numeric key)
                if (is_numeric($assetEntry)) {
                    $assetAlias = false;
                    $assetDefinition = $definition[0];
                } else {
                    $assetAlias = $assetEntry;
                    $assetDefinition = $definition[$assetEntry];
                }

                // do not support expression like: - alias: [include_path]
                if (is_array($assetDefinition)) {
                    throw new \Exception("Asset definition is not supported <" . var_export($definition, true).">");
                }

                $asset = $this->resolveAssetPath($assetDefinition);
                $assetMeta = pathinfo($asset['path']);
                $assetAlias = $assetAlias ? $assetAlias : $assetMeta['filename'];

                // single asset
                if (isset($assetMeta['extension'])) {
                    $processedAssets[$assetMeta['extension']][] = $asset;
                }
                // many assets in folder and subfolders
                else {

                    $assets = $this->findAssetsInPath($asset['path'], $asset['web']);

                    if (count($assets['js'])) {
                        foreach ($assets['js'] as $assetAlias => $asset) {
                            $processedAssets['js'][] = $asset;
                        }
                    }
                    if (count($assets['css'])) {
                        foreach ($assets['css'] as $assetAlias => $asset) {
                            $processedAssets['css'][] = $asset;
                        }
                    }
                }
            } // string - single asset or group
            else {
                // check if $definition is a group definition
                if (isset($this->fusionIncludesMap['groups'][$definition])) {
                    $processedAssets['groups'][] = $definition;
                } // simple string asset definition
                else {
                    $assetDefinition = $definition;
                    $asset = $this->resolveAssetPath($assetDefinition);
                    $assetMeta = pathinfo($asset['path']);
                    $assetAlias = $assetMeta['filename'];

                    // single asset
                    if (isset($assetMeta['extension'])) {
                        $processedAssets[$assetMeta['extension']][] = $asset;
                    }
                    // many assets in folder and subfolders
                    else {
                        $assets = $this->findAssetsInPath($asset['path'], $asset['web']);
                        if (count($assets['js'])) {
                            foreach ($assets['js'] as $assetAlias => $asset) {
                                $processedAssets['js'][] = $asset;
                            }
                        }
                        if (count($assets['css'])) {
                            foreach ($assets['css'] as $assetAlias => $asset) {
                                $processedAssets['css'][] = $asset;
                            }
                        }
                    }
                }
            }
        }

        return $processedAssets;
    }

    private function resolveAssetPath(string $assetPath)
    {
        $assetPath = strtolower($assetPath);
        $assetParts = explode('/', $assetPath);
        $assetAliasPath = array_shift($assetParts);

        if (isset($this->fusionIncludesMap['assets_paths'][$assetAliasPath])) {
            $asset = [
                'path' => str_replace($assetAliasPath, $this->fusionIncludesMap['assets_paths'][$assetAliasPath]['path'], $assetPath),
                'web' => str_replace($assetAliasPath, $this->fusionIncludesMap['assets_paths'][$assetAliasPath]['web'], $assetPath),
            ];
        } else {
            throw new \Exception("Asset Alias Path <" . var_export($assetAliasPath, true)."> is not registered");
        }

        return $asset;
    }

    private function findAssetsInPath(string $realPath, string $webPath = '')
    {
        $assets = ['js'=>[],'css'=>[]];



        $finder = Finder::create();
        $files = $finder->files()->name('*.js')->in($realPath);

        if (count($files)) {
            foreach ($files as $file) {
                $assets['js'][] = [
                    'path' => $file->getRealPath(),
                    'web'  => str_replace('\\', '/', $webPath . '/' . $file->getRelativePathname()),
                ];
            }
        }

        $finder = Finder::create();
        $files = $finder->files()->name('*.css')->in($realPath);
        if (count($files)) {
            foreach ($files as $file) {
                $assets['css'][] = [
                    'path' => $file->getRealPath(),
                    'web'  => str_replace('\\', '/', $webPath . '/' . $file->getRelativePathname()),
                ];
            }
        }

        return $assets;
    }

}