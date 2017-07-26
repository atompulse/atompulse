<?php
namespace AtomPulse\FusionBundle\Fusion\Asset\Compiler;

use AtomPulse\FusionBundle\Fusion\Asset\Refine;

/**
 * Asset Compiler Interface
 *
 * @author Petru Cojocar
 */
interface CompilerInterface
{

    public function addRefiner(Refine\RefinerInterface $refiner);

    public function compile($assets);

}