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
     * @param FusionAsset $fusionAsset
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
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @return FusionAssetCollection
     */
    public function getCollection() : FusionAssetCollection
    {
        return $this->assetCollection;
    }

    /**
     * @param string $group
     * @return array
     */
    public function getGroup(string $group) : array
    {
        return $this->assetCollection->getGroupAssets($group);
    }

    /**
     * @param string $asset
     * @return FusionAsset
     */
    public function getAsset(string $asset)
    {
        return $this->assetCollection->getAsset($asset);
    }

}
