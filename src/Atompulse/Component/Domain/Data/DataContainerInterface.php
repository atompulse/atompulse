<?php
namespace Atompulse\Component\Domain\Data;

/**
 * Interface DataContainerInterface
 * @package Atompulse\Component\Domain\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface DataContainerInterface
{
    /**
     * Get data as array
     * @return array
     */
    public function toArray();

    /**
     * Add data as array
     * @param array $data
     * @return mixed
     */
    public function fromArray(array $data);

}
