<?php

namespace Atompulse\Component\Grid\Data\Source;

use Atompulse\Component\Grid\Data\Flow\Parameters;

/**
 * Interface DataSourceInterface
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataSourceInterface
{
    /**
     * Get the data from the source
     * @return
     */
    public function getData(Parameters $parameters);

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords();

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords();

    /**
     * Check if there's pagination required
     * @return bool
     */
    public function haveToPaginate();

    /**
     * Total number of pages
     * @return int
     */
    public function getTotalPages();

    /**
     * Return the range of available pages
     * @return []
     */
    public function getPages();

    /**
     * Current page number
     * @return int
     */
    public function getCurrentPageNumber();

}
