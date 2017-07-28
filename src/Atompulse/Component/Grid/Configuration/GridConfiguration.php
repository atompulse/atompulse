<?php
namespace Atompulse\Component\Grid\Configuration;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;
use Atompulse\Component\Grid\Configuration\Definition\GridAction;
use Atompulse\Component\Grid\Configuration\Definition\GridField;

/**
 * Class GridConfiguration
 * @package Atompulse\Component\Grid\Configuration
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property array actions
 * @property array fields
 *
 */
class GridConfiguration implements DataContainerInterface
{
    use DataContainer

    /**
     * @param array $configuration
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException
     */
    public function __construct(array $configuration = [])
    {
        $this->validProperties = [
            'actions' => 'array|null',
            'fields' => 'array|null'
        ];

        if ($configuration !== null) {
            return $this->fromArray($configuration);
        }

        return $this;
    }

    /**
     * @param GridAction $action
     */
    public function addAction(GridAction $action)
    {
        $this->properties['actions'][] = $action;

        return $this;
    }

    /**
     * @param GridField $field
     */
    public function addField(GridField $field)
    {
        $this->properties['fields'][] = $field;

        return $this;
    }

    /**
     * @param array $actions
     * @return $this
     */
    public function setActions(array $actions = [])
    {
        foreach ($actions as $action) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields = [])
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

}
