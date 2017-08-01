<?php
namespace Atompulse\Component\Domain\Data;

use Atompulse\Component\Domain\Data\Exception\PropertyNotValidException;
use Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException;
use Atompulse\Component\Domain\Data\Exception\PropertyValueNormalizationException;

use Atompulse\Component\Data\Transform;

/**
 * Trait DataContainer
 * @package Atompulse\Component\Domain\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait DataContainer
{
    /**
     * @var array Array to describe the valid properties of container and its types
     * @example:
     *   "code" => "integer|0",
     *   "name" => "string",
     *   "date_added" => "DateTime",
     *   "category" => "FullyQualifiedClassImplementingDataContainerInterface",
     *   "price" => "double",
     *   "products" => "array|null",
     */
    protected $validProperties = [];

    /**
     * Default values specification
     * @var array
     */
    protected $defaultValues = [];

    /**
     * @var array Array to store data of all valid properties.
     */
    protected $properties = [];

    /**
     * @inheritdoc
     * @param string $property
     * @param mixed $value Value of property
     * @throws PropertyNotValidException Thrown if property is not defined into validProperties.
     * @throws PropertyValueNotValidException Thrown if property value type is inconsistent with declaration
     * @return void
     */
    public function __set($property, $value)
    {
        if (!empty($this->validProperties) && !array_key_exists($property, $this->validProperties)) {
            throw new PropertyNotValidException("Property [$property] not valid for this model [".__CLASS__."]");
        }

        // Check if there's a specialized setter method
        $setterMethod = "set".Transform::camelize($property);
        if (method_exists($this, $setterMethod)) {
            $this->$setterMethod($value);
        } else {
            $this->properties[$property] = $value;
        }

        // perform property value type checking
        // performed last in order to be able to test the validity
        // of property values that had been set using a custom setter
        $this->checkTypes($property, $this->properties[$property]);
    }

    /**
     * @inheritdoc
     * @param string $property
     * @return mixed
     * @throws PropertyNotValidException
     */
    public function &__get($property)
    {
        if (!$this->isValidProperty($property)) {
            throw new PropertyNotValidException("Property [$property] does not exists in this model [".__CLASS__."]");
        }

        $propertyValue = null;

        // specialized getter method
        $getterMethod = "get".Transform::camelize($property);
        if (method_exists($this, $getterMethod)) {
            $propertyValue = $this->$getterMethod();
        } else {
            // default value when property was not set
            if (!array_key_exists($property, $this->properties) && array_key_exists($property, $this->defaultValues)) {
                $propertyValue = $this->defaultValues[$property];
            } elseif (isset($this->properties[$property])) {
                $propertyValue = $this->properties[$property];
            }
        }

        return $propertyValue;
    }

    /**
     * @inheritdoc
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @inheritdoc
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->properties[$name]);
    }

    /**
     * Check if a property is valid
     * @param string $property
     * @return bool
     */
    public function isValidProperty(string $property)
    {
        if (!empty($this->validProperties) && !array_key_exists($property, $this->validProperties)) {
            return false;
        }

        return true;
    }

    /**
     * Set a property value
     * @info This is a shortcut method to allow $this->properties[$property] = $value / $this->properties[$property][] = $value;
     * @param string $property
     * @param mixed $value
     * @throws PropertyNotValidException
     */
    public function addPropertyValue(string $property, $value)
    {
        if ($this->isValidProperty($property)) {
            $requiredTypes = $this->getDefinedTypes($property);
            // add to array property type
            if (in_array('array', $requiredTypes) && !is_array($value)) {
                $this->properties[$property][] = $value;
            } else {
                $this->properties[$property] = $value;
            }
        } else {
            throw new PropertyNotValidException("Property [$property] does not exists in this model [".__CLASS__."]");
        }
    }

    /**
     * Transform data properties into PHP-array structure keeping
     * property items in their respective DataContainerInterface state if the values are objects
     *
     * This method will ONLY return the current state of the data with object and primitives,
     * it will not return default values or property values that have not been set.
     *
     * @see To get a normalized result set use ::normalizeData method
     *
     * @param string|null $property
     * @return array [key => value] Data structure
     * @throws PropertyNotValidException
     * @throws PropertyValueNotValidException
     */
    public function toArray(string $property = null)
    {
        $properties = $this->properties;

        if (!is_null($property)) {
            if ($this->isValidProperty($property)) {
                if (is_array($this->properties[$property]) ||
                    $this->properties[$property] instanceof DataContainerInterface ||
                    method_exists($this->properties[$property], 'toArray')) {
                    $properties = $this->properties[$property];
                } else {
                    throw new PropertyValueNotValidException(
                            "Property type error: property [$property]
                            does not implement DataContainerInterface OR does not have a toArray method OR is not an array"
                        );
                }
            } else {
                throw new PropertyNotValidException("Property [$property] does not exists in this model [".__CLASS__."]");
            }
        }

        return array_map(
            function ($item) {
                if (is_object($item)) {
                    // default object->primitive type conversion for DateTime objects
                    if (get_class($item) === "DateTime") {
                        return $item->format("Y-m-d");
                    }
                    if ($item instanceof DataContainerInterface || method_exists($item, 'toArray')) {
                        return $item->toArray();
                    } else {
                        throw new PropertyValueNotValidException(
                            "Value type error: class [" . get_class($item) . "]
                            does not implement DataContainerInterface nor have a toArray method"
                        );
                    }
                } elseif (is_array($item)) {
                    return array_map(
                        function ($subItem) {
                            if ($subItem instanceof DataContainerInterface || method_exists($subItem, 'toArray')) {
                                return $subItem->toArray();
                            }
                            return $subItem;
                        },
                        $item
                    );
                }
                return $item;
            },
            $properties
        );
    }

    /**
     * Add data from array
     * @param array $data
     * @param bool|true $skipInvalidProperties
     * @return $this
     */
    public function fromArray(array $data, bool $skipInvalidProperties = true)
    {
        foreach ($data as $property => $value) {
            if ($skipInvalidProperties && !$this->isValidProperty($property)) {
                continue;
            } else {
                $this->$property = $value;
            }
        }

        return $this;
    }

    /**
     * Normalize all properties of this container:
     * - handles default value resolution
     * - handles DataContainerInterface property values normalization
     * - return simple array with key->value OR multidimensional array with key->array but never object values
     * @param string|null $property Normalize a specific property of the container
     * @return array|mixed
     * @throws PropertyNotValidException
     * @throws PropertyValueNormalizationException
     */
    public function normalizeData(string $property = null)
    {
        $data = [];

        $validProperties = $this->validProperties;

        if (!is_null($property)) {
            if ($this->isValidProperty($property)) {
                return $this->normalizeValue($this->$property);
            } else {
                throw new PropertyNotValidException("Property [$property] does not exists in this model [".__CLASS__."]");
            }
        }

        foreach ($validProperties as $validProperty => $types) {
            $data[$validProperty] = $this->normalizeValue($this->$validProperty);
        }

        return $data;
    }

    /**
     * Normalize a property value
     * @param $value
     * @return mixed
     * @throws PropertyValueNormalizationException
     */
    protected function normalizeValue($value)
    {
        $normalizedValue = $value;
        // object value
        if (is_object($value)) {
            if (get_class($value) === "DateTime") {
                $normalizedValue = $value->format("Y-m-d");
            } elseif ($value instanceof DataContainerInterface || method_exists($value, 'normalizeData')) {
                $normalizedValue = $value->normalizeData();
            } else {
                throw new PropertyValueNormalizationException(
                    "Value error: object of type [" . get_class($value) . "]
                    does not implement DataContainerInterface nor have a [normalizeData] method"
                );
            }
        } elseif (is_array($value)) {
            foreach ($value as $arrProperty => $arrValue) {
                $normalizedValue[$arrProperty] = $this->normalizeValue($arrValue);
            }
        }

        return $normalizedValue;
    }

    /**
     * Check property value type
     * @param string $property
     * @param mixed $value
     * @return bool
     * @throws PropertyValueNotValidException
     */
    private function checkTypes(string $property, $value)
    {
        $requiredTypes = $this->getDefinedTypes($property);

        $actualValueType = gettype($value);

        if ($actualValueType == 'double') {
            $actualValueType = is_int($value) ? 'integer' : 'number';
        } else {
            if ($actualValueType == 'NULL') {
                $actualValueType = 'null';
            }
        }

        if (count($requiredTypes)) {
            // object check
            if ($actualValueType == 'object') {
                if (!in_array(get_class($value), $requiredTypes)) {
                    throw new PropertyValueNotValidException("Type error: Property [$property] accepts only [".implode(',', $requiredTypes).'], but given value is instance of : [' . get_class($value).']');
                }
            } else {
                // primitive type value
                $isValidType = false;
                foreach ($requiredTypes as $type) {
                    if ($type === 'object' && is_object($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'array' && is_array($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'string' && is_string($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'number' && is_numeric($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'integer' && is_int($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'boolean' && is_bool($value)) {
                        $isValidType = true;
                        break;
                    } elseif ($type == 'null' && $value === null) {
                        $isValidType = true;
                        break;
                    }
                }
                if (!$isValidType) {
                    throw new PropertyValueNotValidException("Type error: Property [$property] accepts only [".implode(',', $requiredTypes)."], but given value is: [$actualValueType]");
                }
            }
        }

        return true;
    }

    /**
     * Get defined types for a property
     * @param string $property
     * @return array
     */
    private function getDefinedTypes(string $property)
    {
        $typeSpecification = $this->validProperties[$property];

        $definedTypes = [];
        if (strpos($typeSpecification, '|') !== false) {
            $definedTypes = explode('|', $typeSpecification);
        } elseif (!empty($typeSpecification)) {
            $definedTypes = [$typeSpecification];
        }

        return $definedTypes;
    }

}
