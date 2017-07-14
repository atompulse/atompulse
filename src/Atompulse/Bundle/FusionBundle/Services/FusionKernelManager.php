<?php
namespace Atompulse\FusionBundle\Services;

use Atompulse\FusionBundle\Compiler\Refiner\SimpleRefiner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class FusionKernelManager
 * @package Atompulse\FusionBundle\Services
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionKernelManager 
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    /**
     * @var bool
     */
    protected $isQualifiedRequest = false;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest() && $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $this->isQualifiedRequest = true;
//            $this->controller = $event->getController()[0];
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->isQualifiedRequest) {
            $response->setContent($this->addFusionKernelToResponse($response->getContent()));
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        $isAjaxRequest = $request->isXmlHttpRequest();

        $exception = $event->getException();

        if ($isAjaxRequest) {

            $headers = [];

            // handle session expired
            if ($exception instanceof ResourceNotFoundException || $exception instanceof NotFoundHttpException) {

                $headers['Resource-Not-Found'] = true;
                $headers['Content-Type'] = 'application/json';

                $responseData = [
                    'status' => false,
                    'msg'    => 'Resource not found.',
                    'data'   => [
                        'exception' => [
                            'message' => $exception->getMessage(),
                        ]
                    ]
                ];
                $response = new Response(json_encode($responseData), 200, $headers);
                $event->setResponse($response);
            }
        } else {
            $this->isQualifiedRequest = true;
        }
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function addFusionKernelToResponse($content)
    {
        $params = ['data' => []];
        $scriptContent = SimpleRefiner::refine($this->container->get('twig')->render('kernel.js.twig', $params));

        // perform injection tag replacement
        $content = str_replace('<!--@fusion_inject_kernel-->', $scriptContent, $content);

        return $content;
    }
} 