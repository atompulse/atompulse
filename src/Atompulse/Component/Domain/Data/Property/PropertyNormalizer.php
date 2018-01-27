<?php

namespace Atompulse\Component\Domain\Data\Property;

/**
 * Interface PropertyNormalizer
 * @package Atompulse\Component\Domain\Data\Property
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface PropertyNormalizer
{
    /**
     * @param $value
     * @return mixed
     */
    public function normalizeValue($value);

}
