<?php

namespace Atompulse\Component\Grid;

use Atompulse\Component\Grid\Data\Flow\Parameters;
use Atompulse\Component\Grid\Data\Source\DataSourceInterface;

/**
 * Interface DataGridInterface
 * @package Atompulse\Component\Grid
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataGridInterface 
{
    /**
     * Set data flow parameters - pagination, filters, sorters
     * @param Parameters $parameters
     * @return mixed
     */
    public function setParameters(Parameters $parameters);

    /**
     * Set the data source
     * @param DataSourceInterface $ds
     * @return \Atompulse\Component\Grid\DataGrid
     */
    public function setDataSource(DataSourceInterface $ds);

    /**
     * Resolve the query
     * @param mixed $query
     * @param DataSourceInterface $ds
     * @return mixed
     */
    public function resolve($query, DataSourceInterface $ds = null);

//    /**
//     * Process the data from the data source
//     * @return \Atompulse\Component\Grid\DataGrid
//     */
//    public function processData();

    /**
     * Get the processed grid data
     * @return array
     */
    public function getGridData();

    /**
     * Get the grid meta data
     * @return array
     */
    public function getMetaData();

    /**
     * Return the filters
     * @return array
     */
    public function getFilters();

    /**
     * Return the sorters
     * @return array
     */
    public function getSorters();

    /**
     * Return the pagination
     * @return array
     */
    public function getPagination();

}
