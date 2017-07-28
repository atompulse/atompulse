<?php
namespace Atompulse\Bundle\RanBundle\Service\Security;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Interface SecurityExceptionHandlerInterface
 * @package Atompulse\Bundle\RanBundle\Service\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface SecurityExceptionHandlerInterface
{
    /**
     * Handle the security exception
     * @param GetResponseForExceptionEvent $event
     * @return mixed
     */
    public function handleException(GetResponseForExceptionEvent $event);
}
