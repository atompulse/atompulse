<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Refiner;

/**
 * Interface RefinerInterface
 * @package Atompulse\Bundle\FusionBundle\Compiler\Refiner
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface RefinerInterface
{
    /**
     * Optimeze string content
     * @param string $content
     */
    public static function refine($content);

}