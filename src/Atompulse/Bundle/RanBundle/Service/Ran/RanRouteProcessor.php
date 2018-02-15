<?php

namespace Atompulse\Bundle\RanBundle\Service\Ran;

use Symfony\Component\Routing\Route;
/**
 * Class RanRouteProcessor
 * @package Atompulse\Bundle\RanBundle\Service\Ran
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class RanRouteProcessor
{
    /**
     * @param string $routeName
     * @param Route $route
     * @return RanItem
     * @throws \Exception
     */
    public static function process(string $routeName, Route $route)
    {
        $ranItem = new RanItem();

        $ranConfig = $route->getOption('ran');

        if (!is_array($ranConfig)) {
            throw new \Exception("Unable to analyze RAN configuration for route [$routeName]");
        }

        // analyze RAN : [group, label, scope, role, granted]
        $routeNameData = explode('_', strtoupper($routeName));

        // the permission will use another RAN for actual permission checking = context is internal (inherited)
        // the permission is declared explicitly = context is action (explicit)
        $context = 'internal'; 

        if (is_numeric(key($ranConfig))) {
            // group mandatory
            $group = $ranConfig[0];
            // label extraction: if label not given then will use the route name starting from the second word and humanize it
            $label = isset($ranConfig[1]) ? $ranConfig[1] : ucfirst(strtolower(implode(' ', array_slice($routeNameData, 1))));
            // context extraction : if no context defined but label defined then use 'action' otherwise use defaultContext
            $context = isset($ranConfig[2]) ? $ranConfig[2] : (isset($ranConfig[1]) ? 'action' : $context);
            // role extraction: if role not explicit then use the exact route name
            $role = isset($ranConfig[3]) ? $ranConfig[3] : $routeName;
            // granted extraction/checking : if no explicit [granted for] found then ignore
            $granted = isset($ranConfig[4]) ? $ranConfig[4] : [];
        } else {
            // group mandatory
            $group = $ranConfig['group'];
            // label extraction: if label not given then will use the route name starting from the second word and humanize the it
            $label = isset($ranConfig['label']) ? $ranConfig['label'] : ucfirst(strtolower(implode(' ', array_slice($routeNameData, 1))));
            // scope extraction : if no scope defined but label defined then use 'action' otherwise use defaultScope
            $context = isset($ranConfig['scope']) ? $ranConfig['scope'] : (isset($ranConfig['label']) ? 'action' : $context);
            // role extraction: if role not explicit then use the exact route name
            $role = isset($ranConfig['role']) ? $ranConfig['role'] : $routeName;
            //  granted extraction/checking : if no explicit [granted for] found then ignore
            $granted = isset($ranConfig['granted']) ? $ranConfig['granted'] : [];
        }

        // final permission name
        $ranItem->name = 'RAN_' . strtoupper($role);
        // final role group access name
        $ranItem->group = 'RAN_' . strtoupper($group) . '_ALL';;

        $ranItem->granted = $granted;

        $ranItem->label = $label;
        $ranItem->context = $context;

        return $ranItem;
    }

}
