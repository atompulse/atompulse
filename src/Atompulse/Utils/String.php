<?php
namespace AtomPulse\FusionBundle\Fusion\Traits\Utils;

/**
 * String Utility
 *
 * @author Petru Cojocar
 */
trait String
{
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


    public function classify($string)
    {
        $strings = explode('.', str_replace(['_','-'], '.', $string));

        $camelString = '';
        foreach ($strings as $str) {
            $camelString.= ucfirst($str);
        }

        return $camelString;
    }

    public function getControllerName($qualifiedControllerName)
    {
        $nsComponents = explode('\\', $qualifiedControllerName);
        $ctrlName = str_replace('Controller', '', end($nsComponents));

        return $ctrlName;
    }

}