<?php

namespace Atompulse\Component\Domain\Model\StoreModel;

use Atompulse\Component\Domain\Model\StoreModel\StoreModelKey\StoreModelKeyInterface;

/**
 * Interface StoreModelInterface
 * @package Atompulse\Component\Domain\Model\StoreModel
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface StoreModelInterface
{
    public function fetchOne(StoreModelKeyInterface $storeModelKey);
    public function fetchByKeys(StoreModelKeysInterface $storeModelKey);
    public function fetchByProperty();
    public function runQuery();
}
