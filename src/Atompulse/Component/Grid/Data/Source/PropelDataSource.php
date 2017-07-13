<?php

namespace Atompulse\Component\Grid\Data\Source;

/**
 * Class PropelDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class PropelDataSource implements DataSourceInterface
{
    /**
     * @var \PropelModelPager
     */
    protected $pager = false;

    /**
     * @var \PropelArrayCollection
     */
    protected $data = false;

    /**
     * @param \ModelCriteria|null $query
     * @param array $pagination
     */
    public function __construct(\ModelCriteria $query = null, $pagination = ['page' => 1, 'page_size' => 10])
    {
        if ($query) {
            $this->setup($query, $pagination);
        }
    }

    /**
     * @param \ModelCriteria $query
     * @param array $pagination
     * @return mixed|void
     */
    public function setup($query, $pagination = ['page' => 1, 'page_size' => 10])
    {
        // Get the PropelModelPager instance
        $this->pager = $query->paginate($pagination['page'], $pagination['page_size']);
    }

    /**
     * Get the data from the source
     * @return array|\PropelArrayCollection
     */
    public function getData()
    {
        if (!$this->data) {
            $this->data = $this->pager->getResults()->getData();
        }

        return $this->data;
    }

    /**
     * Current page number
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->pager->getPage();
    }

    /**
     * Total pages
     * @return int
     */
    public function getTotalPages()
    {
        return $this->pager->getLastPage();
    }

    /**
     * Get range of pages
     * @param int $nrOfLinks
     * @return array
     */
    public function getPages($nrOfLinks = 5)
    {
        return $this->pager->getLinks($nrOfLinks);
    }

    /**
     * @return bool
     */
    public function haveToPaginate()
    {
        return $this->pager->haveToPaginate();
    }

    /**
     * Total number of records
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->pager->getNbResults();
    }

    /**
     * Current number of records
     * @return int
     */
    public function getCurrentNumberOfRecords()
    {
        return count($this->data);
    }

}