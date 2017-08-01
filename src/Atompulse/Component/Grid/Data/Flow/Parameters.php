<?php

namespace Atompulse\Component\Grid\Data\Flow;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class Parameters
 * @package Atompulse\Component\Grid\Data\Flow
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Parameters implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if ($parameters !== null) {
            return $this->fromArray($parameters);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->normalizeData();
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($this->normalizeData());
    }
}
