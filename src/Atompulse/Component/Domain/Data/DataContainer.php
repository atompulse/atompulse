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
     * @var array Array of valid properties of container and its types
     * @example:
     *   "code" => "integer|0",
     *   "name" => "string",
     *   "date_added" => "\DateTime",
     *   "category" => "\SomeClassImplementingDataContainerInterface",
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
     * @var array Array of data of all valid properties.
     */
    protected $properties = [];

    /**
     * Magical setter method
     * @param string $property
     * @param mixed $value Value of property
     * @throws PropertyNotValidException Rise if field is not defined into validProperties.
     * @throws PropertyValueNotValidException Rise if field value type is inconsistent
     * @return bool
     */
    public function __set($property, $value)
    {
        if (!empty($this->validProperties) && !array_key_exists($property, $this->validProperties)) {
            throw new PropertyNotValidException("Property [$property] not valid for this model [".__CLASS__."]");
        }

        // check type and do assignment
        if ($this->checkTypes($property, $value)) {
            // Check if there's a specialized setter method
            $setterMethod = "set".Transform::camelize($property);
            if (method_exists($this, $setterMethod)) {
                return $this->$setterMethod($value);
            } else {
                $this->properties[$property] = $value;
            }
        }

        return true;
    }

    /**
     * Getter magical method
     * @param string $property
     * @return mixed
     * @throws PropertyNotValidException
     */
    public function &__get($property)
    {
        if (!$this->isValidProperty($property)) {
            throw new PropertyNotValidException("Property [$property] does not exists in this model [".__CLASS__."]");
        }
        // Check if there's a specialized getter method
        $getterMethod = "get".Transform::camelize($property);
        if (method_exists($this, $getterMethod)) {
            return $this->$getterMethod();
        }
        if (!array_key_exists($property, $this->properties)) {
            $this->properties[$property] = array_key_exists($property, $this->defaultValues) ? $this->defaultValues[$property] : null;
        }

        return $this->properties[$property];
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
     * Transform data properties into PHP-array structure keeping
     * property items in their respective DataContainerInterface state if the values are objects
     *
     * This method will ONLY return the current state of the data with object and primitives,
     * it will not return default values or property values that has not been set.
     *
     * @see To get a normalize result set use ->normalizeData method
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
     * @throws PropertyValueNotValidException
     */
    public function fromArray(array $data, bool $skipInvalidProperties = true)
    {
        foreach ($data as $property => $value) {
            if ($skipInvalidProperties && !$this->isValidProperty($property)) {
                continue;
            } else {
                if (is_object($value)) {
                    if (get_class($value) === "DateTime") {
                        $this->$property = $value->format("Y-m-d");
                    } else {
                        throw new PropertyValueNotValidException("Input type error: Property [$property] accepts only [".implode(',', $this->getDefinedTypes($property)).'], but given value is: [' . get_class($value).']');
                    }
                } else {
                    $this->$property = $value;
                }
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
     * Check types
     * @param string $property
     * @param $value
     * @return bool
     * @throws PropertyValueNotValidException
     */
    private function checkTypes(string $property, $value)
    {
        $requiredTypes = $this->getDefinedTypes($property);

        $actualType = gettype($value);

        if ($actualType == 'double') {
            $actualType = is_int($value) ? 'integer' : 'number';
        } else {
            if ($actualType == 'NULL') {
                $actualType = 'null';
            }
        }

        if (count($requiredTypes)) {
            $isValidType = true;
            foreach ($requiredTypes as $type) {
                if ($type === 'object' && !is_object($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'array' && !is_array($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'string' && !is_string($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'number' && !is_numeric($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'integer' && !is_int($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'boolean' && !is_bool($value)) {
                    $isValidType = false;
                    break;
                } elseif ($type == 'null' && !$value === null) {
                    $isValidType = false;
                    break;
                }
            }

            if ($isValidType) {
                return true;
            }

            if ($actualType == 'object') {
                if (!in_array(get_class($value), $requiredTypes)) {
                    throw new PropertyValueNotValidException("Type error: Property [$property] accepts only [".implode(',', $requiredTypes).'], but given value is: [' . get_class($value).']');
                }
            } else {
                if (!in_array($actualType, $requiredTypes)) {
                    throw new PropertyValueNotValidException("Type error: Property [$property] accepts only [".implode(',', $requiredTypes)."], but given value is: [$actualType]");
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
