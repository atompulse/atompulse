<?php

namespace Atompulse\Component\Grid\Data\Source;

use Atompulse\Component\Grid\Data\Flow\Parameters;

/**
 * Class PropelDataSource
 * @package Atompulse\Component\Grid\Data\Source
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class PropelDataSource implements DataSourceInterface
{
    /**
     * @var Parameters
     */
    protected $parameters = null;

    /**
     * @var \ModelCriteria
     */
    protected $query = null;

    /**
     * @var \PropelModelPager
     */
    protected $pager = null;

    /**
     * @var \PropelArrayCollection
     */
    protected $data = null;

    /**
     * @param \ModelCriteria $query
     */
    public function __construct(\ModelCriteria $query)
    {
        $this->query = $query;
    }

    /**
     * Get the data from the source
     * @param Parameters $parameters
     * @return array|\PropelArrayCollection
     */
    public function getData(Parameters $parameters)
    {
        $this->parameters = $parameters;

        // Get the PropelModelPager instance
        $this->pager = $this->query->paginate($parameters->page, $parameters->pageSize);

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
     * Check if there's pagination required
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
