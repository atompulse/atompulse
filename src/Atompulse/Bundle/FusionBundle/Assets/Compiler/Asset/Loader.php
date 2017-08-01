<?php
namespace AtomPulse\FusionBundle\Fusion\Asset;

use AtomPulse\FusionBundle\Fusion\Asset\Compiler\CompilerInterface;

/**
 * Assets Loader
 *
 * @author Petru Cojocar
 */
class Loader
{
//    protected $assets = [];
//
//    protected $stack = [];
//
//    protected $stackTrace = [];
//
//    protected $compiledStack = [];
//
//    protected $compiledStackTrace = [];
//
//    protected $isCompiled = false;
//    /**
//     * Compiler
//     * @var \AtomPulse\FusionBundle\Fusion\Asset\Compiler\CompilerInterface
//     */
//    protected $compiler = null;
//    /**
//     * Loader params
//     * @var array
//     */
//    protected $params = [];
//
//    public function __construct($assetGroups, $params)
//    {
//        foreach ($assetGroups as $assetGroup => $assets) {
//            foreach ($assets as $assetName => $asset) {
//                $assetInfo = pathinfo($asset);
//                // clasify asset
//                $assetType = strtolower($assetInfo['extension']);
//                $this->assets[$assetType][$assetGroup][$assetName] = $asset;
//            }
//        }
//
//        $this->stack = ['js' => [], 'css' => []];
//
//        $this->params = $params;
//    }
//
//    public function addAssetGroup($assetGroup, $assets)
//    {
//        $assetGroup = strtolower($assetGroup);
//        foreach ($assets as $assetName => $asset) {
//            $assetInfo = pathinfo($asset);
//            // clasify asset
//            $assetType = strtolower($assetInfo['extension']);
//            $this->assets[$assetType][$assetGroup][$assetName] = $asset;
//        }
//        $this->loadAssetGroup($assetGroup, $assetType);
//    }
//
//    public function load($assetGroup)
//    {
//        if (array_key_exists($assetGroup, $this->assets['js'])) {
//            $this->loadAssetGroup($assetGroup, 'js');
//        } elseif (array_key_exists($assetGroup, $this->assets['css'])) {
//
//            $this->loadAssetGroup($assetGroup, 'css');
//        }
//        else {
//            throw new \Exception("Asset group [$assetGroup] is not defined");
//        }
//    }
//
//    public function hasAssets()
//    {
//        return count($this->stack);
//    }
//
//    public function getAssets()
//    {
//        return $this->stack;
//    }
//
//    public function getCompiledAssets()
//    {
//        return $this->compiledStack;
//    }
//
//    /**
//     * Check if the asset groups were compiled
//     * @return boolean
//     */
//    public function isCompiled()
//    {
//        if (!$this->isCompiled) {
//            $allCompiled = true;
//            if (count($this->stack['js'])) {
//                foreach ($this->stack['js'] as $order => $orderedAssetGroup) {
//                    foreach ($orderedAssetGroup as $assetGroup => $assets) {
//                        $allCompiled = $allCompiled && $this->isGroupCompiled($assetGroup, 'js');
//                    }
//                }
//            }
//            if (count($this->stack['css'])) {
//                foreach ($this->stack['css'] as $order => $orderedAssetGroup) {
//                    foreach ($orderedAssetGroup as $assetGroup => $assets) {
//                        $allCompiled = $allCompiled && $this->isGroupCompiled($assetGroup, 'css');
//                    }
//                }
//            }
//
//            $this->isCompiled = $allCompiled;
//        }
//
//        return $this->isCompiled;
//    }
//
//    public function isGroupCompiled($assetGroup, $assetType)
//    {
//        $compiledAssetGroup = $assetGroup . '.c.' . $assetType;
//        $compiledAssetGroupFile = $this->params['output'] . '/' . $compiledAssetGroup;
//
//        $this->compiledStack[$assetType][$assetGroup] = $this->params['webprefix'] . '/' . $compiledAssetGroup;
//        $this->compiledStackTrace[$assetType][$assetGroup] = $compiledAssetGroupFile;
//
//        return file_exists($compiledAssetGroupFile);
//    }
//
//    /**
//     * @param \AtomPulse\FusionBundle\Fusion\Asset\Compiler\CompilerInterface $compiler
//     */
//    public function setCompiler(CompilerInterface $compiler)
//    {
//        $this->compiler = $compiler;
//    }
//
//    public function compile()
//    {
//        foreach ($this->stack['css'] as $order => $assetGrouped) {
//            foreach ($assetGrouped as $assetGroup => $assets) {
//                $compiledAssetGroupName = $assetGroup . '.c.css';
//                $compiledAssetGroupFile = $this->params['output'] . '/' . $compiledAssetGroupName;
//                $compiledAssetsContent = $this->compiler->compile($assets);
//
//                file_put_contents($compiledAssetGroupFile, $compiledAssetsContent);
//
//                $this->compiledStack['css'][$assetGroup] = $this->params['webprefix'] . '/' . $compiledAssetGroupName;
//                $this->compiledStackTrace['css'][$assetGroup] = $compiledAssetGroupFile;
//            }
//        }
//
//        foreach ($this->stack['js'] as $order => $assetGrouped) {
//            foreach ($assetGrouped as $assetGroup => $assets) {
//                $compiledAssetGroupName = $assetGroup . '.c.js';
//                $compiledAssetGroupFile = $this->params['output'] . '/' . $compiledAssetGroupName;
//                $compiledAssetsContent = $this->compiler->compile($assets);
//
//                file_put_contents($compiledAssetGroupFile, $compiledAssetsContent);
//
//                $this->compiledStack['js'][$assetGroup] = $this->params['webprefix'] . '/' . $compiledAssetGroupName;
//                $this->compiledStackTrace['js'][$assetGroup] = $compiledAssetGroupFile;
//
//            }
//        }
//
//        $this->isCompiled = true;
//    }
//
//    public function getCompiledAssetGroupTrace($assetGroup, $assetType)
//    {
//        return $this->compiledStackTrace[$assetType][$assetGroup];
//    }
//
//
//    protected function loadAssetGroup($assetGroup, $assetType)
//    {
//        $this->stack[$assetType][][$assetGroup] = $this->assets[$assetType][$assetGroup];
//        $this->stackTrace[$assetType][$assetGroup] =  $this->assets[$assetType][$assetGroup];
//    }

//    private function resolveDependencies($assets)
//    {
//        $preparedAssets = [];
//        // extract independent assets
//        foreach ($assets as $group => $groupAssets) {
//            if (isset($groupAssets['none'])) {
//                foreach ($groupAssets['none'] as $assetName => $asset) {
//                    $preparedAssets[$assetName]['asset'] = $asset;
//                    $preparedAssets[$assetName]['dependents'] = [];
//                }
//            }
//        }
//        // add dependent assets to specific dependent
//        foreach ($assets as $group => $groupAssets) {
//            foreach ($groupAssets as $dependency => $asset) {
//                if ($dependency == 'none') continue;
//                $preparedAssets[$dependency]['dependents'][key($asset)] = $asset[key($asset)];
//            }
//        }
//
//        return $preparedAssets;
//    }

}
