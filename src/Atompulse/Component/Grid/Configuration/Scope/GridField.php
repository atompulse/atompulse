<?php
namespace Atompulse\Component\Grid\Configuration\Scope;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class GridField
 * @package Atompulse\Component\Grid\Configuration\Scope
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class GridField implements DataContainerInterface
{
    use DataContainer;

    const FIELD_TYPE_STRING = 'string';
    const FIELD_TYPE_NUMBER = 'number';
    const FIELD_TYPE_VIRTUAL = 'virtual';

    /**
     * @param array $field
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException
     */
    public function __construct(array $field = [])
    {
        $this->validProperties = [
            'visible' => 'boolean|null',
            'type' => 'string|null',
            'label' => 'string|null',
            'sort' => 'boolean|null',
            'render' => 'string|null',
            'css' => 'string|null',
            'cell_css' => 'string|null',
            'width' => 'string|null',

        ];

        if ($field !== null) {
            return $this->fromArray($field);
        }

        return $this;
    }

}
