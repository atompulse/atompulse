<?php

namespace Atompulse\Craft\Foundation\Component;

/**
 * Trait ComponentTrait
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait ComponentTrait
{
    /**
     * @var ComponentKeyInterface
     */
    private $key;

    /**
     * @return string
     */
    public function getName() : string
    {
        return static::class;
    }

    /**
     * @return ComponentKeyInterface
     */
    public function getKey() : ComponentKeyInterface
    {
        return $this->key;
    }
}