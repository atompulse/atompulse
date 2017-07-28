<?php
namespace Atompulse\Bundle\RanBundle\Service\Security;

use Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


/**
 * Class SecurityAdviser
 * @package Atompulse\Bundle\RanBundle\Service\Security
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class SecurityAdviser
{
    /**
     * @var TokenStorage
     */
    protected $tokenStorage = null;

    /**
     * @var TokenInterface
     */
    protected $token = null;

    /**
     * @var array
     */
    protected $ranSystem = null;
    /**
     * @var array
     */
    protected $ranSecurity = null;

    /**
     * @var array RAN system permissions structure
     */
    protected $systemPermissions = [
        'list' => [],
        'hierarchy' => []
    ];

    /**
     * @var array Authenticated user permissions
     */
    protected $userPermissions = [
        'single' => [],
        'groups' => []
    ];

    /**
     * @param array $ranSystem
     * @param array $ranSecurity
     * @param TokenStorage $tokenStorage
     */
    public function __construct(array $ranSystem, array $ranSecurity, TokenStorage $tokenStorage)
    {
        $this->ranSystem = $ranSystem;
        $this->ranSecurity = $ranSecurity;
        $this->tokenStorage = $tokenStorage;

        $this->processRanSystem();
        $this->checkToken();
    }

    /**
     * Build list with system permissions
     */
    private function processRanSystem()
    {
        $this->systemPermissions['hierarchy'] = $this->ranSystem['hierarchy'];
        foreach ($this->ranSystem['hierarchy'] as $group => $items) {
            $this->systemPermissions['list'] = array_merge($this->systemPermissions['list'], [$group], $items);
        }
    }

    /**
     * Ensure token existence and process user permissions when token is available
     */
    private function checkToken()
    {
        if (!$this->token) {
            $this->token = $this->tokenStorage->getToken();
            $this->processUserPermissions();
        }
    }

    /**
     * Build list with user permissions
     */
    private function processUserPermissions()
    {
        if ($this->token && $this->token->isAuthenticated()) {
            $userRoles = $this->token->getUser()->getRoles();

            foreach ($userRoles as $role) {
                if (isset($this->systemPermissions['hierarchy'][$role])) {
                    $this->userPermissions['groups'][] = $role;
                    $this->userPermissions['single'] = array_merge(
                        $this->userPermissions['single'],
                        [$role],
                        $this->systemPermissions['hierarchy'][$role]
                    );
                } else {
                    if (in_array($role, $this->systemPermissions['list']) || stripos($role, 'ROLE') !== false) {
                        $this->userPermissions['single'] = array_merge($this->userPermissions['single'], [$role]);
                    }
                }
            }
        }
    }

    /**
     * @param bool|true $useHierarchy
     * @return mixed
     */
    public function getSystemPermissions($useHierarchy = true)
    {
        return $useHierarchy ? $this->systemPermissions['hierarchy'] : $this->systemPermissions['list'];
    }

    /**
     * @param bool|false $withGroups
     * @return array
     */
    public function getUserPermissions($withGroups = false)
    {
        $this->checkToken();

        return $withGroups ? $this->userPermissions : $this->userPermissions['single'];
    }

    /**
     * @param $route
     * @return bool
     */
    public function userHasRequiredPermissionsForRoute($route)
    {
        $this->checkToken();

        $requiredPermissions = $this->getRouteRequiredPermissions($route);

        if (!$requiredPermissions) {
            return true;
        }

        return count(array_intersect($this->userPermissions['single'], $requiredPermissions)) > 0;
    }

    /**
     * @param $route
     * @return bool|array
     */
    public function getRouteRequiredPermissions($route)
    {
        if (isset($this->ranSystem['requirements'][$route])) {
            return $this->ranSystem['requirements'][$route];
        }

        return false;
    }

    /**
     * Check if the user has a specific permission
     * @param $permission
     * @return bool
     */
    public function userHasPermission($permission)
    {
        $this->checkToken();

        return in_array($permission, $this->userPermissions['single']);
    }

    /**
     * Check if the user has roles that overrides the permissions checking
     * @return bool
     */
    public function userHasOverrideRoles()
    {
        $this->checkToken();

        // check if the required overridden roles are present in the user roles
        $overriddenRoles = array_intersect($this->userPermissions['single'], $this->ranSecurity['override']);

        // user has the required override roles
        return count($overriddenRoles) > 0;
    }

    /**
     * @return mixed|null
     */
    public function getUser()
    {
        if ($this->isUserAuthenticated()) {
            return $this->token->getUser();
        }

        return null;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public function isUserAuthenticated()
    {
        $this->checkToken();

        return $this->token ? $this->token->isAuthenticated() : false;
    }

    /**
     * Check if a permission is defined in the RAN system
     * @param $permission
     * @return bool
     */
    public function permissionExists($permission)
    {
        return in_array($permission, $this->systemPermissions['list']);
    }

    /**
     * Check if a non existent permission can be ignored
     * @return bool
     */
    public function canIgnoreMissingPermission()
    {
        return $this->ranSecurity['ignore_inexistent_role'];
    }
}
