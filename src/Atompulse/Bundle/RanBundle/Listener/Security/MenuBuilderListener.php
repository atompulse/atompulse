<?php
namespace Atompulse\RanBundle\Listener\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Atompulse\RanBundle\Service\Security\SecurityAdviser;

/**
 * Class MenuBuilderListener
 * @package Atompulse\RanBundle\Listener\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class MenuBuilderListener
{
    /**
     * @var SecurityAdviser
     */
    protected $securityAdviser;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

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
     * @param SecurityAdviser $securityAdviser
     * @param RouterInterface $router
     * @param LoggerInterface $logger
     * @param $ranSettings
     */
    public function __construct(SecurityAdviser $securityAdviser, RouterInterface $router, LoggerInterface $logger, Session $session, $ranMenu)
    {
        $this->securityAdviser = $securityAdviser;
        $this->router = $router;
        $this->logger = $logger;
        $this->ranMenu = $ranMenu;
        $this->session = $session;
    }

    /**
     * Add available Menu Items on login
     * @param InteractiveLoginEvent $event
     * @throws \Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if (!$this->ranMenu) {
            throw new \Exception("Menu param name is not defined.. impossible to read menu items!");
        }
        // process the available menu items for the user
        $menuData = $this->buildMenuData();

        $this->logger->debug("RAN: Got [" . count($this->ranMenu['data']) . "] menu items.");
        $this->logger->debug("RAN: System Role Access Names [" . json_encode($this->securityAdviser->getSystemPermissions()) . "]");
        $this->logger->debug("RAN: Processed(available) menu items [" . json_encode($menuData) . "]");

        // add this to the user session
        $this->session->set($this->ranMenu['session'], $menuData);
        $this->logger->debug("RAN: Added processed menu items into user session [{$this->ranMenu['session']}]");
    }

    /**
     * Build the menu data
     * @return array
     */
    protected function buildMenuData()
    {
        $menuData = [];

        // process menu items
        foreach ($this->ranMenu['data'] as $menuGroup => $menuGroupData) {
            $menuGroupItems = [];
            // menu group with items
            if (isset($menuGroupData['items']) && count($menuGroupData['items'])) {
                foreach ($menuGroupData['items'] as $itemKey => $itemData) {
                    // route has RAN requirement
                    if (!$this->securityAdviser->userHasOverrideRoles() &&
                        $this->securityAdviser->getRouteRequiredPermissions($itemData['route']) &&
                        $this->securityAdviser->userHasRequiredPermissionsForRoute($itemData['route'])
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
                if (!$this->securityAdviser->userHasOverrideRoles() &&
                    $this->securityAdviser->getRouteRequiredPermissions($menuGroupData['route']) &&
                    $this->securityAdviser->userHasRequiredPermissionsForRoute($menuGroupData['route'])
                ) {
                    $menuData[$menuGroup] = $this->processMenuGroupItem($menuGroupData, $menuGroupItems);
                }
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
