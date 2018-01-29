<?php

namespace Atompulse\Component\FusionInclude;

use Atompulse\Bundle\FusionBundle\Assets\Data\FusionAsset;
use Atompulse\Component\FusionInclude\Assets\Data\FusionAssetCollection;
use Atompulse\Component\FusionInclude\Assets\Data\FusionNamespace;

/**
 * Class FusionIncludeEngine
 * @package Atompulse\Component\FusionInclude
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludeEngine
{
    /**
     * @var FusionAssetCollection
     */
    protected $assetCollection = null;

    /**
     * @var array
     */
    protected $namespaces = [];

    /**
     * @param FusionAsset $asset
     * @throws \Atompulse\Component\Domain\Data\Exception\PropertyNotValidException
     */
    public function addAsset(FusionAsset $fusionAsset)
    {
        $this->assetCollection->addAsset($fusionAsset);
    }

    /**
     * @param FusionAssetCollection $fusionAssetCollection
     */
    public function addCollection(FusionAssetCollection $fusionAssetCollection)
    {
        $this->assetCollection = $fusionAssetCollection;
    }

    /**
     * @param FusionNamespace $fusionNamespace
     */
    public function addNamespace(FusionNamespace $fusionNamespace)
    {
        $this->namespaces[$fusionNamespace->name] = $fusionNamespace;
    }

    /**
     * @return FusionAssetCollection
     */
    public function getCollection() : FusionAssetCollection
    {
        return $this->assetCollection;
    }

    public function getGroup(string $group)
    {

    }

    public function getAsset(string $asset)
    {

    }

}
