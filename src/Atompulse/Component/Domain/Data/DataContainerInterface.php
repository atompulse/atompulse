<?php
namespace Atompulse\Component\Domain\Data;

/**
 * Interface DataContainerInterface
 * @package Atompulse\Component\Domain\Data
 *
 * The data values associated with a container consist of one or more properties.
 * Each property has a name and one or more values.
 * A property with more than one value is called an list property and is defined using an array.
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataContainerInterface
{
    /**
     * Define a property on data container
     * @param string $property
     * @param array $constraints
     * @param null $defaultValue
     * @return DataContainerInterface
     */
    public function defineProperty(string $property, array $constraints = [], $defaultValue = null) : DataContainerInterface;

    /**
     * Set a property value
     * @param string $property
     * @param $value
     * @return DataContainerInterface
     */
    public function addPropertyValue(string $property, $value) : DataContainerInterface;

    /**
     * Get the defined list of properties
     * @return array
     */
    public function getProperties() : array;

    /**
     * Get the list of properties names
     * @return array
     */
    public function getPropertiesList() : array;

    /**
     * Check if a property is valid
     * @param string $property
     * @return bool
     */
    public function isValidProperty(string $property) : bool;

    /**
     * Set custom $errorMessage for PropertyNotValidException
     * @param string $errorMessage
     * @return mixed
     */
    public function setPropertyNotValidErrorMessage(string $errorMessage);

    /**
     * Get current data as array
     *
     * Transform data properties into PHP-array structure keeping
     * property items in their respective DataContainerInterface state if the values are objects
     *
     * This method will ONLY return the current state of the data with object and primitives,
     * it will not return default values or property values that have not been set.
     *
     * @see To get a normalized result set use DataContainerInterface::normalizeData
     *
     * @return array
     */
    public function toArray() : array;

    /**
     * Add data from array
     * @param array $data
     * @return DataContainerInterface
     */
    public function fromArray(array $data) : DataContainerInterface;

    /**
     * Normalize all properties of this container:
     * - handles default value resolution
     * - handles DataContainerInterface property values normalization
     * - will return an array with key->value OR multidimensional array with key->array but never object values
     * @return array
     */
    public function normalizeData() : array;
}
