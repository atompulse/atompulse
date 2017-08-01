<?php
namespace Atompulse\Bundle\RanBundle\Listener\Menu;

use Atompulse\Bundle\RanBundle\Service\Menu\MenuBuilderService;
use Atompulse\Bundle\RanBundle\Service\Security\SecurityAdviser;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SecuredMenuBuilderListener
 * @package Atompulse\Bundle\RanBundle\Listener\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class SecuredMenuBuilderListener
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
     * @var SecurityAdviser
     */
    protected $securityAdviser;

    /**
     * @param MenuBuilderService $menuBuilder
     */
    public function __construct(MenuBuilderService $menuBuilder, Session $session, array $ranMenuSettings, LoggerInterface $logger, SecurityAdviser $securityAdviser)
    {
        $this->menuBuilder = $menuBuilder;
        $this->session = $session;
        $this->ranMenuSettings = $ranMenuSettings;
        $this->logger = $logger;
        $this->securityAdviser = $securityAdviser;
    }

    /**
     * Add available Menu Items on login
     * @throws \Exception
     */
    public function onSecurityInteractiveLogin()
    {
        // process the available menu items for the user
        $menuData = $this->menuBuilder->buildMenuDataWithAuthorizationCheck($this->securityAdviser);

        $this->logger->debug("RAN: Got [" . count($this->ranMenuSettings['data']) . "] menu items.");
        $this->logger->debug("RAN: System Role Access Names [" . json_encode($this->securityAdviser->getSystemPermissions()) . "]");
        $this->logger->debug("RAN: Processed(available) menu items [" . json_encode($menuData) . "]");

        // add this to the user session
        $this->session->set($this->ranMenuSettings['session'], $menuData);
        $this->logger->debug("RAN: Added processed menu items into user session [{$this->ranMenuSettings['session']}]");
    }

}
