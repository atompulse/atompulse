<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Helper;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait ControllerActionResolver
 * @package Atompulse\Bundle\FusionBundle\Assets\Loader\Helper
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait ControllerActionResolverTrait
{

    /**
     * Get the controller name and action name from request
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getControllerAndAction(Request $request)
    {
        $data = [
            'action' => null,
            'controller' => null,
        ];

       // obtain action name
        $paramsDefault = explode('::', $request->attributes->get('_controller'));
        $paramsBackup = explode(':', $request->attributes->get('_controller'));
        $params =  count($paramsDefault) > 1 ? $paramsDefault : $paramsBackup;

        if (count($params) > 1) {
            $data['action'] = substr($params[1], 0, -6);
            $data['controller'] = $params[1];
        } else {
            throw new \Exception("Couldn't extract action name. Maybe cache should be cleared?");
        }

        return $data;
    }
}