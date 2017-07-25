<?php
namespace Atompulse\Component\Domain\tests\model;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class ProductData
 * @package Atompulse\Component\Domain\tests\model
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class ProductData implements DataContainerInterface
{
    use DataContainer;

    public function __construct(array $data = null)
    {
        $this->validProperties = [
            "id" => "integer|null",
            "brand" => "string|null",
            "name" => "string",
            "price" => "number",
        ];

        return $this->fromArray($data);
    }

    public function setId($value)
    {
        $this->properties['id'] = is_numeric($value) ? (integer)($value) : null;
    }
}
