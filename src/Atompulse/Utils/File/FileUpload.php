<?php

namespace Atompulse\Utils\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * File Upload Trait
 * - for symfony2 controllers -
 *
 * Make sure file is not cached (as it happens for example on iOS devices)
 * ->header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 * ->header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 * ->header("Cache-Control: no-store, no-cache, must-revalidate");
 * ->header("Cache-Control: post-check=0, pre-check=0", false);
 * ->header("Pragma: no-cache");
 * @author Petru Cojocar
 */
trait FileUpload
{
    /**
     * @param Request $request
     * @param $fileNameParameter
     * @param $storePath
     * @return array
     * @throws \Exception
     */
    public function handleFileUpload(Request $request, $fileNameParameter, $storePath)
    {
        $fileName = $request->request->get('name');

        $filePath = $storePath . DIRECTORY_SEPARATOR . $fileName;

        $hasChunks = $request->request->has('chunks');

        if ($hasChunks) {
            $chunk = (int)$request->request->get('chunk', 0);
            $chunks = (int)$request->request->get('chunks', 1);
        } else {
            $chunk = 0;
            $chunks = 1;
        }

        $this->handleTemporaryCleanup($storePath, $filePath);

        if ($request->files->has($fileNameParameter)) {
            /** @var $uploadedFile UploadedFile */
            $uploadedFile = $request->files->get($fileNameParameter);
        } else {
            throw new \Exception("File [$fileNameParameter] is missing");
        }

        if ($uploadedFile->getError() || !$uploadedFile->isValid()) {
            throw new \Exception('Failed to move uploaded file');
        }

        // handle chunks as file parts
        if ($hasChunks) {
            // first chunk : we want to overwrite any existing file with the same name
            $flag = ($chunk == 0) ? null : FILE_APPEND;

            $success = file_put_contents("{$filePath}.part", file_get_contents($uploadedFile->getRealPath()), $flag);

            if ($success && ($chunk == ($chunks - 1))) {
                $progress = 100;
                $success = rename("{$filePath}.part", $filePath);
            }
            elseif ($success) {
                $progress = (100 / $chunks) * ($chunk+1);
            }
            else {
                $progress = 0;
            }
        }
        // one single file
        else {
            $success = file_put_contents($filePath, file_get_contents($uploadedFile->getRealPath()));
            $progress = $success ? 100 : 0;
        }

        $data = [
            'status' => (!$success ? $success : true),
            'progress' => $progress,
            'file' => $filePath,
            'file_name' => $fileName,
        ];

        return $data;
    }

    /**
     * Remove old temp files
     * @param $targetDir
     * @param $filePath
     * @return bool
     * @throws \Exception
     */
    protected function handleTemporaryCleanup($targetDir, $filePath)
    {
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
            throw new \Exception("Failed to open directory [$targetDir] when trying to cleanup temporary files");
        }

        while (($file = readdir($dir)) !== false) {
            $tmpFilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

            // If temp file is current file proceed to the next
            if ($tmpFilePath == "{$filePath}.part") {
                continue;
            }

            // Remove temp file if it is older than the max age and is not the current file
            if (preg_match('/\.part$/', $file) && (filemtime($tmpFilePath) < time() - $maxFileAge)) {
                @unlink($tmpFilePath);
            }
        }

        closedir($dir);

        return true;
    }

}


