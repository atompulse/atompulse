<?php

namespace Atompulse\Utils\Cache;

/**
 * Interface FileWriterInterface
 * @package Atompulse\Utils\Cache
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
interface FileWriterInterface
{
    /**
     * Set $cacheDirName
     * @param string $cacheDirName
     * @return $this
     */
    public function setCacheDirName(string $cacheDirName);

    /**
     * Get cache file name
     * @param string $filename
     * @return string
     */
    public function getFilePath(string $filename);

    /**
     * Store cache data
     * @param string $filename
     * @param mixed $data
     * @return boolean
     */
    public function writeToCache(string $filename, $data);

    /**
     * Get cached data
     * @param string $filename
     * @return bool|string
     */
    public function readFromCache(string $filename);

    /**
     * Invalidate a cache
     * @param string $filename
     */
    public function removeCache(string $filename);

}
