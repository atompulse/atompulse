<?php

namespace Atompulse\Component\Data\Delta;

/**
 * Class ArrayDelta
 * @package Atompulse\Component\Data\Delta
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class ArrayDelta
{
    protected $a = [];
    protected $b = [];

    public function __construct(array $a, array $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function __invoke()
    {
        return $this->diff($this->a, $this->b);
    }

    /**
     * Compute the difference of 2 arrays - multi dimensions supported
     * @param array $a
     * @param array $b
     * @return array
     */
    protected function compare(array $a, array $b) : ArrayDelta
    {
        $delta = [];

        foreach ($a as $key => $value) {
            if (array_key_exists($key, $b)) {
                if (is_array($value)) {
                    $deltaX = self::compare($value, $b[$key]);
                    if (count($deltaX)) {
                        $delta[$key] = $deltaX;
                    }
                } else {
                    if ($value != $b[$key]) {
                        $delta[$key] = $value;
                    }
                }
            } else {
                $delta[$key] = $value;
            }
        }

        return $delta;
    }

    protected function diff($a, $b)
    {
        $delta = [];

        foreach ($a as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($b[$key])) {
                    $delta[$key] = $value;
                } else {
                    $deltaX = self::diff($value, $b[$key]);
                    if (!empty($deltaX)) {
                        $delta[$key] = $deltaX;
                    }
                }
            } else {
                if (!array_key_exists($key, $b) || $b[$key] !== $value) {
                    $delta[$key] = $value;
                }
            }
        }

        return $delta;
    }
}
