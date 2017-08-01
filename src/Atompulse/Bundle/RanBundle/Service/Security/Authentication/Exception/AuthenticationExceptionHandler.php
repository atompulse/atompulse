<?php
namespace Atompulse\Bundle\RanBundle\Service\Security\Authentication\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Atompulse\Bundle\RanBundle\Service\Security\SecurityExceptionHandlerInterface;

/**
 * Class AuthenticationExceptionHandler
 * @package Atompulse\Bundle\RanBundle\Services\Authentication\Exception
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class AuthenticationExceptionHandler implements SecurityExceptionHandlerInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $tokenManager;

    public function __construct($router, $tokenManager)
    {
        $this->router = $router;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @inheritdoc
     */
    public function handleException(GetResponseForExceptionEvent $event)
    {
        // handle only AJAX
        if ($event->getRequest()->isXmlHttpRequest()) {
            $token = $this->tokenManager->getToken('authenticate');
            if (!$this->tokenManager->isTokenValid($token)) {
                $token = $this->tokenManager->refreshToken('authenticate');
            }

            $loginUrl = $this->router->generate('fos_user_security_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $loginCheckUrl = $this->router->generate('fos_user_security_check', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // add custom header to redirect to login page on the client
            $arrHeaders = [];

            $arrHeaders['Ran-Auth-Expired'] = true;
            $arrHeaders['Ran-Auth-Login'] = $loginUrl;
            $arrHeaders['Ran-Auth-Login-Check'] = $loginCheckUrl;
            $arrHeaders['Ran-Auth-Token'] = $token->getValue();

            $response = new Response('Authentication Required', 301, $arrHeaders);

            $event->setResponse($response);
            $event->stopPropagation();
        }

    }
}