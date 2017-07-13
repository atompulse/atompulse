<?php
namespace Atompulse\Component\Grid\Data\Source;

use Sokil\Mongo\Paginator;

/**
 * Class MongoDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class MongoDataSource implements DataSourceInterface
{
    /**
     * @var array
     */
    protected $pagination = ['page' => 1, 'page_size' => 10];

    /**
     * @var Paginator
     */
    protected $pager = false;

    /**
     * @var mixed
     */
    protected $data = false;

    /**
     * @param mixed $query
     * @param array $pagination
     */
    public function __construct($query = null, $pagination = ['page' => 1, 'page_size' => 10])
    {
        if ($query) {
            $this->setup($query, $pagination);
        }
    }

    /**
     * @param mixed $query
     * @param array $pagination
     */
    public function setup($query, $pagination = ['page' => 1, 'page_size' => 10])
    {
        $this->pagination = $pagination ? $pagination : ['page' => 1, 'page_size' => 10];
        // Get the PropelModelPager instance
        $this->pager = $query->getQueryBuilder()->paginate($pagination['page'], $pagination['page_size']);
    }

    /**
     * Get the data from the source
     * @return array
     */
    public function getData()
    {
        if (!$this->data) {
            if ($this->pager->getTotalRowsCount() > 0) {
                foreach ($this->pager as $row) {
                    $this->data[] = $row->toArray();
                }
            } else {
                $this->data = [];
            }
        }

        return $this->data;
    }

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords()
    {
        return count($this->data);
    }

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->pager->getTotalRowsCount();
    }

    /**
     * Check if there's pagination required
     * @return boolean
     */
    public function haveToPaginate()
    {
        return (($this->pagination['page_size'] != 0) && ($this->pager->getTotalRowsCount() > $this->pagination['page_size']));
    }

    /**
     * Total number of pages
     * @return int
     */
    public function getTotalPages()
    {
        return $this->pager->getTotalPagesCount();
    }

    /**
     * Return the range of available pages
     * @param int $nrPages
     * @return array
     */
    public function getPages($nrPages = 5)
    {
        $pages =[];
        $tmp = $this->pager->getCurrentPage() - floor($nrPages / 2);
        $lastPage = (int) ceil($this->pager->getTotalRowsCount() / $this->pagination['page_size']);
        $check = $lastPage - $nrPages + 1;
        $limit = ($check > 0) ? $check : 1;
        $begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

        $i = (int) $begin;
        while (($i < $begin + $nrPages) && ($i <= $lastPage)) {
            $pages[] = $i++;
        }

        return $pages;
    }

    /**
     * Current page number
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->pager->getCurrentPage();
    }
}
