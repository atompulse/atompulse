<?php

namespace Atompulse\Utils\File;

use Symfony\Component\HttpFoundation\Response;

trait OutputFile
{
    /**
     * Output file
     * @param $filename
     * @param $ext
     * @return Response
     * @throws
     */
    protected function outputFile($filename, $ext, $downloadFileName = false)
    {
        if ($filename) {

            if (file_exists($filename) && !is_dir($filename)) {
                $baseName = !$downloadFileName ? basename($filename) : $downloadFileName;
                $headers = [
                    'Content-Type' => "application/$ext",
                    'Content-Disposition' => 'attachment; filename="' . $baseName . '"',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                ];

                $response = new Response(file_get_contents($filename), 200, $headers);

                return $response;
            }
            else {

                $baseName = basename($filename);

                throw $this->createNotFoundException("File [$baseName] not found or not a valid file");
            }
        }
        else {
            throw $this->createNotFoundException("File not specified");
        }
    }

}