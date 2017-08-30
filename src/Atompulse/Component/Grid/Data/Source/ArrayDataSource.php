<?php
namespace Atompulse\Component\Grid\Data\Source;

use Atompulse\Component\Grid\Data\Flow\Parameters;

/**
 * Class ArrayDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class ArrayDataSource implements DataSourceInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $paginationData = [
            'current_page_number' => 1,
            'total_records' => 0,
            'current_number_of_records' => 0,
            'total_pages' => 1,
            'pages' => 5,
            'has_pagination' => false
        ];

    /**
     * @param array $data
     * @param array $paginationData
     */
    public function __construct(array $data, array $paginationData = [
            'current_page_number' => 1,
            'total_records' => 0,
            'current_number_of_records' => 0,
            'total_pages' => 1,
            'pages' => 5,
            'has_pagination' => false
        ])
    {
        $this->data = $data;
        $this->paginationData = $paginationData;
    }

    /**
     * Get the data from the source
     * @return array
     */
    public function getData(Parameters $parameters)
    {
        return $this->data;
    }

    /**
     * Set current page number
     * @param int $page
     */
    public function setCurrentPageNumber(int $page)
    {
        $this->paginationData['current_page_number'] = $page;
    }

    /**
     * Get current page number
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->paginationData['current_page_number'];
    }

    /**
     * Set total number of records
     * @param int $totalRecords
     * @return int
     */
    public function setTotalRecords(int $totalRecords)
    {
        return $this->paginationData['total_records'] = $totalRecords;
    }

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->paginationData['total_records'];
    }

    /**
     * Set current number of records
     * @param int $currentNumberOfRecords
     * @return int
     */
    public function setCurrentNumberOfRecords(int $currentNumberOfRecords)
    {
        return $this->paginationData['current_number_of_records'] = $currentNumberOfRecords;
    }

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords()
    {
        return $this->paginationData['current_number_of_records'];
    }

    /**
     * Set number of total pages
     * @param int $totalPages
     * @return int
     */
    public function setTotalPages(int $totalPages)
    {
        return $this->paginationData['total_pages'] = $totalPages;
    }

    /**
     * Get number of total pages
     * @return int
     */
    public function getTotalPages()
    {
        return $this->paginationData['total_pages'];
    }

    /**
     * Set the range of available pages
     * @param int $pages
     * @return int
     */
    public function setPages(int $pages = 5)
    {
        return $this->paginationData['pages'] = $pages;
    }

    /**
     * Get the range of available pages
     * @return int
     */
    public function getPages()
    {
        return $this->paginationData['pages'];
    }

    /**
     * Set have to paginate
     * @param bool $haveToPaginate
     * @return bool
     */
    public function setHaveToPaginate(bool $haveToPaginate)
    {
        return $this->paginationData['has_pagination'] = $haveToPaginate;
    }

    /**
     * Have to paginate
     * @return bool
     */
    public function haveToPaginate()
    {
        return $this->paginationData['has_pagination'];
    }

}
