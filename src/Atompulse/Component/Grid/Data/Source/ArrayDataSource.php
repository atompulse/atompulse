<?php
namespace Atompulse\Component\Grid\Data\Source;

/**
 * Class ArrayDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class ArrayDataSource implements DataSourceInterface
{
    protected $pageMetaData = [
            'current_page_number' => 1,
            'total_records' => 1,
            'current_number_of_records' => 1,
            'total_pages' => 1,
            'pages' => 1,
            'has_pagination' => 1
        ];

    protected $data = [];

    /**
     * @param mixed $query
     * @param array $pagination
     */
    public function setup($query, $pagination = ['page' => 1, 'page_size' => 10])
    {
    }

    /**
     * Get the data from the source
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Current page number
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->pageMetaData['current_page_number'];
    }

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->pageMetaData['total_records'];
    }

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords()
    {
        return $this->pageMetaData['current_number_of_records'];
    }

    public function getTotalPages()
    {
        return $this->pageMetaData['total_pages'];

    }

    public function getPages()
    {
        return $this->pageMetaData['pages'];

    }

    public function haveToPaginate()
    {
        return $this->pageMetaData['has_pagination'];

    }

}
