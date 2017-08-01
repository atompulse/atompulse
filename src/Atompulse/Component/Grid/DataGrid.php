<?php

namespace Atompulse\Component\Grid;

use Atompulse\Component\Grid\Configuration\Definition\GridAction;
use Atompulse\Component\Grid\Configuration\Definition\GridField;
use Atompulse\Component\Grid\Data\Flow\Parameters;
use Symfony\Component\HttpFoundation\Request;

use Atompulse\Component\Data\Transform;
use Atompulse\Component\Grid\Data\Source\DataSourceInterface;
use Atompulse\Component\Grid\Data\Source\PropelDataSource;
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

    protected $mappedParams = false;

    protected $filterParams = false;
    protected $sortParams = false;
    protected $paginationParams = false;

    /**
     * @var DataSourceInterface
     */
    protected $dataSource = false;

    protected $gridHeader = false;
    protected $gridData = false;
    /**
     * @var array
     */
    protected $gridFieldsOrder = [];
    /**
     * @var array
     */
    protected $virtualFields = [];
    protected $gridRowActions = false;
    protected $gridCustomRenders = false;

    protected $gridMetaData = false;

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
     * Setup flow parameters
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
     * @return \Atompulse\Component\Grid\DataGrid
     */
    public function setDataSource(DataSourceInterface $ds)
    {
        $this->dataSource = $ds;

        return $this;
    }

    /**
     * Execute the $query using the given DataSource
     * @param mixed $query
     * @param DataSourceInterface $ds
     * @return DataGrid
     */
    public function resolve($query, DataSourceInterface $ds = null)
    {
        if (is_null($ds)) {
            $ds = new PropelDataSource($query, $this->getPagination());
        } else {
            $ds->setup($query, $this->getPagination());
        }

        return $this->setDataSource($ds);
    }

    /**
     * Get the processed grid data
     * @return array
     */
    public function getGridData()
    {
        return $this->processData();
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
     */
    public function getFilters()
    {
        $this->extractFilterParams();

        return $this->filterParams;
    }

    /**
     * Return the sorters
     * @return array
     */
    public function getSorters()
    {
        $this->extractSortingParams();

        return $this->sortParams;
    }

    /**
     * Return the pagination
     * @return array
     */
    public function getPagination()
    {
        $this->extractPaginationParams();

        return $this->paginationParams;
    }

    /**
     * Process the data from the data source
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function processData()
    {
        $this->prepareGridHeader()
             ->prepareGridData();

        return $this;
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

            // TODO: introduce the

            /** @var GridField $field */
            foreach ($this->config->fields as $field) {
                switch ($field->type) {
                    case GridField::FIELD_TYPE_ACTIONS :
                        $header[$idx]['aTargets'][] = $idx;
                        $header[$idx]['sTitle'] = $field->label ? $field->label : 'Actions';
                        $header[$idx]['bVisible'] = $field->visible;
                        $header[$idx]['bSortable'] = $field->sort;
                        $header[$idx]['sWidth'] = $field->width;
                        $header[$idx]['sClass'] = $field->css;
                        $header[$idx]['cellClass'] = $field->cell_css;
                        $header[$idx]['bAction'] = $field->type == GridField::FIELD_TYPE_ACTIONS;
                        $header[$idx]['fType'] = $field->type;
                        break;
                    case GridField::FIELD_TYPE_VIRTUAL :
                        $this->virtualFields[] = $field->name;
                        break;
                    default:
                        // get items with custom render
                        if ($field->render) {
                            $this->gridCustomRenders[$idx] = $field->render;
                        }
                        $header[$idx]['aTargets'][] = $this->gridFieldsOrder[$field->name];
                        $header[$idx]['sTitle'] = $field->label ? $field->label : $field->name;
                        $header[$idx]['bVisible'] = $field->visible;
                        $header[$idx]['bSortable'] = $field->sort;
                        $header[$idx]['sWidth'] = $field->width;
                        $header[$idx]['sClass'] = $field->css;
                        $header[$idx]['cellClass'] = $field->cell_css;
                        $header[$idx]['bAction'] = $field->type == GridField::FIELD_TYPE_ACTIONS;
                        $header[$idx]['fType'] = $field->type;
                        break;
                }
                $idx++;
            }

            $this->gridHeader = $header;
        }

        return $this;
    }


    /**
     * Prepare the grid data
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function prepareGridData()
    {
        if (!$this->gridData) {
            $this->processGridFieldsOrderSettings();
            $output = ["aaData" => $this->normalizeDataSourceData()];

            $metaData = [
                "iPage" => (int) $this->dataSource->getCurrentPageNumber(),
                "iTotalRecords" => (int)$this->dataSource->getTotalRecords(),
                "iTotalDisplayRecords" => (int) $this->dataSource->getCurrentNumberOfRecords(),
                "iTotalPages" => (int)$this->dataSource->getTotalPages(),
                "iPages" => (array)$this->dataSource->getPages(),
                "iPaginate" => (boolean)$this->dataSource->haveToPaginate()
            ];

            $this->gridData = array_merge($metaData, $output);
        }

        return $this;
    }

    /**
     * Transform DataSource data to DataGrid compatible data structure
     * @return array
     */
    protected function normalizeDataSourceData()
    {
        $normalizedData = [];

        foreach ($this->dataSource->getData() as $row) {
            $rowSet = [];
            foreach ($row as $field => $value) {
                $field = Transform::unCamelize($field);
                // skip items that are not defined
                if (!isset($this->config['fields']['settings'][$field])) {
                    continue;
                }
                // assign order in the result array
                $rowSet[$this->gridFieldsOrder[$field]] = $value;
            }
            // add virtual fields entries
            if (count($this->virtualFields)) {
                foreach ($this->virtualFields as $vcName) {
                    $rowSet[$this->gridFieldsOrder[$vcName]] = '';
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
     * @return \Atompulse\Component\Grid\DataGrid
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
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function processGridFieldsOrderSettings()
    {
        if (!count($this->gridFieldsOrder)) {
            $definedFieldsOrder = array_flip($this->config->order);
            $maxOrderIdx = max($definedFieldsOrder);
            // add order definition for fields which didn't had the order defined
            /** @var GridField $field */
            foreach ($this->config->fields as $field) {
                if (!isset($definedFieldsOrder[$field->name])) {
                    $definedFieldsOrder[$field->name] = ++$maxOrderIdx;
                }
            }

            $this->gridFieldsOrder = $definedFieldsOrder;
        }

        return $this;
    }

//    /**
//     * Transform data tables params to mapped: paramName => paramValue array
//     * @return \Atompulse\Component\Grid\DataGrid
//     */
//    protected function mappDtRequestParams()
//    {
//        if (!$this->mappedParams) {
//            $parameters = $this->request;
//
//            if ($this->requestNamespace) {
//                $params = $parameters->get($this->requestNamespace);
//            } else {
//                // POST
//                if ($request->getMethod() == 'POST') {
//                    $params = $request->request->all();
//                } // GET
//                else {
//                    $params = $request->query->all();
//                }
//            }
//
//            $mappedParams = [];
//
//            foreach ($params as $paramData) {
//                $paramName = $paramData['name'];
//                $paramValue = $paramData['value'];
//                $mappedParams[$paramName] = $paramValue;
//            }
//
//            $this->mappedParams = $mappedParams;
//        }
//
//        return $this;
//    }


    /**
     * Extract filter params from the request
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function extractFilterParams()
    {
        if (!$this->filterParams) {
            $filters = [];

            if (isset($this->mappedParams['data-filters'])) {
                $filters = $this->mappedParams['data-filters'];
            }

            // remove unused filters
            foreach ($filters as $filterName => $filterValue) {
                if (!is_array($filterValue)) {
                    if (strlen(trim($filterValue)) == 0 || $filterValue == '') {
                        unset($filters[$filterName]);
                    }
                } else {
                    if (count($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                }
            }

            $this->filterParams = $filters;
        }

        return $this;
    }

    /**
     * Extract sorting params from the request
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function extractSortingParams()
    {
        if (!$this->sortParams) {
            $sortParams = [];
            $sorters = [];

            // check for sorting params
            if (isset($this->mappedParams['data-sorters'])) {
                $sorters = $this->mappedParams['data-sorters'];
            }

            //map sorting params
            foreach ($sorters as $sortColumn => $sortValue) {
                $sortParams[$sortColumn] = $sortValue;
            }

            $this->sortParams = $sortParams;
        }

        return $this;
    }

    /**
     * Extract pagination params from the request
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function extractPaginationParams()
    {
        if (!$this->paginationParams) {

            $page = isset($this->mappedParams['iDisplayStart']) ? $this->mappedParams['iDisplayStart'] : 1;
            $size = isset($this->mappedParams['iDisplayLength']) ? $this->mappedParams['iDisplayLength'] : 10;

            $this->paginationParams = ['page' => $page == 0 ? 1 : $page, 'page_size' => $size];
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
