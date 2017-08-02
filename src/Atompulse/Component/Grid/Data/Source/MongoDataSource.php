<?php
namespace Atompulse\Component\Grid\Data\Source;

use Atompulse\Component\Grid\Data\Flow\Parameters;
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
     * @var Parameters
     */
    protected $parameters = null;

    /**
     * @var mixed
     */
    protected $query = null;

    /**
     * @var Paginator
     */
    protected $pager = null;

    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * @param null $query
     */
    public function __construct($query = null)
    {
        $this->query = $query;
    }

    /**
     * Get the data from the source
     * @param Parameters $parameters
     * @return array
     */
    public function getData(Parameters $parameters)
    {
        $this->parameters = $parameters;

        $this->pager = $this->query->getQueryBuilder()->paginate($parameters->page, $parameters->pageSize);

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
        return (($this->parameters->pageSize != 0) && ($this->pager->getTotalRowsCount() > $this->parameters->pageSize));
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
        $lastPage = (int) ceil($this->pager->getTotalRowsCount() / $this->parameters->pageSize);
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
