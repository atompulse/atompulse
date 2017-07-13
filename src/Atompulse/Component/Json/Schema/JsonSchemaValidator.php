<?php
namespace Atompulse\Component\Json\Schema;

/**
 * Class SchemaJsonSchemaValidator
 * @package Atompulse\Component\Json\Schema
 *
 * Code inspired from https://github.com/geraintluff/jsv4-php
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class JsonSchemaValidator
{

    const INVALID_TYPE = 0;
    const ENUM_MISMATCH = 1;
    const ANY_OF_MISSING = 10;
    const ONE_OF_MISSING = 11;
    const ONE_OF_MULTIPLE = 12;
    const NOT_PASSED = 13;
    // Numeric errors
    const NUMBER_MULTIPLE_OF = 100;
    const NUMBER_MINIMUM = 101;
    const NUMBER_MINIMUM_EXCLUSIVE = 102;
    const NUMBER_MAXIMUM = 103;
    const NUMBER_MAXIMUM_EXCLUSIVE = 104;
    // String errors
    const STRING_LENGTH_SHORT = 200;
    const STRING_LENGTH_LONG = 201;
    const STRING_PATTERN = 202;
    // Object errors
    const OBJECT_PROPERTIES_MINIMUM = 300;
    const OBJECT_PROPERTIES_MAXIMUM = 301;
    const OBJECT_PROPERTY_MISSING = 302;
    const OBJECT_ADDITIONAL_PROPERTIES = 303;
    const OBJECT_DEPENDENCY_KEY = 304;
    // Array errors
    const ARRAY_LENGTH_SHORT = 400;
    const ARRAY_LENGTH_LONG = 401;
    const ARRAY_UNIQUE = 402;
    const ARRAY_ADDITIONAL_ITEMS = 403;


    protected static $defaultErrorMessages = [
        JsonSchemaValidator::INVALID_TYPE => "Invalid type for '{property}'. Expected {expected} but got {given}",
        JsonSchemaValidator::ENUM_MISMATCH => "'{property}' value {value} is not one of the configured enum options",
        JsonSchemaValidator::NOT_PASSED => "'{property}' value satisfies prohibited schema",
        JsonSchemaValidator::ANY_OF_MISSING => "'{property}' value does not satisfy [any of] the configured options",
        JsonSchemaValidator::ONE_OF_MULTIPLE => "'{property}' value satisfies more than one of the options {successIndex} and {index})",
        JsonSchemaValidator::ONE_OF_MISSING => "'{property}' value must satisfy [one of] the options",

        JsonSchemaValidator::OBJECT_ADDITIONAL_PROPERTIES => "Additional properties not allowed '{property}'",
        JsonSchemaValidator::OBJECT_PROPERTY_MISSING => "Required property '{property}' is missing from {object}",
        JsonSchemaValidator::OBJECT_DEPENDENCY_KEY => "Property '{property}' depends on {depKey}",
        JsonSchemaValidator::OBJECT_PROPERTIES_MINIMUM => "Object must have at least {min} defined properties",
        JsonSchemaValidator::OBJECT_PROPERTIES_MAXIMUM => "Object must have at most {max} defined properties",

        JsonSchemaValidator::ARRAY_ADDITIONAL_ITEMS => "Additional items (index {count} or more) are not allowed in {property}",
        JsonSchemaValidator::ARRAY_LENGTH_SHORT => "Array '{property}' must have at least {minItems} items",
        JsonSchemaValidator::ARRAY_LENGTH_LONG => "Array '{property}' has more than {maxItems} items",
        JsonSchemaValidator::ARRAY_UNIQUE => "Array {property} items must be unique (items {indexA} and {indexB})",

        JsonSchemaValidator::STRING_LENGTH_SHORT => "String '{property}' must be at least {minLength} characters long",
        JsonSchemaValidator::STRING_LENGTH_LONG => "String '{property}' must be at most {maxLength} characters long",
        JsonSchemaValidator::STRING_PATTERN => "String '{property}' does not match pattern: {pattern}",

        JsonSchemaValidator::NUMBER_MULTIPLE_OF => "Number '{property}' must be a multiple of {multiple}, but got {given}",
        JsonSchemaValidator::NUMBER_MINIMUM_EXCLUSIVE => "Number '{property}' must be > {minimum}, but got {given}",
        JsonSchemaValidator::NUMBER_MINIMUM => "Number '{property}' must be >= {minimum}, but got {given}",
        JsonSchemaValidator::NUMBER_MAXIMUM_EXCLUSIVE => "Number '{property}' must be < {maximum}, but got {given}",
        JsonSchemaValidator::NUMBER_MAXIMUM => "Number '{property}' must be <= {maximum}, but got {given}",
    ];

    private $data;
    private $schema;

    private $dataKey;
    private $schemaKey;

    public $validated = false;
    public $valid = true;
    public $errors = [];

    protected $coerce = false;

    /**
     * @param $schema
     * @param $data
     * @param bool|false $coerce
     */
    private function __construct($schema, &$data, $coerce = false)
    {
        $this->data = $data;
        $this->schema = $schema;
        $this->coerce = $coerce;
    }

    public static function createValidator($schema, $data, $coerce = false)
    {
        return new JsonSchemaValidator($schema, $data, $coerce);
    }

    /**
     * @param bool|false $schemaKey
     * @param bool|false $dataKey
     * @return $this
     */
    public function validate($schemaKey = false, $dataKey = false)
    {
        $this->schemaKey = $schemaKey;
        $this->dataKey = $dataKey;

        // subschema validation => only use a subschema to validate data
        if ($schemaKey && !$dataKey && isset($this->schema->properties->{$this->schemaKey})) {
            $this->schema = $this->schema->properties->{$this->schemaKey};
            // if input is a subset but $dataKey not given then address the issue
            if (isset($this->data->{$this->schemaKey})) {
                $this->data = $this->data->{$this->schemaKey};
            }
        }

        $this->checkTypes();
        $this->checkEnum();
        $this->checkObject();
        $this->checkArray();
        $this->checkString();
        $this->checkNumber();
        $this->checkComposite();

        $this->validated = true;

        return $this;
    }

    /**
     * Get validated data.
     * If coercion was used then type conversion + default values will be added
     * @return mixed|null
     */
    public function getData()
    {
        if ($this->validated && $this->valid) {
            return json_decode(json_encode($this->dataKey ? $this->data->{$this->dataKey} : $this->data), true);
        }

        return null;
    }

    static public function pointerJoin($parts)
    {
        $result = "";
        foreach ($parts as $part) {
            $part = str_replace("~", "~0", $part);
            $part = str_replace("/", "~1", $part);
            $result .= "/" . $part;
        }
        return $result;
    }

    static public function recursiveEqual($a, $b)
    {
        if (is_object($a)) {
            if (!is_object($b)) {
                return false;
            }
            foreach ($a as $key => $value) {
                if (!isset($b->$key)) {
                    return false;
                }
                if (!self::recursiveEqual($value, $b->$key)) {
                    return false;
                }
            }
            foreach ($b as $key => $value) {
                if (!isset($a->$key)) {
                    return false;
                }
            }
            return true;
        }
        if (is_array($a)) {
            if (!is_array($b)) {
                return false;
            }
            foreach ($a as $key => $value) {
                if (!isset($b[$key])) {
                    return false;
                }
                if (!self::recursiveEqual($value, $b[$key])) {
                    return false;
                }
            }
            foreach ($b as $key => $value) {
                if (!isset($a[$key])) {
                    return false;
                }
            }
            return true;
        }
        return $a === $b;
    }

    /**
     * Get error keyword
     * @param $errorCode
     * @return string
     */
    private function getErrorCodeKeyword($errorCode)
    {
        switch ($errorCode) {
            case JsonSchemaValidator::OBJECT_PROPERTY_MISSING :
                $codeKeyword = 'missing';
                break;
            case JsonSchemaValidator::ENUM_MISMATCH :
                $codeKeyword = 'enum';
                break;
            case JsonSchemaValidator::NOT_PASSED :
                $codeKeyword = 'not';
                break;
            case JsonSchemaValidator::ANY_OF_MISSING :
                $codeKeyword = 'anyOf';
                break;
            case JsonSchemaValidator::ONE_OF_MULTIPLE :
                $codeKeyword = 'oneOf';
                break;
            case JsonSchemaValidator::ONE_OF_MISSING :
                $codeKeyword = 'oneOf';
                break;
            case JsonSchemaValidator::OBJECT_ADDITIONAL_PROPERTIES :
                $codeKeyword = 'additionalProperties';
                break;
            case JsonSchemaValidator::OBJECT_DEPENDENCY_KEY :
                $codeKeyword = 'dependencies';
                break;
            case JsonSchemaValidator::OBJECT_PROPERTIES_MINIMUM :
                $codeKeyword = 'minProperties';
                break;
            case JsonSchemaValidator::OBJECT_PROPERTIES_MAXIMUM :
                $codeKeyword = 'maxProperties';
                break;
            case JsonSchemaValidator::ARRAY_ADDITIONAL_ITEMS :
                $codeKeyword = 'additionalItems';
                break;
            case JsonSchemaValidator::ARRAY_LENGTH_SHORT :
                $codeKeyword = 'minItems';
                break;
            case JsonSchemaValidator::ARRAY_LENGTH_LONG :
                $codeKeyword = 'maxItems';
                break;
            case JsonSchemaValidator::ARRAY_UNIQUE :
                $codeKeyword = 'uniqueItems';
                break;
            case JsonSchemaValidator::STRING_LENGTH_SHORT :
                $codeKeyword = 'minLength';
                break;
            case JsonSchemaValidator::STRING_LENGTH_LONG :
                $codeKeyword = 'maxLength';
                break;
            case JsonSchemaValidator::STRING_PATTERN :
                $codeKeyword = 'pattern';
                break;
            case JsonSchemaValidator::NUMBER_MULTIPLE_OF :
                $codeKeyword = 'multipleOf';
                break;
            case JsonSchemaValidator::NUMBER_MINIMUM_EXCLUSIVE :
                $codeKeyword = 'exclusiveMinimum';
                break;
            case JsonSchemaValidator::NUMBER_MINIMUM :
                $codeKeyword = 'minimum';
                break;
            case JsonSchemaValidator::NUMBER_MAXIMUM_EXCLUSIVE :
                $codeKeyword = 'exclusiveMaximum';
                break;
            case JsonSchemaValidator::NUMBER_MAXIMUM :
                $codeKeyword = 'maximum';
                break;
            default :
                $codeKeyword = 'type';
        }

        return $codeKeyword;
    }

    /**
     * @param $code
     * @param $schemaPath
     * @param null $subErrors
     * @param array $details
     */
    private function fail($code, $schemaPath, $subErrors = null, $details = [])
    {
        $this->valid = false;

        $message = self::$defaultErrorMessages[$code];

//        print "{$this->dataKey}/{$this->schemaKey}\n";

        switch ($code) {
            case JsonSchemaValidator::OBJECT_PROPERTY_MISSING :
                $schema = $this->schema->properties->$details['{property}'];
                if (isset($schema->requiredMessage) && strlen($schema->requiredMessage) > 0) {
                    $message = str_replace(array_keys($details), $details, $schema->requiredMessage);
                } else {
                    $message = str_replace(array_keys($details), $details, $message);
                }
                break;
            default :
                if (isset($this->schema->invalidMessage) && strlen($this->schema->invalidMessage) > 0) {
                    $message = str_replace(array_keys($details), $details, $this->schema->invalidMessage);
                } else {
                    $message = str_replace(array_keys($details), $details, $message);
                }
        }

        $error = [
            'keyword' => $this->getErrorCodeKeyword($code),
            'data_path' => isset($details['{property}']) ? "/".$details['{property}'] : $this->dataKey,
            'schema_path' => $schemaPath,
            'msg' => $message,
            'context' => [
                'data' => $this->data
            ]
        ];
        $this->errors[] = $error;

        if ($subErrors) {
            foreach ($subErrors as $subError) {
                foreach ($subError->errors as $se) {
                    $se['data_path'] = $error['data_path'].'/'.$se['data_path'];
                    $se['schema_path'] = $error['schema_path'].$se['schema_path'];
                    $this->errors[] = $se;
                }
            }
        }

    }

    /**
     * Recursively validate Depth-In
     * @param $data
     * @param $schema
     * @param bool|false $dataKey
     * @param bool|false $schemaKey
     * @return JsonSchemaValidator
     */
    private function subResult($data, $schema, $dataKey = false, $schemaKey = false)
    {
        $validator = JsonSchemaValidator::createValidator($schema, $data, $this->coerce);

        return $validator->validate($dataKey, $schemaKey);
    }

    private function includeSubResult($subResult, $schemaPrefix)
    {
        if (!$subResult->valid) {
            $this->valid = false;
            foreach ($subResult->errors as $error) {
                $this->errors[] = [
                    'data_path' => str_replace('//', '/', $this->dataKey.'/'.$error['data_path']),
                    'keyword' => $error['keyword'],
                    'schema_path' => $schemaPrefix . $error['schema_path'],
                    'msg' => $error['msg'],
                    'context' => $error['context'],
                ];
            }
        }
    }

    /**
     * Validate types
     */
    private function checkTypes()
    {
//        print "Checking types [$this->dataKey]\n";
        if (isset($this->schema->type)) {
            $types = $this->schema->type;
            if (!is_array($types)) {
                $types = [$types];
            }
            foreach ($types as $type) {
                if ($type == "object" && is_object($this->data)) {
                    return;
                } elseif ($type == "array" && is_array($this->data)) {
                    return;
                } elseif ($type == "string" && is_string($this->data)) {
                    return;
                } elseif ($type == "number" && !is_string($this->data) && is_numeric($this->data)) {
                    return;
                } elseif ($type == "integer" && is_int($this->data)) {
                    return;
                } elseif ($type == "boolean" && is_bool($this->data)) {
                    return;
                } elseif ($type == "null" && $this->data === null) {
                    return;
                }
            }

            // enforce and handle type conversions
            if ($this->coerce) {
                foreach ($types as $type) {
                    switch ($type) {
                        case "number" :
                            if (is_numeric($this->data)) {
                                $this->data = (float)$this->data;
                                return;
                            } else {
                                if (is_bool($this->data)) {
                                    $this->data = $this->data ? 1 : 0;
                                    return;
                                }
                            }
                            break;
                        case "integer" :
                            if ((int)$this->data == $this->data) {
                                $this->data = (int)$this->data;
                                return;
                            }
                            break;
                        case "string" :
                            if (is_numeric($this->data)) {
                                $this->data = "" . $this->data;
                                return;
                            } else {
                                if (is_bool($this->data)) {
                                    $this->data = ($this->data) ? "true" : "false";
                                    return;
                                } else {
                                    if (is_null($this->data)) {
                                        $this->data = "";
                                        return;
                                    }
                                }
                            }
                            break;
                        case "boolean" :
                            if (is_numeric($this->data)) {
                                $this->data = ($this->data != "0");
                                return;
                            } else {
                                if ($this->data == "yes" || $this->data == "true") {
                                    $this->data = true;
                                    return;
                                } else {
                                    if ($this->data == "no" || $this->data == "false") {
                                        $this->data = false;
                                        return;
                                    } else {
                                        if ($this->data == null) {
                                            $this->data = false;
                                            return;
                                        }
                                    }
                                }
                            }
                            break;
                    }
                }
            }

            $type = gettype($this->data);
            if ($type == "double") {
                $type = ((int)$this->data == $this->data) ? "integer" : "number";
            } else {
                if ($type == "NULL") {
                    $type = "null";
                }
            }

            $this->fail(self::INVALID_TYPE, "/type", null, [
                '{given}' => $type,
                '{expected}' => $this->schema->type,
                '{value}' => !is_object($this->data) ? $this->data : 'object',
                '{property}' => $this->schemaKey
            ]);
        }
    }

    /**
     * Validate enum values
     */
    private function checkEnum()
    {
        if (isset($this->schema->enum)) {
            foreach ($this->schema->enum as $option) {
                if (self::recursiveEqual($this->data, $option)) {
                    return;
                }
            }
            $this->fail(self::ENUM_MISMATCH, "/enum", null, ['{property}' => $this->schemaKey, '{value}' => $this->data]);
        }
    }

    /**
     * Validate object
     */
    private function checkObject()
    {
        if (!is_object($this->data)) {
            return;
        }

        if (isset($this->schema->required)) {
            foreach ($this->schema->required as $index => $schemaKey) {
                if (!array_key_exists($schemaKey, (array)$this->data)) {
                    if ($this->coerce && $this->createValueForProperty($schemaKey)) {
                        continue;
                    }
                    $object = is_numeric($this->dataKey) ? "$this->schemaKey/$this->dataKey" : $this->schemaKey;
                    $this->fail(self::OBJECT_PROPERTY_MISSING, "/required/{$index}", null, ['{property}' => $schemaKey, '{object}' => $object]);
                }
            }
        }

        $checkedProperties = [];
        if (isset($this->schema->properties)) {
            foreach ($this->schema->properties as $schemaKey => $subSchema) {
                $checkedProperties[$schemaKey] = true;
                if (array_key_exists($schemaKey, (array)$this->data)) {
                    $subResult = $this->subResult($this->data->$schemaKey, $subSchema, $schemaKey, $schemaKey);
                    $this->includeSubResult($subResult, self::pointerJoin(["properties", $schemaKey]));
                // add default values for properties which are not present in the data
                } elseif ($this->coerce && $this->createValueForProperty($schemaKey)) {
                    continue;
                }
            }
        }
        if (isset($this->schema->patternProperties)) {
            foreach ($this->schema->patternProperties as $pattern => $subSchema) {
                foreach ($this->data as $key => &$subValue) {
                    if (preg_match("/" . str_replace("/", "\\/", $pattern) . "/", $key)) {
                        $checkedProperties[$key] = true;
                        $subResult = $this->subResult($this->data->$key, $subSchema, $key, $key);
                        $this->includeSubResult($subResult, self::pointerJoin(["patternProperties", $pattern]));
                    }
                }
            }
        }
        if (isset($this->schema->additionalProperties)) {
            $additionalProperties = $this->schema->additionalProperties;
            foreach ($this->data as $key => &$subValue) {
                if (isset($checkedProperties[$key])) {
                    continue;
                }
                if (!$additionalProperties) {
                    $this->fail(self::OBJECT_ADDITIONAL_PROPERTIES, "/additionalProperties", null, ['{property}' => $key]);
                } else {
                    if (is_object($additionalProperties)) {
                        $subResult = $this->subResult($subValue, $additionalProperties, $key, $key);
                        $this->includeSubResult($subResult, "/additionalProperties");
                    }
                }
            }
        }
        if (isset($this->schema->dependencies)) {
            foreach ($this->schema->dependencies as $schemaKey => $dep) {
                if (!isset($this->data->$schemaKey)) {
                    continue;
                }
                if (is_object($dep)) {
                    $subResult = $this->subResult($this->data, $dep, $schemaKey, $schemaKey);
                    $this->includeSubResult($subResult, self::pointerJoin(["dependencies", $schemaKey]));
                } else {
                    if (is_array($dep)) {
                        foreach ($dep as $index => $depKey) {
                            if (!isset($this->data->$depKey)) {
                                $this->fail(self::OBJECT_DEPENDENCY_KEY, self::pointerJoin(["dependencies", $schemaKey, $index]), ['schemaKey' => $schemaKey]);
                            }
                        }
                    } else {
                        if (!isset($this->data->$dep)) {
                            $this->fail(self::OBJECT_DEPENDENCY_KEY, self::pointerJoin(["dependencies", $schemaKey]), ['schemaKey' => $schemaKey]);
                        }
                    }
                }
            }
        }
        if (isset($this->schema->minProperties)) {
            if (count(get_object_vars($this->data)) < $this->schema->minProperties) {
                $this->fail(self::OBJECT_PROPERTIES_MINIMUM, "/minProperties");
            }
        }
        if (isset($this->schema->maxProperties)) {
            if (count(get_object_vars($this->data)) > $this->schema->maxProperties) {
                $this->fail(self::OBJECT_PROPERTIES_MAXIMUM, "/minProperties");
            }
        }
    }

    /**
     * Validate array
     */
    private function checkArray()
    {
        if (!is_array($this->data)) {
            return;
        }
        if (isset($this->schema->items)) {
            $items = $this->schema->items;
            if (is_array($items)) {
                foreach ($this->data as $index => &$subData) {
                    if (!is_numeric($index)) {
                        $this->fail(self::ARRAY_ADDITIONAL_ITEMS, "/additionalItems", null, ['{property}' => $this->schemaKey]);
                    }
                    if (isset($items[$index])) {
                        $subResult = $this->subResult($subData, $items[$index], $index, $this->schemaKey);
                        $this->includeSubResult($subResult, "/items/{$index}");
                    } else {
                        if (isset($this->schema->additionalItems)) {
                            $additionalItems = $this->schema->additionalItems;
                            if (!$additionalItems) {
                                $this->fail(self::ARRAY_ADDITIONAL_ITEMS, "/additionalItems", null, ['{property}' => $this->schemaKey]);
                            } else {
                                if ($additionalItems !== true) {
                                    $subResult = $this->subResult($subData, $additionalItems, $index, $this->schemaKey);
                                    $this->includeSubResult($subResult, "/additionalItems");
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($this->data as $index => &$subData) {
                    if (!is_numeric($index)) {
                        $this->fail(self::INVALID_TYPE, "/type", null, [
                                        '{given}' => 'object',
                                        '{expected}' => 'array',
                                        '{value}' => 'object',
                                        '{property}' => $this->schemaKey
                                    ]);
                    }
                    $subResult = $this->subResult($subData, $items, $index, $this->schemaKey);
                    $this->includeSubResult($subResult, "/items");
                }
            }
        }
        if (isset($this->schema->minItems)) {
            if (count($this->data) < $this->schema->minItems) {
                $this->fail(self::ARRAY_LENGTH_SHORT, "/minItems", null, ['{minItems}' => $this->schema->minItems, '{property}' => $this->schemaKey]);
            }
        }
        if (isset($this->schema->maxItems)) {
            if (count($this->data) > $this->schema->maxItems) {
                $this->fail(self::ARRAY_LENGTH_LONG, "/maxItems", null, ['{maxItems}' => $this->schema->maxItems, '{property}' => $this->schemaKey]);
            }
        }
        if (isset($this->schema->uniqueItems)) {
            foreach ($this->data as $indexA => $itemA) {
                foreach ($this->data as $indexB => $itemB) {
                    if ($indexA < $indexB) {
                        if (self::recursiveEqual($itemA, $itemB)) {
                            $this->fail(self::ARRAY_UNIQUE, "/uniqueItems", null, ['{uniqueItems}' => $this->schema->uniqueItems, '$itemA' => $itemA, '$itemB' => $itemB, '{property}' => $this->schemaKey]);
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Validate string
     */
    private function checkString()
    {
        if (!is_string($this->data)) {
            return;
        }
        if (isset($this->schema->minLength)) {
            if (strlen($this->data) < $this->schema->minLength) {
                $this->fail(self::STRING_LENGTH_SHORT, "/minLength", null, ['{minLength}' => $this->schema->minLength, '{property}' => $this->schemaKey]);
            }
        }
        if (isset($this->schema->maxLength)) {
            if (strlen($this->data) > $this->schema->maxLength) {
                $this->fail(self::STRING_LENGTH_LONG, "/maxLength", null, ['{maxLength}' => $this->schema->minLength, '{property}' => $this->schemaKey]);
            }
        }
        if (isset($this->schema->pattern)) {
            $pattern = $this->schema->pattern;
            $patternFlags = isset($this->schema->patternFlags) ? $this->schema->patternFlags : '';
            $result = preg_match("/" . str_replace("/", "\\/", $pattern) . "/" . $patternFlags, $this->data);
            if ($result === 0) {
                $this->fail(self::STRING_PATTERN, "/pattern", null, ['{pattern}' => $this->schema->pattern, '{property}' => $this->schemaKey]);
            }
        }
    }

    /**
     * Validate number
     */
    private function checkNumber()
    {
        if (is_string($this->data) || !is_numeric($this->data)) {
            return;
        }
        if (isset($this->schema->multipleOf)) {
            if (fmod($this->data / $this->schema->multipleOf, 1) != 0) {
                $this->fail(self::NUMBER_MULTIPLE_OF, "/multipleOf", null, ['{property}' => $this->schemaKey, '{multiple}' => $this->schema->multipleOf, '{given}' => $this->data]);
            }
        }
        if (isset($this->schema->minimum)) {
            $minimum = $this->schema->minimum;
            if (isset($this->schema->exclusiveMinimum) && $this->schema->exclusiveMinimum) {
                if ($this->data <= $minimum) {
                    $this->fail(self::NUMBER_MINIMUM_EXCLUSIVE, "", null, ['{property}' => $this->schemaKey, '{minimum}' => $this->schema->exclusiveMinimum, '{given}' => $this->data]);
                }
            } else {
                if ($this->data < $minimum) {
                    $this->fail(self::NUMBER_MINIMUM, "/minimum", null, ['{property}' => $this->schemaKey, '{minimum}' => $this->schema->minimum, '{given}' => $this->data]);
                }
            }
        }
        if (isset($this->schema->maximum)) {
            $maximum = $this->schema->maximum;
            if (isset($this->schema->exclusiveMaximum) && $this->schema->exclusiveMaximum) {
                if ($this->data >= $maximum) {
                    $this->fail(self::NUMBER_MAXIMUM_EXCLUSIVE, "", null, ['{property}' => $this->schemaKey, '{maximum}' => $this->schema->exclusiveMaximum, '{given}' => $this->data]);
                }
            } else {
                if ($this->data > $maximum) {
                    $this->fail(self::NUMBER_MAXIMUM, "/maximum", null, ['{property}' => $this->schemaKey, '{maximum}' => $this->schema->maximum, '{given}' => $this->data]);
                }
            }
        }
    }

    /**
     * Validate composite
     */
    private function checkComposite()
    {
        if (isset($this->schema->allOf)) {
            foreach ($this->schema->allOf as $index => $subSchema) {
                $subResult = $this->subResult($this->data, $subSchema, $index);
                $this->includeSubResult($subResult, "/allOf/" . (int)$index);
            }
        }
        if (isset($this->schema->anyOf)) {
            $failResults = [];
            foreach ($this->schema->anyOf as $index => $subSchema) {
                $subResult = $this->subResult($this->data, $subSchema, $index);
                if ($subResult->valid) {
                    return;
                }
                $failResults[] = $subResult;
            }
            $this->fail(self::ANY_OF_MISSING, "/anyOf", $failResults, ['{property}' => $this->schemaKey]);
        }
        if (isset($this->schema->oneOf)) {
            $failResults = [];
            $successIndex = null;
            foreach ($this->schema->oneOf as $index => $subSchema) {
                $subResult = $this->subResult($this->data, $subSchema, $index);
                if ($subResult->valid) {
                    if ($successIndex === null) {
                        $successIndex = $index;
                    } else {
                        $this->fail(self::ONE_OF_MULTIPLE, "/oneOf", null, ['{property}' => $this->schemaKey]);
                    }
                    continue;
                }
                $failResults[] = $subResult;
            }
            if ($successIndex === null) {
                $this->fail(self::ONE_OF_MISSING, "/oneOf", $failResults, ['{property}' => $this->schemaKey]);
            }
        }
        if (isset($this->schema->not)) {
            $subResult = $this->subResult($this->data, $this->schema);
            if ($subResult->valid) {
                $this->fail(self::NOT_PASSED, "/not", null, ['{property}' => $this->schemaKey]);
            }
        }
    }

    /**
     * Create default value for property
     * @param $schemaKey
     * @return bool
     */
    private function createValueForProperty($schemaKey)
    {
        $schema = null;
        if (isset($this->schema->properties->$schemaKey)) {
            $schema = $this->schema->properties->$schemaKey;
        } else {
            if (isset($this->schema->patternProperties)) {
                foreach ($this->schema->patternProperties as $pattern => $subSchema) {
                    if (preg_match("/" . str_replace("/", "\\/", $pattern) . "/", $schemaKey)) {
                        $schema = $subSchema;
                        break;
                    }
                }
            }
        }
        if (!$schema && isset($this->schema->additionalProperties)) {
            $schema = $this->schema->additionalProperties;
        }
        if ($schema) {
            if (isset($schema->default)) {
                $defaultValue = unserialize(serialize($schema->default));
                if (isset($schema->type)) {
                    $types = is_array($schema->type) ? $schema->type : array($schema->type);
                    if (in_array("null", $types)) {
                        $defaultValue = null;
                    } elseif (in_array("boolean", $types)) {
                        $defaultValue = (boolean)$defaultValue;
                    } elseif (in_array("integer", $types) || in_array("number", $types)) {
                        $defaultValue = (int)$defaultValue;
                    } elseif (in_array("string", $types)) {
                        $defaultValue = (string)$defaultValue;
                    } elseif (in_array("object", $types)) {
                        $defaultValue = new \StdClass;
                    } elseif (in_array("array", $types)) {
                        $defaultValue = [];
                    }
                }
                $this->data->$schemaKey = $defaultValue;
                return true;
            }
//            if (isset($schema->type)) {
//                $types = is_array($schema->type) ? $schema->type : array($schema->type);
//                if (in_array("null", $types)) {
//                    $this->data->$schemaKey = null;
//                } elseif (in_array("boolean", $types)) {
//                    $this->data->$schemaKey = true;
//                } elseif (in_array("integer", $types) || in_array("number", $types)) {
//                    $this->data->$schemaKey = 0;
//                } elseif (in_array("string", $types)) {
//                    $this->data->$schemaKey = "";
//                } elseif (in_array("object", $types)) {
//                    $this->data->$schemaKey = new \StdClass;
//                } elseif (in_array("array", $types)) {
//                    $this->data->$schemaKey = [];
//                } else {
//                    return false;
//                }
//            }
//            return true;
        }
        return false;
    }

}
