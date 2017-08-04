<?php
namespace Atompulse\Bundle\RadBundle\Traits\Controller;

use Atompulse\Component\Data\Transform;
use Atompulse\Component\Grid\Configuration\GridConfiguration;
use Atompulse\Component\Grid\Data\Flow\Parameters;
use Atompulse\Component\Grid\DataGrid;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RadControllerDataGridTrait
 * @package Atompulse\Bundle\RadBundle\Traits\Controller
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait RadControllerDataGridTrait
{
    use ContainerAwareTrait;

    /**
     * Default grid parameter prefix
     * @var string
     */
    protected $gridParameterPrefix = 'grids';

    /**
     * Override default grid parameter prefix
     * @param string $prefix
     */
    public function setDataGridParameterPrefix(string $prefix)
    {
        $this->gridParameterPrefix = $prefix;
    }

    /**
     * Initialize and inject a data grid meta data into the application data namespace
     * @param string $applicationDataNamespace
     * @param string|null $gridConfigurationParameter
     */
    public function addGridMetaData(string $applicationDataNamespace, string $gridConfigurationParameter = null)
    {
        $gridConfigurationParameter = $this->resolveGridConfigurationParameter($applicationDataNamespace, $gridConfigurationParameter);

        $grid = $this->initDataGrid($gridConfigurationParameter);

        $this->addAppData($applicationDataNamespace, ['Grid' => ['MetaData' => $grid->getMetaData()]]);
    }

    /**
     * @param Request $request
     * @param string $applicationDataNamespace
     * @param string|null $gridConfigurationParameter
     * @return DataGrid
     */
    public function getGridInstance(Request $request, string $applicationDataNamespace, string $gridConfigurationParameter = null)
    {
        $gridConfigurationParameter = $this->resolveGridConfigurationParameter($applicationDataNamespace, $gridConfigurationParameter);

        $grid = $this->initDataGrid($gridConfigurationParameter);

        $parameters = $this->resolveRequestParameters($request);

        $grid->setParameters($parameters);

        return $grid;
    }

    /**
     * @param string $gridConfigurationParameter
     * @return DataGrid
     */
    protected function initDataGrid(string $gridConfigurationParameter)
    {
        $gridConfigurationData = $this->container->getParameter($gridConfigurationParameter);
        $gridConfiguration = new GridConfiguration($gridConfigurationData);

        $grid = new DataGrid($gridConfiguration);

        return $grid;
    }

    /**
     * @param string $applicationDataNamespace
     * @param string|null $gridConfigurationParameter
     * @return string|string
     */
    protected function resolveGridConfigurationParameter(string $applicationDataNamespace, string $gridConfigurationParameter = null)
    {
        // if given namespace is [GreyDuck] then the expected grid parameter is [grids.grey_duck]
        $gridConfigurationParameter = $gridConfigurationParameter ?
            $gridConfigurationParameter : "$this->gridParameterPrefix." . Transform::underscore($applicationDataNamespace);

        return $gridConfigurationParameter;
    }

    /**
     * @param Request $request
     * @return Parameters
     */
    protected function resolveRequestParameters(Request $request)
    {
        // POST
        if ($request->getMethod() == 'POST') {
            $requestParams = $request->request->all();
        } // GET
        else {
            $requestParams = $request->query->all();
        }

        $mappedParams = [];

        foreach ($requestParams as $paramData) {
            $paramName = $paramData['name'];
            $paramValue = $paramData['value'];
            $mappedParams[$paramName] = $paramValue;
        }

        $params = [
            'page' => isset($mappedParams['page']) ? ($mappedParams['page'] <= 0 ? 1 : $mappedParams['page']) : 1,
            'pageSize' => isset($mappedParams['page-size']) ? $mappedParams['page-size'] : 10,
            'filters' => $this->extractFilterParams($mappedParams),
            'sorters' => $this->extractSortingParams($mappedParams),
        ];


        $parameters = new Parameters($params);

        return $parameters;
    }

    /**
     * @param array $mappedParams
     * @return array
     */
    private function extractFilterParams(array $mappedParams)
    {
        $filters = [];

        if (isset($mappedParams['data-filters'])) {
            $filters = $mappedParams['data-filters'];
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

        return $filters;
    }

    /**
     * @param array $mappedParams
     * @return array
     */
    private function extractSortingParams(array $mappedParams)
    {
        $sorters = [];

        // check for sorting params
        if (isset($mappedParams['data-sorters'])) {
            $sorters = $mappedParams['data-sorters'];
        }

        return $sorters;
    }

}
