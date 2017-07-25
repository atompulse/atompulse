<?php
namespace Atompulse\Component\Domain\tests\model;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;
use Atompulse\Component\Domain\tests\model\CategoryData;

/**
 * Class DomainData
 * @package Atompulse\Component\Domain\tests\model
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property integer|null id
 * @property number code
 * @property string name
 * @property \DateTime|null date_added
 * @property \Atompulse\Component\Domain\tests\model\CategoryData category
 * @property array products
 *
 *
 */
class DomainData implements DataContainerInterface
{
    use DataContainer;

    public function __construct(array $data = null)
    {
        $this->validProperties = [
            "id" => "integer|null",
            "code" => "number",
            "name" => "string",
            "date_added" => "DateTime|null",
            "category" => "Atompulse\\Component\\Domain\\tests\\model\\CategoryData",
            "products" => "array|null",
        ];

        $this->defaultValues = [
            "products" => null,
        ];

        return $this->fromArray($data);
    }

    public function setProducts($products)
    {
        foreach ($products as $product) {
            if (is_array($product)) {
                $this->properties['products'][] = new ProductData($product);
            } elseif ($product instanceof ProductData) {
                $this->properties['products'][] = $product;
            }
        }
    }

    public function getProducts()
    {
        return $this->properties['products'] ?: null;
    }

    public function setDateAdded($value)
    {
        if (!$value instanceof \DateTime) {
            $value = !is_null($value) ? new \DateTime($value) : null;
        }

        $this->properties['date_added'] = $value;
    }

    public function getDateAdded()
    {
        /**
         * @var $dateAdded \DateTime
         */
        $dateAdded = $this->properties['date_added'];

        return $dateAdded instanceof \DateTime ? $dateAdded->format('Y-m-d') : null;
    }
}
