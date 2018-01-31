<?php

namespace Atompulse\Component\FusionInclude\Assets\Data;

use Atompulse\Component\Domain\Data\DataContainerTrait;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionNamespace
 * @package Atompulse\Component\FusionInclude\Assets\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property string name
 * @property string source
 * @property string target
 */
class FusionNamespace implements DataContainerInterface
{
    use DataContainerTrait;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this
            ->defineProperty('name', ['string'])
            ->defineProperty('source', ['string'])
            ->defineProperty('target', 'string');

        return $this->fromArray($data);
    }

}
