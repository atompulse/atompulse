<?php

namespace Atompulse\Utils\Csv;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Atompulse\Utils\Cache;

/**
 * Class Csv File Generator
 * @package Atompulse\Utils
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Generator
{
    /**
     * Service container
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;
    protected $fileWriter = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->fileWriter = new Cache\FileWriter($this->container);
    }

    /**
     * Generate a CSV file from the given input
     * @param $csvData
     * @param $outputFile
     * @return bool
     * @throws \Exception
     */
    public function generate($csvData, $outputFile)
    {
        $tempCsvFileLocation = $this->fileWriter->getFilePath($outputFile);

        $fp = fopen($tempCsvFileLocation, 'w');

        if ($fp) {
            foreach ($csvData as $line) {
                fputcsv($fp, $line);
            }
            fclose($fp);
        } else {
            throw new \Exception("Temporary output file [$tempCsvFileLocation] couldn't be created.");
        }

        return $tempCsvFileLocation;
    }

}