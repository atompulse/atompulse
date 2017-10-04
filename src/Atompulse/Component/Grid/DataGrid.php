<?php

namespace Atompulse\Component\Grid;

use Atompulse\Component\Grid\Configuration\Definition\GridAction;
use Atompulse\Component\Grid\Configuration\Definition\GridField;
use Atompulse\Component\Grid\Data\Flow\OutputMetaData;
use Atompulse\Component\Grid\Data\Flow\OutputMetaDataInterface;
use Atompulse\Component\Grid\Data\Flow\Parameters;

use Atompulse\Component\Data\Transform;
use Atompulse\Component\Grid\Data\Source\DataSourceInterface;
use Atompulse\Component\Grid\Configuration\GridConfiguration;


/**
 * Class DataGrid
 * @package Atompulse\Component\Grid
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class DataGrid implements DataGridInterface
{
    /**
     * @var GridConfiguration
     */
    protected $config = null;

    /**
     * @var Parameters
     */
    protected $parameters = null;

    /**
     * @var DataSourceInterface
     */
    protected $dataSource = null;

    /**
     * @var array
     */
    protected $gridHeader = null;

    /**
     * @var array
     */
    protected $gridFieldsOrder = [];

    /**
     * @var array
     */
    protected $virtualFields = [];
    /**
     * @var array
     */
    protected $gridRowActions = null;
    /**
     * @var array
     */
    protected $gridCustomRenders = null;

    /**
     * @var array
     */
    protected $gridMetaData = null;

    /** @var OutputMetaDataInterface */
    protected $outputMetaDataMapper = null;

    /**
     * Create DataGrid Instance
     * @param GridConfiguration $config
     */
    public function __construct(GridConfiguration $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the parameters for the grid
     * @param Parameters $parameters
     * @return $this
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Set the data source
     * @param DataSourceInterface $ds
     * @return $this
     */
    public function setDataSource(DataSourceInterface $ds)
    {
        $this->dataSource = $ds;

        return $this;
    }

    /**
     * Get the grid data
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        if (!$this->dataSource) {
            throw new \Exception("DataSource not given, make sure you have passed a correct DataSource instance using DataGrid::setDataSource");
        }
        if (!$this->parameters) {
            throw new \Exception("Parameters not given, make sure you have passed a correct Parameters instance using DataGrid::setParameters");
        }

        $this->processGridFieldsOrderSettings();

        return [
            'data' => $this->normalizeDataSourceData($this->dataSource->getData($this->parameters)),
            'meta' => $this->getOutputMetaDataMapper()->getOutputMetaData($this->dataSource),
        ];
    }

    /**
     * @return \Atompulse\Component\Grid\Data\Flow\OutputMetaDataInterface
     */
    public function getOutputMetaDataMapper()
    {
        if (!$this->outputMetaDataMapper) {
            $this->outputMetaDataMapper = new OutputMetaData();
        }

        return $this->outputMetaDataMapper;
    }

    /**
     * @param \Atompulse\Component\Grid\Data\Flow\OutputMetaDataInterface $outputMetaDataMapper
     */
    public function setOutputMetaDataMapper(OutputMetaDataInterface $outputMetaDataMapper)
    {
        $this->outputMetaDataMapper = $outputMetaDataMapper;
    }

    /**
     * Process grid configuration and return metadata
     * @return array|bool
     */
    public function getMetaData()
    {
        if (!$this->gridMetaData) {
            $this->prepareGridHeader()
                 ->prepareGridActions();

            $this->gridMetaData = [
                'header' => $this->gridHeader,
                'columnsOrderMap' => [
                    'name2pos' => $this->gridFieldsOrder,
                    'pos2name' => array_flip($this->gridFieldsOrder)
                ],
                'rowActions' => $this->gridRowActions,
                'customRenders' => $this->gridCustomRenders
            ];
        }

        return $this->gridMetaData;
    }

    /**
     * Return the filters
     * @return array
     * @throws \Exception
     */
    public function getFilters()
    {
        if (!$this->parameters) {
            throw new \Exception("Parameters not given, make sure you have passed a correct Parameters instance using DataGrid::setParameters");
        }

        return $this->parameters->filters;
    }

    /**
     * Return the sorters
     * @return array
     * @throws \Exception
     */
    public function getSorters()
    {
        if (!$this->parameters) {
            throw new \Exception("Parameters not given, make sure you have passed a correct Parameters instance using DataGrid::setParameters");
        }

        return $this->parameters->sorters;
    }

    /**
     * Return basic pagination information
     * @return array
     * @throws \Exception
     */
    public function getPagination()
    {
        if (!$this->parameters) {
            throw new \Exception("Parameters not given, make sure you have passed a correct Parameters instance using DataGrid::setParameters");
        }

        return ['page' => $this->parameters->page, 'page-size' => $this->parameters->pageSize];
    }

    /**
     * Prepare the grid header
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function prepareGridHeader()
    {
        if (!$this->gridHeader) {
            $header = [];
            $idx = 0;
            $this->processGridFieldsOrderSettings();
            $this->prepareGridActions();
            $this->gridCustomRenders = [];

            /** @var GridField $field */
            foreach ($this->config->fields as $field) {
                $header[$idx]['field'] = $field->name;
                $header[$idx]['fieldType'] = $field->type;
                $header[$idx]['position'] = $this->gridFieldsOrder[$field->name];
                $header[$idx]['visible'] = $field->visible;
                $header[$idx]['sortable'] = $field->sort;
                $header[$idx]['width'] = $field->width;
                $header[$idx]['headerClass'] = $field->header_css;
                $header[$idx]['cellClass'] = $field->cell_css;
                switch ($field->type) {
                    case GridField::FIELD_TYPE_ACTIONS :
                        $header[$idx]['label'] = $field->label ? $field->label : 'Actions';
                        $header[$idx]['isAction'] = true;
                        break;
                    case GridField::FIELD_TYPE_VIRTUAL :
                        $header[$idx]['label'] = $field->label ? $field->label : Transform::camelize($field->name);
                        $this->virtualFields[] = $field->name;
                        if ($field->render) {
                            // custom render
                            $this->gridCustomRenders[$this->gridFieldsOrder[$field->name]] = $field->render;
                        }
                        break;
                    default:
                        $header[$idx]['label'] = $field->label ? $field->label : Transform::camelize($field->name);
                        $header[$idx]['isAction'] = false;
                        if ($field->render) {
                            // custom render
                            $this->gridCustomRenders[$this->gridFieldsOrder[$field->name]] = $field->render;
                        }
                        break;
                }
                $idx++;
            }

            $this->gridHeader = $header;
        }

        return $this;
    }

    /**
     * Transform DataSource data to DataGrid compatible data structure
     * @return array
     */
    protected function normalizeDataSourceData(array $data)
    {
        $normalizedData = [];
        $fields = array_keys($this->gridFieldsOrder);

        foreach ($data as $row) {
            $rowSet = [];
            foreach ($row as $field => $value) {
                $field = Transform::unCamelize($field);
                // skip fields that are not defined
                if (!in_array($field, $fields)) {
                    continue;
                }
                // assign order in the result array
                $rowSet[$this->gridFieldsOrder[$field]] = $value;
            }
            // add virtual fields entries
            if (count($this->virtualFields)) {
                foreach ($this->virtualFields as $virtualField) {
                    $rowSet[$this->gridFieldsOrder[$virtualField]] = null;
                }
            }
            // add blank entry for actions
            $rowSet[] = null;

            ksort($rowSet);
            $normalizedData[] = $rowSet;
        }

        return $normalizedData;
    }

    /**
     * Prepare the grid row actions
     * @return $this
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyNotValidException
     */
    protected function prepareGridActions()
    {
        if (!$this->gridRowActions) {
            $rowActions = [];
            if (count($this->config->actions)) {
                // prepare the action params
                /** @var GridAction $action */
                foreach ($this->config->actions as $action) {
                    $rowActions[$action->name] = $action->normalizeData();
                    if (is_array($action->with)) {
                        $rowActions[$action->name]['with'] = $this->prepareActionParams($action->with);
                    }
                }
            }
            $this->gridRowActions = $rowActions;
        }

        return $this;
    }

    /**
     * Process grid fields order
     * @return $this
     */
    protected function processGridFieldsOrderSettings()
    {
        if (!count($this->gridFieldsOrder)) {
            $definedFieldsOrder = count($this->config->order) ? array_flip(array_values($this->config->order)) : [];
            $orderIdx = count($definedFieldsOrder) ? max($definedFieldsOrder) : -1;

            // add order definition for fields which didn't had the order defined
            /** @var GridField $field */
            foreach ($this->config->fields as $field) {
                if (array_key_exists($field->name, $definedFieldsOrder)) {
                    $this->gridFieldsOrder[$field->name] = $definedFieldsOrder[$field->name];
                } else {
                    $orderIdx++;
                    $this->gridFieldsOrder[$field->name] = $orderIdx;
                }
            }
        }

        return $this;
    }

    /**
     * Prepare params for an action
     * @param array $params
     * @return array
     */
    private function prepareActionParams($params)
    {
        $preparedParams = [];
        foreach ($params as $paramName) {
            $preparedParams[$this->gridFieldsOrder[$paramName]] = $paramName;
        }

        return $preparedParams;
    }
}
