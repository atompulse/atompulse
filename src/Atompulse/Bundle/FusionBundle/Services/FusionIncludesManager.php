<?php
namespace Atompulse\Bundle\FusionBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class Includes Manager
 * @package Atompulse\Bundle\FusionBundle\Services
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludesManager
{
    use ContainerAwareTrait;

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
     * @var \Atompulse\Bundle\FusionBundle\Assets\Loader\AssetsLoaderInterface
     */
    protected $loader = null;

    /**
     * @param GetResponseEvent $event
     * @throws \Exception
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->container->hasParameter('fusion_includes_enabled') && $this->container->getParameter('fusion_includes_enabled')) {

            $this->includesReady = $this->container->getParameter('fusion_includes_configured');

            if ($this->includesReady) {
                if ($this->container->getParameter('fusion')['includes']['compiler']['enabled']) {
                    $this->loader = $this->container->get('fusion.assets.compiled.loader');
                } else {
                    // check if the fusion map has been created
                    if ($this->container->hasParameter('fusion_includes_map') && !is_null($this->container->getParameter('fusion_includes_map'))) {
                        $this->loader = $this->container->get('fusion.assets.async.loader');
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
            $this->loader->addAssetsToResponse($event->getResponse(), $event->getRequest());
        }
    }
}