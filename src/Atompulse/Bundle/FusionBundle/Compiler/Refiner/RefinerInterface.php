<?php
namespace Atompulse\FusionBundle\Compiler\Refiner;

/**
 * Interface RefinerInterface
 * @package Atompulse\FusionBundle\Compiler\Refiner
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