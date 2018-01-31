<?php

namespace Atompulse\Component\FusionInclude;

use Atompulse\Component\FusionInclude\Assets\Data\FusionAsset;
use Atompulse\Component\FusionInclude\Assets\Data\FusionIncludeNamespace;

/**
 * Class FusionIncludeEngine
 * @package Atompulse\Component\FusionInclude
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FusionIncludeEngine
{
    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var array
     */
    protected $namespaces = [];

    /**
     * @var array
     */
    protected $groupsMap = [];

    /**
     * @var array
     */
    protected $namespaceMap = [];

    /**
     * @var string
     */
    protected $defaultGroup = 'global';

    public function __construct()
    {
    }

    /**
     * @param FusionAsset $asset
     */
    public function addAsset(FusionAsset $asset)
    {
        $this->assets[$asset->name] = $asset;
        $this->groupsMap[$asset->group ?: $this->defaultGroup][] = $this->assets[$asset->name];
        $this->namespaceMap[$asset->namespace][] = $this->assets[$asset->name];
    }

    /**
     * @param FusionIncludeNamespace $fusionNamespace
     */
    public function addNamespace(FusionIncludeNamespace $fusionNamespace)
    {
        $this->namespaces[$fusionNamespace->name] = $fusionNamespace;
    }

    /**
     * @param string $asset
     * @return FusionAsset
     */
    public function getAsset(string $asset): FusionAsset
    {
        return $this->assets[$asset];
    }

    /**
     * @param string $namespace
     * @return FusionIncludeNamespace
     */
    public function getNamespace(string $namespace): FusionIncludeNamespace
    {
        return $this->namespaces[$namespace];
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return array_keys($this->groupsMap);
    }

    /**
     * @param string $group
     * @return array
     * @throws \Exception
     */
    public function getGroupAssets(string $group): array
    {
        if (isset($this->groupsMap[$group])) {
            return $this->groupsMap[$group];
        }

        throw new \Exception("No assets found under this group name [$group]");
    }

    /**
     * @return array
     */
    public function getAssetsNamespaces(): array
    {
        return array_keys($this->namespaceMap);
    }

    /**
     * @param string $namespace
     * @return array
     * @throws \Exception
     */
    public function getNamespaceAssets(string $namespace): array
    {
        if (isset($this->namespaceMap[$namespace])) {
            return $this->namespaceMap[$namespace];
        }

        throw new \Exception("There were no assets found with this namespace [$namespace]");
    }

    /**
     * @return array
     */
    public function getNamespaces() : array
    {
        return $this->namespaces;
    }

    /**
     * @return array
     */
    public function getAssets() : array
    {
        return $this->assets;
    }
}
