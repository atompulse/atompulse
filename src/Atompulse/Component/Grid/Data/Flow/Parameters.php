<?php

namespace Atompulse\Component\Grid\Data\Flow;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class Parameters
 * @package Atompulse\Component\Grid\Data\Flow
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property integer page
 * @property integer pageSize
 * @property array filters
 * @property array sorters
 *
 */
class Parameters implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->validProperties = [
            'page' => 'int|null',
            'pageSize' => 'int|null',
            'filters' => 'array|null',
            'sorters' => 'array|null'
        ];

        $this->defaultValues = [
            'page' => 1,
            'pageSize' => 10
        ];

        if ($parameters !== null) {
            return $this->fromArray($parameters);
        }

        return $this;
    }
}
