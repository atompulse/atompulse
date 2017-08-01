<?php
namespace Atompulse\Component\Grid\Configuration;

use Atompulse\Component\Domain\Data\DataContainer;
use Atompulse\Component\Domain\Data\DataContainerInterface;
use Atompulse\Component\Grid\Configuration\Definition\GridAction;
use Atompulse\Component\Grid\Configuration\Definition\GridField;
use Atompulse\Component\Grid\Configuration\Exception\GridConfigurationException;

/**
 * Class GridConfiguration
 * @package Atompulse\Component\Grid\Configuration
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property array actions
 * @property array fields
 * @property array order
 *
 */
class GridConfiguration implements DataContainerInterface
{
    use DataContainer;

    /**
     * @param array $configuration
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyValueNotValidException
     */
    public function __construct(array $configuration = ['actions' => [], 'fields' => [], 'order' => []])
    {
        $this->validProperties = [
            'actions' => 'array|null',
            'fields' => 'array|null',
            'order' => 'array|null',
        ];

        $this->fromArray($configuration);

        $this->validateConfiguration();

        return $this;
    }

    /**
     * @param GridAction $action
     */
    public function addAction(GridAction $action)
    {
        $this->addPropertyValue('actions', $action);

        return $this;
    }

    /**
     * @param GridField $field
     * @return $this
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyNotValidException
     */
    public function addField(GridField $field)
    {
        $this->addPropertyValue('fields', $field);

        return $this;
    }

    /**
     * @param array $fieldsOrder
     * @return $this
     */
    public function setFieldsOrder(array $fieldsOrder = [])
    {
        $this->addPropertyValue('order', $fieldsOrder);

        return $this;
    }

    /**
     * @param array $actions
     * @return $this
     */
    public function setActions(array $actions = [])
    {
        foreach ($actions as $actionName => $action) {
            if (!isset($action['name'])) {
                $action['name'] = $actionName;
            }
            $this->addAction(new GridAction($action));
        }

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields = [])
    {
        foreach ($fields as $fieldName => $field) {
            if (!isset($field['name'])) {
                $field['name'] = $fieldName;
            }
            $this->addField(new GridField($field));
        }

        return $this;
    }

    /**
     * Validate minimum viable configuration
     */
    protected function validateConfiguration()
    {
        if (count($this->fields) == 0) {
            throw new GridConfigurationException("GridConfiguration should have at least 1 field defined, none given");
        }
    }

}
