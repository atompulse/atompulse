<?php
namespace Atompulse\Component\Validator\JsonSchema;

use Symfony\Component\Validator\Constraint;

/**
 * Class JsonSchemaConstraint
 * @package Atompulse\Component\Validator\JsonSchema
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class JsonSchemaConstraint extends Constraint
{
    protected $coercedData = null;

    public $message = '"%value%" is not a valid value';

    /**
     * @var string The parameter in the container from where to extract the json schema
     */
    public $schema = null;
    /**
     * @var string Other schemas on which the used schema depends
     */
    public $dependencies = [];

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return 'JsonSchemaValidator';
    }

    /**
     * @param $data
     */
    public function addCoercedData($data)
    {
        $this->coercedData = $data;
    }

    /**
     * @return null
     */
    public function getCoercedData()
    {
        return $this->coercedData;
    }

}
