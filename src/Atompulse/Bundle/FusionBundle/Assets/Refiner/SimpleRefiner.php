<?php
namespace Atompulse\Bundle\FusionBundle\Assets\Refiner;

/**
 * Class SimpleRefiner
 * @package Atompulse\Bundle\FusionBundle\Compiler\Refiner
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class SimpleRefiner implements RefinerInterface
{
    /**
     * Very basic content optimizer
     * @param string $content
     * @return mixed|string
     */
    public static function refine($content)
    {
        // remove comments
        $content = preg_replace('$\/\*[\s\S]*?\*\/$', '', $content);
        //$content = preg_replace('/[ \t]*(?:\/\*(?:.(?!(?<=\*)\/))*\*\/|\/\/[^\n\r]*\n?\r?)/', '', $content);
        $content = preg_replace('$(?<=\s|\w)[\/]{2,}.*$', '', $content);

        // remove tabs, spaces, newlines, etc.
        $content = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $content);
        // remove special language constructs
        $content = str_replace([" : ",": ",', ',' (', ' = ',' || ',' ? ',') {'], [':', ':',',','(','=','||','?','){'], $content);

        return $content;
    }
}