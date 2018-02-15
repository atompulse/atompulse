<?php

namespace Atompulse\Bundle\RanBundle\Service\Ran;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class RanItem
 * @package Atompulse\Bundle\RanBundle\Service\Ran
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property string name Permission name (machine text)
 * @property string label Permission label (human readable text)
 * @property string context Permission context (inherited, explicit)
 * @property string group Permission group name
 * @property array granted Roles for which this permission is granted implicitly
 */
class RanItem implements DataContainerInterface
{
    use DataContainer;

    /**
     * RanItem constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->defineProperty('name', ['string']);
        $this->defineProperty('label', ['string']);
        $this->defineProperty('context', ['string']);
        $this->defineProperty('group', ['string']);
        $this->defineProperty('granted', ['array']);

        if ($data) {
            return $this->fromArray($data);
        }

        return $this;
    }

}
