<?php

namespace Atompulse\Craft\Foundation\Component\Kind;

use Atompulse\Craft\Foundation\Component\ComponentCollectionInterface;
use Atompulse\Craft\Foundation\Component\ComponentInterface;
use Atompulse\Craft\Foundation\Component\ComponentKeyInterface;

/**
 * Interface ContainerComponentInterface
 * @package Atompulse\Craft\Foundation\Component\Kind
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface ContainerComponentInterface extends ComponentInterface
{
    public function addComponent(ComponentInterface $component) : self;

    public function deleteComponent(ComponentInterface $component) : self;

    public function removeComponent(ComponentKeyInterface $componentKey) : ComponentInterface;

    public function getCollection() : ComponentCollectionInterface;
}
