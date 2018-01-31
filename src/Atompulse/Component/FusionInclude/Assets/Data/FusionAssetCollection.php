<?php

namespace Atompulse\Component\FusionInclude\Assets\Data;

use Atompulse\Component\FusionInclude\Assets\Data\FusionAsset;
use Atompulse\Component\Domain\Data\DataContainerTrait;
use Atompulse\Component\Domain\Data\DataContainerInterface;

/**
 * Class FusionAssetCollection
 * @package Atompulse\Component\FusionInclude\Assets\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 *
 * @property array namespaces
 * @property array assets
 *
 */
class FusionAssetCollection implements DataContainerInterface
{
    use DataContainerTrait;

    /**
     * @var array
     */
    protected $groupsMap = [];

    /**
     * @var array
     */
    protected $namespaceMap = [];

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        $this->defineProperty('namespaces', ['array']);
        $this->defineProperty('assets', ['array']);

        if ($data) {
            $this->fromArray($data);
        }
    }

    /**
     * @param array $assets
     */
    public function setAssets(array $assets)
    {
        foreach ($assets as $assetData) {
            $asset = new FusionAsset($assetData);
            $this->addAsset($asset);
        }
    }

    /**
     * @param FusionAsset $asset
     */
    public function addAsset(FusionAsset $asset)
    {
        $this->addPropertyValue('assets', $asset);
        $this->groupsMap[$asset->group ?: 'global'][] = $asset;
        $this->namespaceMap[$asset->name ?: '*'][] = $asset;
    }

    /**
     * @param string $asset
     * @return FusionAsset
     */
    public function getAsset(string $asset) : FusionAsset
    {
        return $this->assets[$asset];
    }

    /**
     * @param array $assets
     */
    public function setNamespaces(array $assets)
    {
        foreach ($assets as $assetData) {
            $asset = new FusionAsset($assetData);
            $this->addAsset($asset);
        }
    }

    /**
     * @param FusionIncludeNamespace $fusionNamespace
     */
    public function addNamespace(FusionIncludeNamespace $fusionNamespace)
    {
        $this->addPropertyValue('namespaces', $fusionNamespace);
//        $this->namespaces[$fusionNamespace->name] = $fusionNamespace;
    }

    /**
     * @param string $namespace
     * @return FusionIncludeNamespace
     */
    public function getNamespace(string $namespace) : FusionIncludeNamespace
    {
        return $this->namespaces[$namespace];
    }

    /**
     * @return array
     */
    public function getGroups() : array
    {
        return array_keys($this->groupsMap);
    }

    /**
     * @param string $group
     * @return array
     * @throws \Exception
     */
    public function getGroupAssets(string $group) : array
    {
        if (isset($this->groupsMap[$group])) {
            return $this->groupsMap[$group];
        }

        throw new \Exception("No assets found under this group name [$group]");
    }

    /**
     * @return array
     */
    public function getDeclaredNamespaces() : array
    {
        return $this->namespaceMap;
    }

    /**
     * @param string $namespace
     * @return array
     * @throws \Exception
     */
    public function getNamespaceAssets(string $namespace) : array
    {
        if (isset($this->namespaceMap[$namespace])) {
            return $this->namespaceMap[$namespace];
        }

        throw new \Exception("There were no assets found with this namespace [$namespace]");
    }

}
