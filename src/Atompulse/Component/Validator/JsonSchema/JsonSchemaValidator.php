<?php
namespace Atompulse\Component\Validator\JsonSchema;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Atompulse\Component\Json\Schema;
use League\JsonGuard;

/**
 * Class JsonSchemaValidator
 * @package Atompulse\Component\Validator\JsonSchema
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class JsonSchemaValidator extends ConstraintValidator
{
    use ContainerAwareTrait;

    /**
     * Validate the data against a json schema
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $metaSchema = $this->prepareSchema($constraint->schema, $constraint->dependencies);

        $dereferencer  = new JsonGuard\Dereferencer();
        $schema = $dereferencer->dereference(json_decode($metaSchema));

        $validator = Schema\JsonSchemaValidator::createValidator($schema, json_decode(json_encode($value)), true);
        $validator->validate($this->getDefinitionName($constraint->schema));

        if (!$validator->valid) {
            foreach ($validator->errors as $error) {
                $this->context->buildViolation($error['msg'])->atPath($this->formatJsonPath($error['data_path']))->addViolation();
            }
        } else {
            $constraint->addCoercedData($validator->getData());
        }
    }

    /**
     * @param $jsonDataPath
     * @return mixed
     */
    protected function formatJsonPath($jsonDataPath)
    {
        $jsonDataPath = substr($jsonDataPath, 1);

        return $jsonDataPath;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    protected function getDefinitionName($parameter)
    {
        /**
         * Given 'diam.json_schema.tender' the schema definition will be consider
         * the last part of the name => 'tender'
         * This is important because it will be used when building the metaschema
         * and also is used when referencing inside schemas such as: $ref: '#/definitions/xxxxx'
         */
        return explode('.', $parameter)[2];
    }

    /**
     * @param $schema
     * @param array $dependencies
     * @return array|null|string
     * @throws \Exception
     */
    protected function prepareSchema($schema, $dependencies = [])
    {
        $apc = $this->container->get('orkestra.apc');
        $metaSchema = null;

        if ($apc->exist($schema) && !$this->container->get('kernel')->getEnvironment() == 'dev') {
            $metaSchema = $apc->get($schema);
        } else {
            $schemas = array_merge([$schema], $dependencies);
            $schemaCollection = new Schema\JsonSchemaCollection();

            // build metaschema
            foreach ($schemas as $schemaParameterName) {
                if (!$arrSchema = $this->container->getParameter($schemaParameterName)) {
                    throw new \Exception("Parameter [$schemaParameterName] was not found in container");
                }
                $schemaCollection->addSchema($this->getDefinitionName($schemaParameterName), $arrSchema);
            }

            $metaSchema = $schemaCollection->getMetaSchema(true);
            // store to apc
            $apc->set($schema, $metaSchema);
        }

        return $metaSchema;
    }


}
