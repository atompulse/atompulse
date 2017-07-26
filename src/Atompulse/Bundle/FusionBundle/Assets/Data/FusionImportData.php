<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Data;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionImportData
 * @package Atompulse\Bundle\FusionBundle\Assets\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 * @property array groups
 * @property array global
 * @property array includes
 * @property array controllers
 */
class FusionImportData implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->validProperties = [
            'groups' => 'array|null',
            'global' => 'array|null',
            'includes' => 'array|null',
            'controllers' => 'array|null'
        ];

        return $this->fromArray($data);
    }
}
