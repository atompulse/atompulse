<?php

namespace Atompulse\Utils;

/**
 * Trait StringUtils
 * @package Atompulse\Utils
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
trait StringUtils
{
    /**
     * @param $jsQualifiedName
     * @return array
     */
    public function toNamespaceComponents($jsQualifiedName)
    {
         $parts = explode('.', $jsQualifiedName);
         $nsComponents = ['namespace' => $parts[0], 'name' => $parts[1]];

         return $nsComponents;
    }

    /**
     * Transform an arrray's keys from camelCase to under_score
     * @param array $data
     * @return array
     */
    public function fromCamelToUnder($data)
    {
        $transformed = [];
        foreach ($data as $camelKey => $value) {
            $transformed[self::unCamelize($camelKey)] = $value;
        }

        return $transformed;
    }

    /**
     * Transform a string from camelCase to under_score
     * @param string $string
     * @return string
     */
    public function unCamelize($string)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string));
    }

    /**
     * @param $string
     * @return string
     */
    public function classify($string)
    {
        $strings = explode('.', str_replace(['_','-'], '.', $string));

        $camelString = '';
        foreach ($strings as $str) {
            $camelString.= ucfirst($str);
        }

        return $camelString;
    }

    /**
     * @param $qualifiedControllerName
     * @return mixed
     */
    public function getControllerName($qualifiedControllerName)
    {
        $nsComponents = explode('\\', $qualifiedControllerName);
        $ctrlName = str_replace('Controller', '', end($nsComponents));

        return $ctrlName;
    }

}