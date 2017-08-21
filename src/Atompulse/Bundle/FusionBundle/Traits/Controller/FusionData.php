<?php
namespace Atompulse\Bundle\FusionBundle\Traits\Controller;

/**
 * Fusion
 *
 * @author Petru Cojocar
 */
trait FusionData
{
    /**
     * Application Data
     * Data stored in [Application.Data]
     * @var array
     */
    protected $appData = [];

    /**
     * Current Activated Modules
     * @var array
     */
    protected $modules = [];

    /**
     * Add data with $name and $value to js
     * @param string $name
     * @param mixed $value
     * @param bool|false $namespace
     */
    public function setJsData($name, $value, $namespace = false)
    {
        $this->get('fusion.data.manager')->setData($name, $value, $namespace);
    }


    /**
     * Activate a loaded angular module as an application
     * @param array $modules
     */
    public function activateModules(Array $modules = [])
    {
        if (count($modules)) {
            $activateModules = count($this->modules) ?
                array_unique(array_merge($this->modules, $modules)) : $modules;
            $this->modules = $activateModules;
            $this->setJsData('Modules', $activateModules, 'Application');
        }
    }

    /**
     * Get current active modules
     * @return array
     */
    public function getActiveModules()
    {
        return $this->modules;
    }

    /**
     * Add data to Application.Data
     * @param string $key
     * @param mixed $data
     */
    public function addAppData($key, $data)
    {
        if (isset($this->appData[$key])) {
            $data = array_merge_recursive($this->appData[$key], $data);
        }
        $this->appData[$key] = $data;
        $this->setJsData('Data', $this->appData, 'Application');
    }

    /**
     * Get all current app data
     * @return array
     */
    public function getAppData()
    {
        return $this->appData;
    }
}