<?php

namespace Atompulse\Utils\System;

/**
 * Class Command
 * @package Atompulse\Utils\System
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Command
{
    /**
     * @param string $cmd
     * @param string $input
     * @param bool $cwd
     * @return array
     */
    public static function pipeExec(string $cmd, string $input = '', bool $cwd = false)
    {
        $specs = [['pipe','r'], ['pipe','w'], ['pipe','w']];
        $pipes = [];

        // open process
        $proc = proc_open($cmd, $specs, $pipes, $cwd ? $cwd : null);

        // write input if any
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        // get output
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        /**
         * @var int $terminationStatus
         * @link http://php.net/manual/en/function.proc-close.php
         */
        $terminationStatus = proc_close($proc);

        $status = [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'return' => $terminationStatus,
        ];

        return $status;
    }
}
