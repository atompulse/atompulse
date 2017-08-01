<?php

namespace Atompulse\Component\Grid\Data\Source;

/**
 * Interface DataSourceInterface
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataSourceInterface
{
    /**
     * @param mixed $query
     * @param array $pagination
     * @return void
     */
    public function setup($query, $pagination = ['page' => 1, 'page_size' => 10]);

    /**
     * Get the data from the source
     * @return
     */
    public function getData();

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
     * @return boolean
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
