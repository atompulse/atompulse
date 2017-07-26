<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Data;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionMap
 * @package Atompulse\Bundle\FusionBundle\Assets\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionMap implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->validProperties = [
            'global'        => 'array|null',
            'groups'        => 'array|null',
            'controllers'   => 'array|null',
        ];

        return $this->fromArray($data);
    }

}
