<?php
namespace AtomPulse\FusionBundle\Fusion\Asset\Compiler;

/**
 * Asset Compiler
 *
 * @author Petru Cojocar
 */
class Compiler implements CompilerInterface
{

//    protected $refiners;
//    protected $container;
//
//    public function __construct($container)
//    {
//        $this->container = $container;
//    }
//
//    public function addRefiner(Refine\RefinerInterface $refiner)
//    {
//        $this->refiners[] = $refiner;
//    }
//
//    protected function refine($content)
//    {
//        $refinedContent = $content;
//
//        foreach ($this->refiners as $refiner) {
//            $refinedContent = $refiner->refine($refinedContent);
//        }
//
//        return $refinedContent;
//    }
//
//    public function compile($assets)
//    {
//        $compiledAssets = '';
//
//        foreach($assets as $assetName => $assetFile)
//        {
//            $fullPathAsset = $this->container->get('kernel')->locateResource('@'.$assetFile);
//
//            if (!file_exists($fullPathAsset)) {
//                throw new \Exception("File [$assetFile] was not found!");
//            }
//
//            $fContent = file_get_contents($fullPathAsset);
//            $fHeader = "/*@asset $assetName*/";
//            $compiledAssets .= $fHeader . "\n" . $this->refine($fContent) . "\n";
//        }
//
//        return $compiledAssets;
//    }

}
