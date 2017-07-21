<?php
namespace Atompulse\Component\Domain\Data;

/**
 * Interface DataContainerInterface
 * @package Atompulse\Component\Domain\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataContainerInterface
{
    /**
     * Check if a property is valid
     * @param string $property
     * @return bool
     */
    public function isValidProperty(string $property);

    /**
     * Get data as array
     * @return array
     */
    public function toArray();

    /**
     * Add data from array
     * @param array $data
     * @return mixed
     */
    public function fromArray(array $data);

    /**
     * Normalize all properties of this container:
     * - handles default value resolution
     * - handles DataContainerInterface property values normalization
     * - return simple array with key->value OR multidimensional array with key->array but never object values
     * @param string|null $property
     * @return mixed
     */
    public function normalizeData(string $property = null);
}
