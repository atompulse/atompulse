<?php

namespace Atompulse\Bundle\RanBundle\Traits\Controller;

use Atompulse\Component\Data\DataFinder;

/**
 * Trait RanManagement
 * @package Atompulse\Bundle\RanBundle\Controller\Traits
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait RanManagement
{
    /**
     * Create ran tree for a group/user
     * @param array $arrRoles - existing groups/user roles
     * @param array $readOnly - read only groups/user roles
     * @return array $arrTree configuration for javascript
     */
    protected function createRanTree($arrRoles, $readOnly = [])
    {
        $arrRan = $this->container->getParameter('ran_gui');
        $arrRanTree = $this->container->getParameter('ran_ui_tree');

        $arrTree = [];

        foreach ($arrRanTree as $key => $value) {
            $mainSuperGroup = [
                'title' => $value['label'],
                'ran_all' => false,
                'key' => $key,
                'isFolder' => true,
                'children' => []
            ];
            if (isset($value['items'])) {
                $mainGroup = [];
                $totalMainGroupsEnabled = count($value['items']);
                $countReadOnlyMainGroups = 0;
                foreach ($value['items'] as $ranAll => $arrRans) {
                    $mainGroup = [
                        'title' => $arrRans['label'],
                        'key' => $ranAll,
                        'children' => []
                    ];

                    $path = DataFinder::searchRecursive($ranAll, $arrRan);

                    $treeGroup = [];
                    foreach ($arrRan[$path[0]]['roles'] as $route => $arrSettings) {
                        $isSelected = in_array($arrSettings['role'], $arrRoles) ||
                            in_array($ranAll, $arrRoles);

                        $treeGroup = [
                            'title' => $arrSettings['label'],
                            'key' => $arrSettings['role'],
                            'select' => $isSelected,
                            'unselectable' => ($isSelected && in_array($arrSettings['role'], $readOnly) || in_array($ranAll, $readOnly))
                        ];
                        $mainGroup['children'][] = $treeGroup;
                    }

                    if (in_array($ranAll, $readOnly)) {
                        $countReadOnlyMainGroups++;
                        $mainGroup['unselectable'] = true;
                    }

                    $mainSuperGroup['children'][] = $mainGroup;
                }

                if ($totalMainGroupsEnabled == $countReadOnlyMainGroups) {
                    $mainSuperGroup['unselectable'] = true;
                }
            } else {
                $path = DataFinder::searchRecursive($value['item'], $arrRan);

                $treeGroup = [];
                $mainSuperGroup['ran_all'] = $arrRan[$path[0]]['role'];
                foreach ($arrRan[$path[0]]['roles'] as $route => $arrSettings) {
                    $isSelected = in_array($arrSettings['role'], $arrRoles) ||
                        in_array($value['item'], $arrRoles);

                    $treeGroup = [
                        'title' => $arrSettings['label'],
                        'key' => $arrSettings['role'],
                        'select' => $isSelected,
                        'unselectable' => ($isSelected && in_array($arrSettings['role'], $readOnly) || in_array($value['item'], $readOnly))
                    ];
                    $mainSuperGroup['children'][] = $treeGroup;
                }

                if (in_array($value['item'], $readOnly)) {
                    $mainSuperGroup['unselectable'] = true;
                }
            }

            $arrTree[] = $mainSuperGroup;
        }

        return $arrTree;
    }

    /**
     * Pre process rans before save.
     * @param array $arrRans - raw rans received from request
     * @return array $arrRans final rans for group/user to save
     */
    protected function preProcessRans($arrRawRans)
    {
        $arrRanTree = $this->container->getParameter('ran_ui_tree');

        $arrRans = [];

        // Process data to be saved
        foreach ($arrRawRans as $key => $value) {
            // Case when a main super group is selected => all the subgroups rans should be selected
            if (strpos($value, 'RAN_') === false) {
                if (isset($arrRanTree[$value]['items'])) {
                    foreach ($arrRanTree[$value]['items'] as $role => $arrOptions) {
                        $arrRans[] = $role;
                    }
                } else {
                    $arrRans[] = $arrRanTree[$value]['item'];
                }
            } else {
                $arrRans[] = $value;
            }
        }

        return $arrRans;
    }
}