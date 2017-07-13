<?php
namespace Atompulse\Component\Data\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class GenericDataList
 * @package Atompulse\Component\Data
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
