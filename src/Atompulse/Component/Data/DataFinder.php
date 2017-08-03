<?php

namespace Atompulse\Component\Data;

/**
 * Class Data Finder
 * Data Structures Helper
 * @package Atompulse\Component\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class DataFinder
{

    /**
     * Search in multidimensional array and return an array path of keys to the searched item
     * @param string $needle
     * @param array $haystack
     * @param string $needleKey
     * @param boolean $strict
     * @param array $path
     * @return mixed
     */
    public static function searchRecursive($needle, $haystack, $needleKey = "", $strict = false, $path = [])
    {
        if (!is_array($haystack)) {

            return false;
        }

        foreach ($haystack as $key => $val) {
            if (is_array($val) &&
                $subPath = self::searchRecursive($needle, $val, $needleKey, $strict, $path)
            ) {
                $path = array_merge($path, [$key], $subPath);

                return $path;
            } elseif ((!$strict && ($val == $needle || is_null($needle)) &&
                    $key == (strlen($needleKey) > 0 ? $needleKey : $key)) ||
                ($strict && $val === $needle &&
                    $key == (strlen($needleKey) > 0 ? $needleKey : $key))
            ) {
                $path[] = $key;

                return $path;
            }
        }

        return false;
    }

    /**
     * Given a path array structure (i.e. returned by searchRecursive) this function
     * retrieves the actual value from a target array (the returned value can be single or a complex structure)
     * @param array $pathKeys
     * @param array $source
     * @return mixed
     */
    public static function getFromPath($pathKeys, $source)
    {
        $mappedValue = $source;

        foreach ($pathKeys as $key) {
            if (isset($mappedValue[$key])) {
                $mappedValue = $mappedValue[$key];
            } else {

                return false;
            }
        }

        return $mappedValue;
    }

    /**
     * Given a path array structure (i.e. returned by searchRecursive) this function
     * retrieves the actual value from a target array (the returned value can be single or a complex structure)
     * @param array $pathKeys
     * @param array &$source
     * @return mixed
     */
    public static function removeFromPath($pathKeys, &$source)
    {
        $mappedValue = $source;

        foreach ($pathKeys as $key) {
            if (isset($mappedValue[$key])) {
                $mappedValue = $mappedValue[$key];
            } else {

                return false;
            }
        }

        return $mappedValue;
    }

}
