<?php

namespace Atompulse\Craft\Foundation\Component;

/**
 * Interface ComponentInterface
 * @package Atompulse\Craft\Foundation\Component
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface ComponentInterface
{
    public function getKey() : ComponentKeyInterface;

    public function getName() : string;

        function getTag() { return $this->_tag; }
        function setTag($value) { $this->_tag=$value; }
}
