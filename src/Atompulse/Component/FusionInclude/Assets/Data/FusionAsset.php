<?php
namespace Atompulse\Component\FusionInclude\Assets\Data;

use Atompulse\Component\Domain\Data\DataContainerTrait;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionImportData
 * @package Atompulse\Component\FusionInclude\Assets\Data
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
    use DataContainerTrait;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this
            ->defineProperty('name', ['string'])
            ->defineProperty('namespace', ['string'])
            ->defineProperty('group', ['string','null'], 'global')
            ->defineProperty('order', ['int','null'], 0)
            ->defineProperty('files', ['array']);

        if ($data) {
            $this->fromArray($data);
        }
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
