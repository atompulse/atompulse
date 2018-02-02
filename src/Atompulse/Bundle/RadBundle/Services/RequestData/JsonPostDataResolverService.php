<?php
namespace Atompulse\Bundle\RadBundle\Services\RequestData;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class JsonPostDataResolverService
 *
 * @package Atompulse\Bundle\RadBundle\Services\RequestData
 *
 * @author  Petru Cojocar <petru.cojocar@gmail.com>
 */
class JsonPostDataResolverService
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->getMethod() === $request::METHOD_POST) {
            // check if we have a json request
            if (\strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                // get the json request data
                $data = \json_decode($request->getContent(), true);
                // mapp params data to request
                $request->request->replace(\is_array($data) ? $data : []);
            }
        }
    }
}
