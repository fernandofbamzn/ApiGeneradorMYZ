<?php
class DirectoryHelper
{
    const dir = '/volume1/StarWarsLegion';
    public function getFiles($allowed_extensions = [], $path = null)
{
    if ($path === null) {
        $path = $this::dir;
    }

    $files = [];    
    $dir = @opendir($path);

    if ($dir === false) {
        return $files;
    }

    while (($entry = readdir($dir)) !== false) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($fullPath)) {
            $files = array_merge($files, $this->getFiles($allowed_extensions, $fullPath));
        } else {
            $pathInfo = pathinfo($fullPath);

            if (!$allowed_extensions || (isset($pathInfo['extension']) && in_array($pathInfo['extension'], $allowed_extensions))) {
                $files[] = str_replace(DirectoryHelper::dir,'',$fullPath);
            }
        }
    }

    closedir($dir);
    return $files;
}



public function getImages($path = null)
{
    if ($path === null) {
        $path = $this::dir;
    }

    $imageList = [];
    $dir = @opendir($path);

    if ($dir === false) {
        return $imageList;
    }

    while (($entry = readdir($dir)) !== false) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($fullPath)) {
            $imageList = array_merge($imageList, $this->getImages($fullPath));
        } else {
            if ($this->isImage($fullPath)) {
                $imageList[] = str_replace(DirectoryHelper::dir,'',$fullPath);
            }
        }
    }

    closedir($dir);
    return $imageList;
}



    public function isImage($file)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $fileInfo = pathinfo($file);

        return isset($fileInfo['extension']) && in_array(strtolower($fileInfo['extension']), $imageExtensions);
    }
}