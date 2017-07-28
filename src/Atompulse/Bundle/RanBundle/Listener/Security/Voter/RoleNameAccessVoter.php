<?php
namespace Atompulse\Bundle\RanBundle\Listener\Security\Voter;

use Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

use Atompulse\Bundle\RanBundle\Exception\InexistentRoleException;
use Atompulse\Bundle\RanBundle\Service\Security\SecurityAdviser;

/**
 * Class RoleNameAccessVoter
 * @package Atompulse\Bundle\RanBundle\Listener\Security\Voter
 *
 * This is the heart of the RAN Authorization System :
 * - authorizes access to URL's by checking the accessed route's requirements vs user roles
 * - checks authorization to specific permission in templates or other context when using is_granted/isGranted
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class RoleNameAccessVoter implements VoterInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @var SecurityAdviser
     */
    protected $securityAdviser = null;

    /**
     * @param SecurityAdviser $securityAdviser
     * @param LoggerInterface $logger
     */
    public function __construct(SecurityAdviser $securityAdviser, LoggerInterface $logger)
    {
        $this->securityAdviser = $securityAdviser;
        $this->logger = $logger;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        // don't check against a user attribute, so return true
        return true;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        // voter supports all type of token classes, so return true
        return true;
    }

    /**
     * Role Access Name Vote
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param mixed $object
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // override to whole system since the user has the required roles to override the system
        if ($this->securityAdviser->userHasOverrideRoles()) {
            $this->logger->alert("Security system overridden, user has correct override roles");
            return VoterInterface::ACCESS_GRANTED;
        }

        // explicit roles checking with is_granted
        if (is_null($object)) {
            return $this->decideAccessForExplicitRoles($attributes);
        }

        // action (route) access checking
        if ($object instanceof Request) {
            return $this->decideAccessForAction($object);
        }

        // could not determine what are we voting for
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * Permission decision
     * @param $checkedRoles
     * @return int
     */
    protected function decideAccessForExplicitRoles($checkedRoles)
    {
        $nrRolesToCheck = count($checkedRoles);
        $nrRolesOwned = 0;

        foreach ($checkedRoles as $permission) {
            if (!$this->securityAdviser->permissionExists($permission)) {
                if (!$this->securityAdviser->canIgnoreMissingPermission()) {
                    throw new InexistentRoleException("Permission [$permission] does not exist");
                } else {
                    // since the role does not exist in the ran system and the this issue is ignored we abstain
                    $this->logger->alert("[$permission] permission is not defined in the ran system. Voter will abstain!");
                    return VoterInterface::ACCESS_ABSTAIN;
                }
            }
            // check if the user has the explicit permission
            if ($this->securityAdviser->userHasPermission($permission)) {
                $nrRolesOwned++;
            }
        }

        return ($nrRolesToCheck == $nrRolesOwned) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    /**
     * Action decision
     * @param Request $request
     * @return int
     */
    protected function decideAccessForAction(Request $request)
    {
        $routeName = $request->get('_route');

        $requiredPermissions = $this->securityAdviser->getRouteRequiredPermissions($routeName);

        if (!$requiredPermissions) {
            // since the route does not have a requirement we abstain
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($this->securityAdviser->userHasRequiredPermissionsForRoute($routeName)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $msg = "[$routeName] action requires one of [" . implode(',', $requiredPermissions) . "] permissions but current user has none of these";
        $this->logger->alert($msg);

        return VoterInterface::ACCESS_DENIED;
    }
}