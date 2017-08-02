<?php

namespace Atompulse\Component\Grid;

use Atompulse\Component\Grid\Configuration\GridConfiguration;
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
     * Create data grid instance
     * @param GridConfiguration $config
     */
    public function __construct(GridConfiguration $config);

    /**
     * Set the data source
     * @param DataSourceInterface $ds
     * @return DataGridInterface
     */
    public function setDataSource(DataSourceInterface $ds);

    /**
     * Set parameters
     * @param Parameters $parameters
     * @return mixed
     */
    public function setParameters(Parameters $parameters);

    /**
     * Get the grid data
     * @return array
     */
    public function getData();

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
