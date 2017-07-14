<?php
namespace Atompulse\FusionBundle\Services;

use Atompulse\FusionBundle\Includes\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class Includes Manager
 * @package Atompulse\FusionBundle\Services
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludesManager
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    /**
     * Keep the includes state in local variable to avoid constantly
     * accessing the container for this state.
     * @var bool
     */
    protected $includesReady = false;

    /**
     * @var bool
     */
    protected $isQualifiedRequest = false;

    /**
     * @var  \Symfony\Bundle\FrameworkBundle\Controller\Controller
     */
    protected $controller = null;

    /**
     * @var \Atompulse\FusionBundle\Includes\Loader\AsyncLoader | \Atompulse\FusionBundle\Includes\Loader\CompiledLoader
     */
    protected $loader = null;

    /**
     * FusionIncludesManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if ($this->container->hasParameter('fusion_includes_enabled') && $this->container->getParameter('fusion_includes_enabled')) {

            $this->includesReady = $this->container->getParameter('fusion_includes_configured');

            if ($this->includesReady) {
                if ($this->container->getParameter('fusion')['includes']['compiler']['enabled']) {
                    $this->loader = new Loader\CompiledLoader($this->container);
                } else {
                    // check if the fusion map has been created
                    if ($this->container->hasParameter('fusion_includes_map') && !is_null($this->container->getParameter('fusion_includes_map'))) {
                        $this->loader = new Loader\AsyncLoader($this->container);
                    } else {
                        throw new \Exception("Fusion Includes enabled but [fusion_includes_map] was not created in container..");
                    }
                }
            }
        }
    }

    /**
     * Decide if the current request is qualified for includes.
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->includesReady) {

            if (!$event->isMasterRequest()) {
                return;
            }

            $request = $event->getRequest();

            if (!$request->isXmlHttpRequest() && $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
                $this->isQualifiedRequest = true;
                $this->controller = $event->getController()[0];
            }
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        $isAjaxRequest = $request->isXmlHttpRequest();

        if (!$isAjaxRequest) {
            $this->isQualifiedRequest = true;
        }
    }

    /**
     * Inject the loaded assets based on configured strategy
     * @param FilterResponseEvent $event
     * @throws \Exception
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->includesReady && $this->isQualifiedRequest) {
            // obtain action name
            $paramsDefault = explode('::', $event->getRequest()->attributes->get('_controller'));
            $paramsBackup = explode(':', $event->getRequest()->attributes->get('_controller'));

            $params =  count($paramsDefault) > 1 ? $paramsDefault : $paramsBackup;

            if (count($params) > 1) {
                $action = substr($params[1], 0, -6);
                $this->loader->addAssetsToResponse($event->getResponse(), $this->controller, $action);
            } else {
                throw new \Exception("[Fusion Includes Manager] Couldn't extract action name. Maybe cache should be cleared?");
            }
        }
    }

//    /**
//     * Load an asset group
//     * @param string $assetGroup
//     */
//    public function loadGroup($assetGroup)
//    {
//        $this->assetLoader->load($assetGroup);
//    }
//
//    /**
//     * Inject an asset group
//     * @param string $assetGroup
//     */
//    public function injectGroup($assetGroup)
//    {
//        $this->assetLoader->load($assetGroup);
//        $this->injectableAssetGroups[] = $assetGroup;
//    }
//
//    /**
//     * Add an runtime asset group that was not previously setup
//     * @param string $assetGroup
//     */
//    public function addAssetGroup($assetGroup, $assets, $assetType)
//    {
//        $this->assetLoader->addAssetGroup($assetGroup, $assets, $assetType);
//    }
//
//   protected function popInjectableAssetGroups(&$assets, $isCompiled = false)
//    {
//        $iAssets = ['js' => [], 'css' => []];
//        if ($isCompiled) {
//            foreach ($this->injectableAssetGroups as $assetGroup) {
//                if (array_key_exists($assetGroup, $assets['js'])) {
//                    $iAssets['js'][$assetGroup] = $assets['js'][$assetGroup];
//                    unset($assets['js'][$assetGroup]);
//                } elseif (array_key_exists($assetGroup, $assets['css'])) {
//                    $iAssets['css'][$assetGroup] = $assets['css'][$assetGroup];
//                    unset($assets['css'][$assetGroup]);
//                }
//            }
//        } else {
//            foreach ($this->injectableAssetGroups as $assetGroup) {
//                foreach ($assets['js'] as $order => $oAssets) {
//                    if (array_key_exists($assetGroup, $oAssets)) {
//                        $iAssets['js'][$assetGroup] = $oAssets[$assetGroup];
//                        unset($assets['js'][$order]);
//                        break;
//                    }
//                }
//                foreach ($assets['css'] as $order => $oAssets) {
//                    if (array_key_exists($assetGroup, $oAssets)) {
//                        $iAssets['css'][$assetGroup] = $oAssets[$assetGroup];
//                        unset($assets['css'][$order]);
//                        break;
//                    }
//                }
//            }
//        }
//
//        return $iAssets;
//    }
//
//    protected function injectAssetsToResponse($assets, $content, $isCompiled = false)
//    {
//        if ($isCompiled) {
//            if (count($assets['css'])) {
//                foreach ($assets['css'] as $assetGroup => $assetContent) {
//                    $assetGroupContent = file_get_contents($this->assetLoader->getCompiledAssetGroupTrace($assetGroup, 'css'));
//                    $params = ['content' => $assetGroupContent, 'contentType' => 'css'];
//                    $scriptContent = $this->container->get('twig')->render('extension/inject.html.twig', $params);
//                    $content = str_replace("<!--@inject $assetGroup-->", "<!--$assetGroup-->".$scriptContent."<!--$assetGroup-->", $content);
//                }
//            }
//            if (count($assets['js'])) {
//                foreach ($assets['js'] as $assetGroup => $assetContent) {
//                    $assetGroupContent = file_get_contents($this->assetLoader->getCompiledAssetGroupTrace($assetGroup, 'js'));
//                    $params = ['content' => $assetGroupContent, 'contentType' => 'js'];
//                    $scriptContent = $this->container->get('twig')->render('extension/inject.html.twig', $params);
//                    $content = str_replace("<!--@inject $assetGroup-->", "<!--$assetGroup-->".$scriptContent."<!--$assetGroup-->", $content);
//                }
//            }
//        }
//        else {
//            if (count($assets['css'])) {
//                foreach ($assets['css'] as $assetGroup => $iAssets) {
//                    $assetGroupContent = '';
//                    foreach ($iAssets as $assetName => $asset) {
//                        $fullPath = $this->container->get('kernel')->locateResource('@'.$asset);
//                        $assetGroupContent .= file_get_contents($fullPath)."\n";
//                    }
//                    $params = ['content' => $assetGroupContent, 'contentType' => 'css'];
//                    $scriptContent = $this->container->get('twig')->render('extension/inject.html.twig', $params);
//                    $content = str_replace("<!--@inject $assetGroup-->", "<!--$assetGroup-->".$scriptContent."<!--$assetGroup-->", $content);
//                }
//            }
//            if (count($assets['js'])) {
//                foreach ($assets['js'] as $assetGroup => $iAssets) {
//                    $assetGroupContent = '';
//                    foreach ($iAssets as $assetName => $asset) {
//                        $fullPath = $this->container->get('kernel')->locateResource('@'.$asset);
//                        $assetGroupContent .= file_get_contents($fullPath)."\n";
//                    }
//                    $params = ['content' => $assetGroupContent, 'contentType' => 'js'];
//                    $scriptContent = $this->container->get('twig')->render('extension/inject.html.twig', $params);
//
//                    $content = str_replace("<!--@inject $assetGroup-->", "<!--$assetGroup-->".$scriptContent."<!--$assetGroup-->", $content);
//                }
//            }
//        }
//
//        return $content;
//    }
}