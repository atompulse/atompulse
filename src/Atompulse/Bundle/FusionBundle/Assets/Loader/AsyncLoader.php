<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Loader;

use Atompulse\Bundle\FusionBundle\Assets\Helper\ControllerActionResolverTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Atompulse\Component\Data;

/**
 * Class AsyncLoader
 * @package Atompulse\Bundle\FusionBundle\Includes\Loader
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AsyncLoader implements AssetsLoaderInterface
{
    use ContainerAwareTrait;
    use ControllerActionResolverTrait;

    protected $fusionIncludesMap = null;

    /**
     * @param Response $response
     * @param Request $request
     * @throws \Exception
     */
    public function addAssetsToResponse(Response $response, Request $request)
    {
        $context = $this->getControllerAndAction($request);

        $refiner = $this->container->get('fusion.assets.refiner');
        $this->fusionIncludesMap = $this->container->getParameter('fusion_includes_map');

        // check for controller configured assets
        $ctrlIncludes = null;

        if (isset($this->fusionIncludesMap['controllers'])) {
            $ctrlConfigPath = Data\DataFinder::searchRecursive($context['controller'], $this->fusionIncludesMap['controllers'], 'class');
            if ($ctrlConfigPath) {
                $ctrlIncludes = $this->fusionIncludesMap['controllers'][$ctrlConfigPath[0]]['includes'];
            }
        }

        $assets = $this->prepareAssets($this->container->getParameter('fusion_includes_map'), $ctrlIncludes, $context['action']);

        // add css
        if (count($assets['css'])) {
            $cssIncludes = [
                'assets' => $assets['css'],
                'type' => 'css'
            ];
            $loaderScript = $this->container->get('twig')->render('includes-async.html.twig', $cssIncludes);
            $response->setContent(str_replace('<!--@fusion_inject_css-->', $refiner::refine($loaderScript), $response->getContent()));
        }
        // add js
        if (count($assets['js'])) {
            $jsIncludes = [
                'assets' => $assets['js'],
                'type' => 'js'
            ];
            $loaderScript = $this->container->get('twig')->render('includes-async.html.twig', $jsIncludes);
            $response->setContent(str_replace('<!--@fusion_inject_js-->', $refiner::refine($loaderScript), $response->getContent()));
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

        // include global
        $assets['js'] = isset($fusionMap['global']['js']) ? $fusionMap['global']['js'] : [];
        $assets['css'] = isset($fusionMap['global']['css']) ? $fusionMap['global']['css'] : [];
        $groups = isset($fusionMap['global']['groups']) ? $fusionMap['global']['groups'] : [];

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
} 