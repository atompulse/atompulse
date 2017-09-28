<?php
namespace Atompulse\Bundle\RanBundle\Service\Menu;

use Atompulse\Bundle\RanBundle\Service\Security\SecurityAdviser;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class MenuBuilderService
 * @package Atompulse\Bundle\RanBundle\Service\Menu
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class MenuBuilderService
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $ranMenu;

    /**
     * Item template
     * @var array
     */
    protected $menuItemDataStructure = [
        'route' => null,
        'route_params' => [],
        'url' => null,
        'label' => '?label?',
        'css' => '',
        'image' => '',
        'title' => '',
        'states' => [],
        'extra' => null,
    ];

    /**
     * Group template
     * @var array
     */
    protected $menuGroupDataStructure = [
        'items' => [],
        'route' => null,
        'route_params' => [],
        'url' => null,
        'label' => '?label?',
        'css' => '',
        'image' => '',
        'title' => '',
        'states' => [],
        'extra' => null,
    ];

    /**
     * @param RouterInterface $router
     * @param LoggerInterface $logger
     * @param array $ranMenu
     * @throws \Exception
     */
    public function __construct(RouterInterface $router, LoggerInterface $logger, array $ranMenu = null)
    {
        $this->router = $router;
        $this->logger = $logger;
        $this->ranMenu = $ranMenu;

        if (!$this->ranMenu) {
            throw new \Exception("Menu not defined.. impossible to process menu items!");
        }
    }

    /**
     * Build menu data with authorization checking
     * @param SecurityAdviser $securityAdviser
     * @return array
     */
    public function buildMenuDataWithAuthorizationCheck(SecurityAdviser $securityAdviser)
    {
        $menuData = [];

        // process menu items
        foreach ($this->ranMenu['data'] as $menuGroup => $menuGroupData) {
            $menuGroupItems = [];
            // menu group with items
            if (isset($menuGroupData['items']) && count($menuGroupData['items'])) {
                foreach ($menuGroupData['items'] as $itemKey => $itemData) {
                    // route has RAN requirement
                    if ((!$securityAdviser->userHasOverrideRoles() &&
                            $securityAdviser->getRouteRequiredPermissions($itemData['route']) &&
                            $securityAdviser->userHasRequiredPermissionsForRoute($itemData['route']))
                        || $securityAdviser->userHasOverrideRoles()
                        || !$securityAdviser->getRouteRequiredPermissions($itemData['route'])
                    ) {
                        $menuGroupItems[$itemKey] = $this->processMenuItem($itemData);
                    }
                }
                // add menu group with items
                if (count($menuGroupItems)) {
                    $menuData[$menuGroup] = $this->processMenuGroupItem($menuGroupData, $menuGroupItems);
                }
            } // standalone menu group
            elseif (isset($menuGroupData['route'])) {
                if ((!$securityAdviser->userHasOverrideRoles() &&
                        $securityAdviser->getRouteRequiredPermissions($menuGroupData['route']) &&
                        $securityAdviser->userHasRequiredPermissionsForRoute($menuGroupData['route']))
                    || $securityAdviser->userHasOverrideRoles()
                    || !$securityAdviser->getRouteRequiredPermissions($menuGroupData['route'])
                ) {
                    $menuData[$menuGroup] = $this->processMenuGroupItem($menuGroupData, $menuGroupItems);
                }
            }
        }

        return $menuData;
    }

    /**
     * Build menu data without authorization checking
     * @return array
     */
    public function buildMenuDataWithoutAuthorizationCheck()
    {
        $menuData = [];

        // process menu items
        foreach ($this->ranMenu['data'] as $menuGroup => $menuGroupData) {
            $menuGroupItems = [];
            // menu group with items
            if (isset($menuGroupData['items']) && count($menuGroupData['items'])) {
                foreach ($menuGroupData['items'] as $itemKey => $itemData) {
                    $menuGroupItems[$itemKey] = $this->processMenuItem($itemData);
                }
                // add menu group with items
                if (count($menuGroupItems)) {
                    $menuData[$menuGroup] = $this->processMenuGroupItem($menuGroupData, $menuGroupItems);
                }
            } // standalone menu group
            elseif (isset($menuGroupData['route'])) {
                $menuData[$menuGroup] = $this->processMenuGroupItem($menuGroupData, $menuGroupItems);
            }
        }

        return $menuData;
    }

    /**
     * @param $itemData
     * @return array
     */
    private function processMenuItem($itemData)
    {
        $menuItem = $this->menuItemDataStructure;
        $menuItem = array_merge($menuItem, $itemData);

        if ($itemData['route']) {
            $routeParams = isset($itemData['route_params']) && count($itemData['route_params']) ? $itemData['route_params'] : [];
            $menuItem['url'] = $this->router->generate($itemData['route'], $routeParams, RouterInterface::ABSOLUTE_URL);
        } else {
            $menuItem['url'] = '#';
        }

        return $menuItem;
    }

    /**
     * @param $groupData
     * @param $groupItems
     * @return array
     */
    private function processMenuGroupItem($groupData, $groupItems)
    {
        $menuGroupItem = $this->menuGroupDataStructure;
        $menuGroupItem = array_merge($menuGroupItem, $groupData);

        $menuGroupItem['items'] = $groupItems;

        if ($menuGroupItem['route']) {
            $routeParams = isset($menuGroupItem['route_params']) ? $menuGroupItem['route_params'] : [];
            $menuGroupItem['url'] = $this->router->generate($menuGroupItem['route'], $routeParams, RouterInterface::ABSOLUTE_URL);
        } else {
            $menuGroupItem['url'] = '#';
        }

        return $menuGroupItem;
    }
}
