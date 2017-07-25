<?php
namespace Atompulse\Component\Domain\tests\model;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class CategoryData
 * @package Atompulse\Component\Domain\tests\model
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class CategoryData implements DataContainerInterface
{
    use DataContainer;

    public function __construct(array $data = null)
    {
        $this->validProperties = [
            "id" => "integer|null",
            "name" => "string",
        ];

        return $this->fromArray($data);
    }
}