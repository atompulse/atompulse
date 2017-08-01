<?php
namespace Atompulse\Component\Grid\Configuration\Definition;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class GridAction
 * @package Atompulse\Component\Grid\Configuration\Definition
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property string name
 * @property string with
 * @property string render
 */
class GridAction implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array $action
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException
     */
    public function __construct(array $action = [])
    {
        $this->validProperties = [
            'name' => 'string',
            'with' => 'array|string',
            'render' => 'string|null'
        ];

        $this->defaultValues = [
            'with' => '*'
        ];

        if ($action !== null) {
            return $this->fromArray($action);
        }

        return $this;
    }

}
