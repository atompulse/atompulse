<?php
namespace Atompulse\Bundle\RadBundle\Services\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class AjaxExceptionHandlerService
 * @package Atompulse\Bundle\RadBundle\Services
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AjaxExceptionHandlerService
{
    /**
     * Intercept Exceptions and send valid ajax responses
     * The idea is to NOT send HTML response for an ajax request
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Get the exception object from the received event
        $exception = $event->getException();

        // handle only AJAX
        if ($event->getRequest()->isXmlHttpRequest()) {
            $headers = [];
            $responseData = [
                'status' => false,
                'msg' => 'Application Exception',
                'data' => [
                    'exception' => [
                        'message' => $exception->getMessage(),
                        'file' => "{$exception->getFile()}:{$exception->getLine()}",
                        'trace' => $exception->getTraceAsString()
                    ]
                ]
            ];
            $response = new Response(json_encode($responseData), 500, $headers);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }


}
