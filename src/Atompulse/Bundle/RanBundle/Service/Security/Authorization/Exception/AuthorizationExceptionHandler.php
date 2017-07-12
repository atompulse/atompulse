<?php
namespace Atompulse\RanBundle\Service\Security\Authorization\Exception;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

use Atompulse\RanBundle\Service\Security\SecurityExceptionHandlerInterface;

/**
 * Class AuthorizationExceptionHandler
 * @package Atompulse\RanBundle\Service\Security\Authorization\Exception
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AuthorizationExceptionHandler implements SecurityExceptionHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handleException(GetResponseForExceptionEvent $event)
    {
        // handle only AJAX
        if ($event->getRequest()->isXmlHttpRequest()) {
            $arrHeaders = [];
            $arrHeaders['Ran-Auth-NotAuthorized'] = true;

            $response = new Response('Not authorized', 403, $arrHeaders);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
