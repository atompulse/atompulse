<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Loader;

/**
 * Class CompiledLoader
 * @package Atompulse\Bundle\FusionBundle\Includes\Loader
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class CompiledLoader 
{
//    protected function handleCompiledLoading($response)
//    {
//        if (!$this->assetLoader->isCompiled()) {
//            $refiner = new Asset\Refine\SimpleRefiner();
//            $compiler = new Asset\Compiler\Compiler($this->container);
//            $compiler->addRefiner($refiner);
//            $this->assetLoader->setCompiler($compiler);
//            $this->assetLoader->compile();
//        }
//
//        // get all compiled assets
//        $assets = $this->assetLoader->getCompiledAssets();
//        // extract the injectable assets
//        $iAssets = $this->popInjectableAssetGroups($assets, true);
//
//        $content = $response->getContent();
//
//        // inject assets into response
//        $content = $this->injectAssetsToResponse($iAssets, $content, true);
//        // add loadable assets into response
//        $content = $this->addCompiledAssetsToResponse($assets, $content);
//
//        $response->setContent($content);
//    }
//
//    protected function addCompiledAssetsToResponse($assets, $content)
//    {
//        if (isset($assets['css']) && count($assets['css'])) {
//            $cssLoaderScript = $this->container->get('twig')->render('extension/load-compiled.html.twig', ['assets' => $assets['css']]);
//            $content = str_replace('</head>', $this->scriptCleanup($cssLoaderScript) . '</head>', $content);
//        }
//        if (isset($assets['js']) && count($assets['js'])) {
//            $jsLoaderScript = $this->container->get('twig')->render('extension/load-compiled.html.twig', ['assets' => $assets['js']]);
//            $content = str_replace('</head>', str_replace(["\n", "\t", '  '], '', $jsLoaderScript) . '</head>', $content);
//        }
//
//        return $content;
//    }

}
