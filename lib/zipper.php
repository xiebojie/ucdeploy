<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class zipper
{

    /**
     * Add files and sub-directories in a folder to zip file. 
     * @param string $folder 
     * @param ZipArchive $zipFile 
     * @param int $exclusiveLength Number of text to be exclusived from the file path. 
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        if(!file_exists($folder))
        {
            return false;
        }
        $handle = opendir($folder);
        while (false !== $f = readdir($handle))
        {
            if ($f != '.' && $f != '..')
            {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip. 
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath))
                {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath))
                {
                    $zipFile->addEmptyDir($localPath);// Add sub-directory. 
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    /**
     *
     *::zipDir('/path/to/sourceDir', '/path/to/out.zip'); 
     * 
     * @param string $sourcePath Path of directory to be zip. 
     * @param string $outZipPath Path of output zip file. 
     */
    public static function zipdir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $zip = new ZipArchive();
        $zip->open($outZipPath, ZIPARCHIVE::CREATE);
        $zip->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $zip, strlen("$parentPath/"));
        $zip->close();
    }
    
    public static function extract($source, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($source) === true)
        {
            $ret = $zip->extractTo($destination);
            $zip->close();
            return $ret;
        }
        return false;
    }
}