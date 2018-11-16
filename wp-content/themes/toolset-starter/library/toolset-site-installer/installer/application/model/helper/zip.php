<?php

class TT_Helper_Zip extends ZipArchive
{

    protected $xml_file_content;

    public function __construct($path)
    {
        if ($this->open($path) !== true) {
            throw new \Exception('Zip file not found. ' . $path);
        }
    }

    public function fetchFileByExtension($extension)
    {
        for ($i = 0; $i < $this->numFiles; $i++) {
            if (strpos($this->getNameIndex($i), $extension) === false) {
                continue;
            }

            return $this->getFromName($this->getNameIndex($i));
        }

        return false;
    }

    public function fetchFilesByExtension($extension)
    {
        $files = array();
        for ($i = 0; $i < $this->numFiles; $i++) {
            if (strpos($this->getNameIndex($i), $extension) === false) {
                continue;
            }

            $files[] = $this->getFromName($this->getNameIndex($i));
        }

        return ! empty($files) ? $files : false;
    }
}