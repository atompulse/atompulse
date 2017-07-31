<?php
namespace Atompulse\Bundle\RanBundle\Listener\Menu;

use Atompulse\Bundle\RanBundle\Service\Menu\MenuBuilderService;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class NonSecuredMenuBuilderListener
 * @package Atompulse\Bundle\RanBundle\Listener\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class NonSecuredMenuBuilderListener
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var MenuBuilderService
     */
    protected $menuBuilder = null;

    /**
     * @var array
     */
    protected $ranMenuSettings = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param MenuBuilderService $menuBuilder
     */
    public function __construct(MenuBuilderService $menuBuilder, Session $session, array $ranMenuSettings, LoggerInterface $logger)
    {
        $this->menuBuilder = $menuBuilder;
        $this->session = $session;
        $this->ranMenuSettings = $ranMenuSettings;
        $this->logger = $logger;
    }

    /**
     * Add available Menu Items on controller selection
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        // process the available menu items for the user
        $menuData = $this->menuBuilder->buildMenuDataWithoutAuthorizationCheck();

        $this->logger->debug("RAN: Got [" . count($this->ranMenuSettings['data']) . "] menu items.");
        $this->logger->debug("RAN: Processed(available) menu items [" . json_encode($menuData) . "]");

        // add this to the user session
        $this->session->set($this->ranMenuSettings['session'], $menuData);
        $this->logger->debug("RAN: Added processed menu items into user session [{$this->ranMenuSettings['session']}]");
    }

}
