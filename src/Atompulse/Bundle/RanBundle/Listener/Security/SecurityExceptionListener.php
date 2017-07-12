<?php
namespace Atompulse\RanBundle\Listener\Security;

use Atompulse\RanBundle\Service\Security\SecurityExceptionHandlerInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class SecurityExceptionListener
 * @package Atompulse\RanBundle\Listener\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class SecurityExceptionListener
{
    /**
     * @var SecurityExceptionHandlerInterface;
     */
    protected $authenticationExceptionHandler = null;
    /**
     * @var SecurityExceptionHandlerInterface;
     */
    protected $authorizationExceptionHandler = null;

    /**
     * @param SecurityExceptionHandlerInterface $authenticationExceptionHandler
     * @param SecurityExceptionHandlerInterface $authorizationExceptionHandler
     */
    public function __construct(SecurityExceptionHandlerInterface $authenticationExceptionHandler, SecurityExceptionHandlerInterface $authorizationExceptionHandler)
    {
        $this->authenticationExceptionHandler = $authenticationExceptionHandler;
        $this->authorizationExceptionHandler = $authorizationExceptionHandler;
    }

    /**
     * Intercept Exceptions and send valid ajax responses
     * The idea is to NOT send HTML response when an issue(i.e.session expired) is encountered
     * in an ajax request.
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Get the exception object from the received event
        $exception = $event->getException();

        // handle session expired
        if ($exception instanceof AuthenticationException) {
            $this->authenticationExceptionHandler->handleException($event);
        } // handle access denied exception
        elseif ($exception instanceof AccessDeniedException) {
            $this->authorizationExceptionHandler->handleException($event);
        }
    }
}