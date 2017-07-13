<?php
namespace Atompulse\Component\Json\Schema;

/**
 * Class JsonSchemaCollection
 * @package Atompulse\Component\Json\Schema
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class JsonSchemaCollection
{
    /**
     * @var array
     */
    protected $metaSchemaStructure = [
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'description' => 'Meta Schema',
        'definitions' => [],
        'type' => 'object',
        'properties' => [],
        'additionalProperties' => false, // should not allow non-defined structures in meta schema
    ];

    /**
     * @param string $schema
     * @param string $description
     */
    public function __construct($schema = 'http://json-schema.org/draft-04/schema#', $description = 'Meta Schema')
    {
        $this->metaSchemaStructure['$schema'] = $schema;
        $this->metaSchemaStructure['description'] = $description;
    }

    /**
     * Add schema to collection
     * @param $definitionName
     * @param $schema
     */
    public function addSchema($definitionName, $schema)
    {
        $this->metaSchemaStructure['definitions'][$definitionName] = $schema;
        $this->metaSchemaStructure['properties'][$definitionName] = [
            '$ref' => "#/definitions/$definitionName"
        ];
    }

    /**
     * @param $definitionName
     * @return mixed
     */
    public function getSchema($definitionName)
    {
        return $this->metaSchemaStructure['definitions'][$definitionName];
    }

    /**
     * Get meta schema
     * @param bool|false $asJson
     * @return array|string
     */
    public function getMetaSchema($asJson = false)
    {
        return $asJson ? json_encode($this->metaSchemaStructure) : $this->metaSchemaStructure;
    }
}
