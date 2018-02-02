<?php
namespace Atompulse\Bundle\RadBundle\Services\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class AjaxExceptionHandlerService.
 *
 * @package Atompulse\Bundle\RadBundle\Services
 *
 * @author  Petru Cojocar <petru.cojocar@gmail.com>
 */
class AjaxExceptionHandlerService
{
    /**
     * Intercept Exceptions and send valid ajax responses
     * The idea is to NOT send HTML response for an ajax request
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Get the exception object from the received event
        $exception = $event->getException();

        // handle only AJAX
        if ($event->getRequest()->isXmlHttpRequest()) {
            $responseData = [
                'data'   => [
                    'exception' => [
                        'message' => $exception->getMessage(),
                        'class'   => \get_class($exception),
                        'file'    => \sprintf('%s:%s', $exception->getFile(), $exception->getLine()),
                        'trace'   => $exception->getTraceAsString(),
                    ],
                ],
                'msg'    => 'Application Exception',
                'status' => false,
            ];

            $response = new JsonResponse($responseData, 500);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
