<?php
namespace Atompulse\FusionBundle\Includes\Loader;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Atompulse\FusionBundle\Compiler\Refiner\SimpleRefiner;
use Atompulse\Component\Data;

/**
 * Class AsyncLoader
 * @package Atompulse\FusionBundle\Includes\Loader
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AsyncLoader 
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    protected $fusionIncludesMap = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Response $response
     * @param $controller
     * @param $action
     */
    public function addAssetsToResponse(Response $response, $controller, $action)
    {
        $fullQualifiedClass = get_class($controller);
        $this->fusionIncludesMap = $this->container->getParameter('fusion_includes_map');

//        print '<pre>';print_r($this->container->getParameter('fusion_includes_map'));

        // check for controller configured assets
        $ctrlIncludes = null;
        if (isset($this->fusionIncludesMap['controllers'])) {
            $ctrlConfigPath = Data\DataFinder::searchRecursive($fullQualifiedClass, $this->fusionIncludesMap['controllers'], 'class');
            if ($ctrlConfigPath) {
                $ctrlIncludes = $this->fusionIncludesMap['controllers'][$ctrlConfigPath[0]]['includes'];
            }
        }

        $assets = $this->prepareAssets($this->container->getParameter('fusion_includes_map'), $ctrlIncludes, $action);

        if (count($assets['css'])) {
            // add css
            $loaderScript = $this->container->get('twig')->render('includes-async.html.twig',
                ['assets' => $assets['css'],
                 'type' => 'css']);
            $response->setContent(str_replace('<!--@fusion_inject_css-->',
                SimpleRefiner::refine($loaderScript), $response->getContent()));
        }
        if (count($assets['js'])) {
            // add js
            $loaderScript = $this->container->get('twig')->render('includes-async.html.twig',
                ['assets' => $assets['js'],
                 'type' => 'js']);
            $response->setContent(str_replace('<!--@fusion_inject_js-->',
                SimpleRefiner::refine($loaderScript), $response->getContent()));
        }
    }

    /**
     * @param $fusionMap
     * @param null $ctrlIncludes
     * @param null $action
     * @return array
     */
    protected function prepareAssets($fusionMap, $ctrlIncludes = null, $action = null)
    {
        $assets = ['js' => [], 'css' => []];
        $groups = [];
        $controllerAssets = ['js' => [], 'css' => []];
        $controllerActionAssets = ['js' => [], 'css' => []];

        // include globals
        $assets['js'] = isset($fusionMap['globals']['js']) ? $fusionMap['globals']['js'] : [];
        $assets['css'] = isset($fusionMap['globals']['css']) ? $fusionMap['globals']['css'] : [];
        $groups = isset($fusionMap['globals']['groups']) ? $fusionMap['globals']['groups'] : [];

        // include all for controller
        if ($ctrlIncludes && count($ctrlIncludes['all'])) {
            $groups = array_merge($groups, $ctrlIncludes['all']['groups']);
            $controllerAssets['js'] = $ctrlIncludes['all']['js'];
            $controllerAssets['css'] = $ctrlIncludes['all']['css'];
        }
        // include specific for action
        if ($ctrlIncludes && isset($ctrlIncludes['actions'][$action])) {
            $groups = array_merge($groups, $ctrlIncludes['actions'][$action]['groups']);
            $controllerActionAssets['js'] = $ctrlIncludes['actions'][$action]['js'];
            $controllerActionAssets['css'] = $ctrlIncludes['actions'][$action]['css'];
        }

        $groups = array_unique($groups);

        // groups
        if (count($groups)) {
            foreach ($groups as $group) {
                $assets['js'] = $this->mergeAssets($assets['js'], $fusionMap['groups'][$group]['js']);
                $assets['css'] = $this->mergeAssets($assets['css'], $fusionMap['groups'][$group]['css']);
            }
        }

        // finally merge all given assets
        $assets['js'] = $this->mergeAssets($assets['js'],
                            $this->mergeAssets($controllerAssets['js'], $controllerActionAssets['js']));
        $assets['css'] = $this->mergeAssets($assets['css'],
                            $this->mergeAssets($controllerAssets['css'], $controllerActionAssets['css']));

        return $assets;
    }

    /**
     * @param $currentAssets
     * @param $newAssets
     * @return array
     */
    protected function mergeAssets($currentAssets, $newAssets)
    {
        $mergedAssets = $currentAssets;

        foreach ($newAssets as $asset) {
            $exists =  Data\DataFinder::searchRecursive($asset['web'], $mergedAssets, 'web');
            if (!$exists) {
                $mergedAssets[] = $asset;
            }
//            if (isset($mergedAssets[$alias])) {
//                // compare web path
//                if ($mergedAssets[$alias]['web'] != $asset['web']) {
//                    $newAssetAlias = $this->generateAssetAlias($alias, $mergedAssets);
//                    print "\n\n\n";
//                    print $alias."\n";
//                    print_r($asset);
//                    print "\n\n\n";
//                    $mergedAssets[$newAssetAlias] = $asset;
//                }
//            } // no conflicts
//            else {
//                $mergedAssets[$alias] = $asset;
//            }
        }

        return $mergedAssets;
    }

    private function generateAssetAlias($key, $source)
    {
        $suffix = 1;
        while (isset($source[$key])) {
            $key = $key .'_'. $suffix;
            $suffix++;
        }

        return $key;
    }


//    protected function addAsyncAssetsToResponse($assets, $content)
//    {
//        $preparedAssets = ['js' => [], 'css' => []];
//        foreach ($assets['js'] as $order => $assetGroup) {
//            foreach ($assetGroup as $assetGroupName => $assetGroupAssets ) {
//                foreach ($assetGroupAssets as $assetName => $asset) {
//                    $preparedAssets['js'][$assetName] = $this->getAssetUrl($asset);
//                }
//            }
//        }
//        foreach ($assets['css'] as $order => $assetGroup) {
//            foreach ($assetGroup as $assetGroupName => $assetGroupAssets ) {
//                foreach ($assetGroupAssets as $assetName => $asset) {
//                    $preparedAssets['css'][$assetName] = $this->getAssetUrl($asset);
//                }
//            }
//        }
//
//        // add css
//        $cssLoaderScript = $this->container->get('twig')->render('extension/load-async.html.twig', ['assets' => $preparedAssets['css']]);
//        $content = str_replace('</head>', $this->scriptCleanup($cssLoaderScript) . '</head>', $content);
//        // add js
//        $jsLoaderScript = $this->container->get('twig')->render('extension/load-async.html.twig', ['assets' => $preparedAssets['js']]);
//        $content = str_replace('</head>', $this->scriptCleanup($jsLoaderScript) . '</head>', $content);
//
//        return $content;
//    }


//    protected function handleAsyncLoading($response)
//    {
//        $assets = $this->assetLoader->getAssets();
//
//        // extract the injectable assets
//        $iAssets = $this->popInjectableAssetGroups($assets);
//
//        $content = $response->getContent();
//
//        // inject assets into response
//        $content = $this->injectAssetsToResponse($iAssets, $content);
//        // add loadable assets into response
//        $content = $this->addAsyncAssetsToResponse($assets, $content);
//
//        $response->setContent($content);
//    }
} 