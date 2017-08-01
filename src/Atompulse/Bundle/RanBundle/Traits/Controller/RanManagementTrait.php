<?php

namespace Atompulse\Bundle\RanBundle\Traits\Controller;

/**
 * Trait RanManagement
 * @package Atompulse\Bundle\RanBundle\Controller\Traits
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait RanManagementTrait
{
    /**
     * Create ran UI tree to be used with client side tree component
     * @param array $arrRoles Existing groups/user roles
     * @param array $readOnly Read only groups/user roles
     * @return array
     * @throws \Exception
     */
    protected function createRanTree()
    {
        // generated role_access_names_gui.yml
        $ranGui = $this->container->getParameter('ran_gui');
        // handcrafted groups tree (ui_tree.yml)
        $ranUiTree = $this->container->getParameter('ran_ui_tree');

        $preparedUiTree = [];

        foreach ($ranUiTree as $collection => $collectionSettings) {
            // collection item structure
            $collectionItem = [
                'title' => $collectionSettings['label'],
                'ran_all' => false,
                'isFolder' => true,
                'children' => []
            ];
            // check for groups
            if (isset($collectionSettings['groups']) && count($collectionSettings['groups']) > 0) {
                foreach ($collectionSettings['groups'] as $permissionGroup => $groupSettings) {
                    // add group item
                    $collectionItem['children'][] = $this->buildGroupItem($permissionGroup, $groupSettings, $ranGui);
                }
            } // check group
            elseif (isset($collectionSettings['group'])) {
                $ranUiGroupData = $ranGui[$collectionSettings['group']];
                $collectionItem['ran_all'] = $ranUiGroupData['role'];
                // check for individual permissions in this group
                if (count($ranUiGroupData['roles'])) {
                    foreach ($ranUiGroupData['roles'] as $route => $permissionSettings) {
                        $collectionItem['children'][] = $this->buildPermissionItem($permissionSettings['label'], $permissionSettings['role'], $route);
                    }
                } else {
                    // item is standalone since it doesnt have (children)roles
                    $collectionItem['isFolder'] = false;
                }
            } else {
                throw new \Exception('A collection must have exactly 1 [group] OR at least 1 child in [groups]');
            }

            // add collection item
            $preparedUiTree[] = $collectionItem;
        }

        return $preparedUiTree;
    }

    /**
     * @param $permissionGroup
     * @param $groupSettings
     * @param $ranGui
     * @return array
     */
    protected function buildGroupItem($permissionGroup, $groupSettings, $ranGui)
    {
        $ranUiGroupData = $ranGui[$permissionGroup];

        $groupItem = [
            'title' => $groupSettings['label'],
            'ran_all' => $ranUiGroupData['role'],
            'isFolder' => true,
            'children' => []
        ];

        // check for individual permissions in this group
        if (count($ranUiGroupData['roles'])) {
            foreach ($ranUiGroupData['roles'] as $route => $permissionSettings) {
                $groupItem['children'][] = $this->buildPermissionItem($permissionSettings['label'], $permissionSettings['role'], $route);
            }
        } else {
            $groupItem['isFolder'] = false;
        }

        return $groupItem;
    }

    /**
     * @param $label
     * @param $permission
     * @param $route
     * @return array
     */
    private function buildPermissionItem($label, $permission, $route)
    {
        $permissionItem = [
            'title' => $label,
            'ran' => $permission,
            'route' => $route
        ];

        return $permissionItem;
    }
}
