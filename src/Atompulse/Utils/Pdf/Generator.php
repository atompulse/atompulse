<?php

namespace Atompulse\Utils\Pdf;

use Atompulse\Utils\Cache\FileWriterInterface;
use Atompulse\Utils\System\Command;

use Psr\Log\LoggerInterface;
use Twig_Environment;

/**
 * Class Generator
 * PDF Generator using wkhtmltopdf
 *
 * @important wkhtmltopdf binary must accessible system wide
 *
 * @package Atompulse\Utils\Pdf
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class Generator
{
    /**
     * @var FileWriterInterface
     */
    protected $fileWriter = null;

    /**
     * @var Twig_Environment
     */
    protected $twig = null;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @var string
     */
    protected $options = '-s A4 -O Portrait';

    /**
     * @var string
     */
    protected $processedContent = null;

    /**
     * Generator constructor.
     * @param FileWriterInterface $cache
     * @param Twig_Environment $twig
     */
    public function __construct(FileWriterInterface $cache, Twig_Environment $twig, LoggerInterface $logger = null)
    {
        $this->fileWriter = $cache;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * wkhtmltopdf CLI options
     * @param string $options
     */
    public function setOptions(string $options)
    {
        $this->options = $options;
    }

    /**
     * Get the processed HTML content that will be used for PDF generation
     * @return string
     */
    public function getProcessedContent()
    {
        return $this->processedContent;
    }

    /**
     * Generate the PDF file
     * @param string $template
     * @param array $params
     * @param string $outputFile
     * @param bool $autoRemoveTemp
     * @return bool
     * @throws \Exception
     */
    public function generate(string $template, array $params, string $outputFile, bool $autoRemoveTemp = true)
    {
        // process content
        $this->processedContent = $this->processTemplate($template, $params);

        // temporary HTML input file
        $uniqueTmpFileName = time() . rand(1000, 9999) . rand(1000, 9999) . ".html";
        $tempHtmlFileLocation = $this->fileWriter->getFilePath($uniqueTmpFileName);

        if ($this->fileWriter->writeToCache($uniqueTmpFileName, $this->processedContent)) {

            $success = !(bool)$this->build($tempHtmlFileLocation, $outputFile, $this->options);

            if ($autoRemoveTemp) {
                $this->fileWriter->removeCache($uniqueTmpFileName);
            }

            return $success;
        } else {
            throw new \Exception("Temporary html input file [$tempHtmlFileLocation] could not be created.");
        }
    }

    /**
     * Process the template using the given params
     * @param string $template
     * @param array $params
     * @return string
     */
    protected function processTemplate(string $template, array $params)
    {
        return $this->twig->render($template, $params);
    }

    /**
     * Run wkhtmltopdf with given parameters
     * @param string $inputHtmlFile
     * @param string $outputPdfFile
     * @param string $options
     * @return mixed
     */
    protected function build(string $inputHtmlFile, string $outputPdfFile, string $options)
    {
        $cmd = "wkhtmltopdf $options $inputHtmlFile $outputPdfFile";

        $this->logger->debug("[Pdf\Generator] prepared command [$cmd]");

        $result = Command::pipeExec($cmd);

        $this->logger->debug("[Pdf\Generator] output \n" . var_export($result, true));

        return $result['return'];
    }
}