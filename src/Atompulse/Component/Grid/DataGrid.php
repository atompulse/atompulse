<?php

namespace Atompulse\Component\Grid;

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
     * @var Request
     */
    protected $request = false;
    protected $requestNamespace = false;
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
        $this->prepareGridHeader();

        return $this;
    }

    /**
     * Grab the request and extract the information needed by the grid
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $requestNamespace
     * @return \Atompulse\Component\Grid\DataGrid
     */
    public function bindRequest(Request $request, $requestNamespace = false)
    {
        $this->request = $request;
        $this->requestNamespace = $requestNamespace;

        // extract the parameters
        $this->mappDtRequestParams()
            ->extractPaginationParams()
            ->extractFilterParams()
            ->extractSortingParams();

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
     * Process the data from the data source
     * @return \Atompulse\Component\Grid\DataGrid
     */
    public function processData()
    {
        $this->prepareGridHeader()
             ->prepareGridData();

        return $this;
    }

    /**
     * Get the processed grid data
     * @return array
     */
    public function getGridData()
    {
        $this->processData();

        return $this->gridData;
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

            $actionsColumnAdded = false;
            // build jqDataTables accepted column definition
            foreach ($this->config['fields']['settings'] as $fieldName => $settings) {
                $isActionField = false;
                if ($fieldName == 'actions') {
                    if ($this->gridRowActions) {
                        $actionsColumnAdded = true;
                        $isActionField = true;
                    } else {
                        continue;
                    }
                }
                // get items with custom render
                if (isset($settings['render'])) {
                    $this->gridCustomRenders[$idx] = $settings['render'];
                }
                $header[$idx]['aTargets'][] = $this->gridFieldsOrder[$fieldName];
                $header[$idx]['sTitle'] = isset($settings['label']) ? $settings['label'] : $fieldName;
                $header[$idx]['bVisible'] = isset($settings['visible']) ? $settings['visible'] : true;
                $header[$idx]['bSortable'] = isset($settings['sort']) ? $settings['sort'] : false;
                $header[$idx]['sWidth'] = isset($settings['width']) ? $settings['width'] : 'auto';
                $header[$idx]['sClass'] = isset($settings['css']) ? $settings['css'] : false;
                $header[$idx]['cellClass'] = isset($settings['cell_css']) ? $settings['cell_css'] : false;
                $header[$idx]['bAction'] = $isActionField;

                $header[$idx]['fType'] = isset($settings['type']) ? $settings['type'] :
                    (($fieldName == 'actions') ? 'action' : 'string');
                $header[$idx]['fScope'] = isset($settings['scope']) ? $settings['scope'] : null;

                // check for virtual field
                if (isset($settings['type']) && $settings['type'] == 'virtual') {
                    $this->virtualFields[] = $fieldName;
                }

                $idx++;
            }

            // add placeholder for actions if it wasn't defined in the fields
            if (!$actionsColumnAdded && $this->gridRowActions) {
                $header[$idx]['aTargets'][] = $idx;
                $header[$idx]['sTitle'] = 'Actions';
                $header[$idx]['bVisible'] = true;
                $header[$idx]['bSortable'] = false;
                $header[$idx]['sWidth'] = isset($settings['width']) ? $settings['width'] : 'auto';
                $header[$idx]['sClass'] = isset($settings['css']) ? $settings['css'] : false;
                $header[$idx]['cellClass'] = isset($settings['cell_css']) ? $settings['cell_css'] : false;
                $header[$idx]['bAction'] = true;
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
            if (isset($this->config['actions']) && count($this->config['actions'])) {
                // prepare the action params => retrieve the position of the items in
                // the datasource array to be used in js
                foreach ($this->config['actions'] as $actionName => $params) {
                    $rowActions[$actionName] = $params;
                    if (isset($rowActions[$actionName]['with'])) {
                        $rowActions[$actionName]['with'] = $this->prepareActionParams($params['with']);
                    } else {
                        $rowActions[$actionName]['with'] = '*';
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

            $definedFieldsOrder = array_flip($this->config['fields']['order']);
            $maxOrderIdx = max($definedFieldsOrder);

            // add order definition for fields which didn't had the order defined
            foreach ($this->config['fields']['settings'] as $fieldName => $settings) {
                if (!isset($definedFieldsOrder[$fieldName])) {
                    $definedFieldsOrder[$fieldName] = ++$maxOrderIdx;
                }
            }

            $this->gridFieldsOrder = $definedFieldsOrder;
        }

        return $this;
    }

    /**
     * Transform data tables params to mapped: paramName => paramValue array
     * @return \Atompulse\Component\Grid\DataGrid
     */
    protected function mappDtRequestParams()
    {
        if (!$this->mappedParams) {
            $request = $this->request;

            if ($this->requestNamespace) {
                $params = $request->get($this->requestNamespace);
            } else {
                // POST
                if ($request->getMethod() == 'POST') {
                    $params = $request->request->all();
                } // GET
                else {
                    $params = $request->query->all();
                }
            }

            $mappedParams = [];

            foreach ($params as $paramData) {
                $paramName = $paramData['name'];
                $paramValue = $paramData['value'];
                $mappedParams[$paramName] = $paramValue;
            }

            $this->mappedParams = $mappedParams;
        }

        return $this;
    }


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