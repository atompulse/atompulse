<?php
namespace Atompulse\Component\Validator\GenericDataList;

use Symfony\Component\Validator\Constraint;

/**
 * Class GenericDataListConstraint
 * @package Atompulse\Component\Validator\GenericDataList
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class GenericDataListConstraint extends Constraint
{
    public $message = '"%value%" is not a valid value';

    /**
     * @var string The parameter in the container from where to extract the data list
     */
    public $parameter = null;

    /**
     * @var string The property from a data list item which is used to extract the valid values
     * @NOTE: Leave this blank to enable direct array value check from data list
     */
    public $key = null;

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return 'GenericDataList';
    }

}
