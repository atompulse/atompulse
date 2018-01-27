<?php

namespace Atompulse\Component\Domain\Model\StoreModel\Entities;

use Atompulse\Component\Domain\Model\StoreModel\StoreModelInterface;
use Atompulse\Component\Domain\Model\StoreModel\StoreModelKey\StoreModelKeyInterface;
use Atompulse\Component\Domain\Model\StoreModel\StoreModelProperties\StoreModelProperties;

/**
 * Interface EntityModelInterface
 * @package Atompulse\Component\Domain\Model\StoreModel\Entities
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface EntityModelInterface
{

    public function __construct(StoreModelInterface $storeModel);

    public function getKey() : StoreModelKeyInterface;

    public function getModelProperties() : StoreModelProperties;

/**
 * Data objects in StoreModel are known as entities.
 * An entity has one or more named properties, each of which can have one or more values.
 * Entities of the same kind do not need to have the same properties,
 * and an entity's values for a given property do not all need to be of the same data type.
 * (If necessary, an application can establish and enforce such restrictions in its own data model.)
 */

//[StoreModelProperty:
//->type(Integers,Floating-point numbers,Strings,Dates,Binary data)
//->name(String)
//->alias(String)
//]
//Store Key->uniquely identifies the entity
//->namespace
//- [StoreModelProperty..])



}
