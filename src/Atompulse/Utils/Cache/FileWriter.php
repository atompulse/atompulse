<?php

namespace Atompulse\Utils\Cache;

/**
 * Class FileWriter
 * @package Atompulse\Utils\Cache
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class FileWriter implements FileWriterInterface
{
    /**
     * @var null
     */
    protected $cacheRootDir = null;
    /**
     * @var null
     */
    protected $cacheNameDir = null;
    /**
     * @var null
     */
    protected $cacheTargetDir = null;

    /**
     * FileWriter constructor.
     * @param string $cacheRootDir
     * @param string|null $cacheDirName
     */
    public function __construct(string $cacheRootDir, string $cacheDirName = null)
    {
        $this->cacheRootDir = $cacheRootDir;
        $this->cacheNameDir = $cacheDirName ? $cacheDirName : uniqid('cache_');
        $this->cacheTargetDir = $this->cacheRootDir . DIRECTORY_SEPARATOR . $this->cacheNameDir;
    }

    /**
     * Set $cacheDirName
     * @param string $cacheDirName
     * @return $this
     */
    public function setCacheDirName(string $cacheDirName)
    {
        $this->cacheNameDir = $cacheDirName;

        return $this;
    }

    /**
     * Get cache file name
     * @param string $filename
     * @return string
     */
    public function getFilePath(string $filename)
    {
        $this->initCacheFolder();

        return $this->cacheTargetDir . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Store cache data
     * @param string $filename
     * @param mixed $data
     * @return boolean
     */
    public function writeToCache(string $filename, $data)
    {
        $cacheFile = $this->getFilePath($filename);

        if ($this->initCacheFolder()) {
            // write file
            return file_put_contents($cacheFile, $data);
        }

        return false;
    }

    /**
     * Get cached data
     * @param string $filename
     * @return bool|string
     */
    public function readFromCache(string $filename)
    {
        $cacheFile = $this->getFilePath($filename);

        if ($this->initCacheFolder() && file_exists($cacheFile)) {
            // read file
            if ($data = file_get_contents($cacheFile)) {
                return $data;
            }
        }

        return false;
    }

    /**
     * Invalidate a cache
     * @param string $filename
     */
    public function removeCache(string $filename)
    {
        $cacheFile = $this->getFilePath($filename);

        // if cache exists
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * Initialize the Cache Folder
     * @return boolean
     */
    protected function initCacheFolder()
    {
        if (!file_exists($this->cacheTargetDir)) {
            // create folder in the cache directory
            return @mkdir($this->cacheTargetDir, 0777, true);
        }

        return true;
    }

}