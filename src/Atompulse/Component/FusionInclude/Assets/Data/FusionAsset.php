<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Data;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionImportData
 * @package Atompulse\Bundle\FusionBundle\Assets\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property string name
 * @property string namespace
 * @property array files
 * @property string group
 * @property string order
 */
class FusionAsset implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->validProperties = [
            'name'  => 'string',
            'namespace'  => 'string|null',
            'group' => 'string|null',
            'order' => 'int|null',
            'files' => 'array',
        ];

        return $this->fromArray($data);
    }

    /**
     * @param $value
     * @throws \Exception
     */
    public function setGroup(string $value = null)
    {
        $value = is_null($value) ?: 'global';

        $this->addPropertyValue('group', $value);
    }

    /**
     * @param $value
     * @throws \Exception
     */
    public function setOrder(int $value = null)
    {
        $value = is_null($value) ?: 0;

        $this->addPropertyValue('order', $value);
    }
}
