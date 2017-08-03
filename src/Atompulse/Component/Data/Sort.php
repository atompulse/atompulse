<?php

namespace Atompulse\Component\Data;

/**
 * Class Sort
 * Sort complex array structures by inner data
 * @package Atompulse\Component\Data
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Sort
{
    /**
     * Optimized callback ready bubble sort
     * @param array $list
     * @param callable|boolean $callback
     * @return array
     */
    public static function byBubbleCallback($list, $callback = false)
    {
        $size = count($list);
        $sortedList = $list;

        if ($size > 0) {
            $normalizedList = [];
            // normalize list
            foreach ($list as $item) {
                $normalizedList[] = $item;
            }
            // optimized bubble sort
            while ($size > 0) {
                $newSize = 0;
                for ($i = 1; $i <= $size-1; $i++) {
                    // compare: a > b using callback
                    if ($callback) {
                        $swap = call_user_func_array($callback, ['a' => $normalizedList[$i-1], 'b' => $normalizedList[$i]]);
                    } else {
                        $swap = $normalizedList[$i-1] > $normalizedList[$i];
                    }
                    if ($swap) {
                        $itemA = $normalizedList[$i-1];
                        $itemB = $normalizedList[$i];
                        $normalizedList[$i-1] = $itemB;
                        $normalizedList[$i] = $itemA;
                        // swap
                        $newSize = $i;
                    }
                }
                $size = $newSize;
            }

            $sortedList = $normalizedList;
        }

        return $sortedList;
    }

    /**
     * @param $data
     * @param $sortCriteria ['field1' => [SORT_DESC, SORT_NUMERIC], 'field3' => [SORT_DESC, SORT_NUMERIC]]
     * @param bool|true $caseInSensitive
     * @return bool|mixed
     */
    public static function multiSort($data, $sortCriteria, $caseInSensitive = true)
    {
        if (!is_array($data) || !is_array($sortCriteria)) {
            return false;
        }
        $args = [];
        $i = 0;

        foreach ($sortCriteria as $sortColumn => $sortAttributes) {
            if (strpos($sortColumn, '.') !== false) {
                $pathKeys = explode('.', $sortColumn);
                $colLists = [];
                foreach ($data as $key => $row) {
                    $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
                    $rowData = DataFinder::getFromPath($pathKeys, $row);
                    $rowData = $convertToLower ? strtolower($rowData) : $rowData;
                    $colLists[$sortColumn][$key] = $rowData;
                }
                $args[] = &$colLists[$sortColumn];

                foreach ($sortAttributes as $sortAttribute) {
                    $tmp[$i] = $sortAttribute;
                    $args[] = &$tmp[$i];
                    $i++;
                }
            } else {
                $colLists = [];
                foreach ($data as $key => $row) {
                    $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
                    $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
                    $colLists[$sortColumn][$key] = $rowData;
                }
                $args[] = &$colLists[$sortColumn];

                foreach ($sortAttributes as $sortAttribute) {
                    $tmp[$i] = $sortAttribute;
                    $args[] = &$tmp[$i];
                    $i++;
                }
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return end($args);
    }

}
