<?php
namespace Atompulse\Bundle\RanBundle\Exception;

/**
 * Class InexistentRoleException
 * Exception thrown when a role doesnt exist in the Ran System
 * @package Atompulse\Bundle\RanBundle\Exception
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class InexistentRoleException extends AuthorizationException
{
    /**
     * @inheritdoc
     */
    public function getMessageKey()
    {
        return 'Role does not exists';
    }
}
