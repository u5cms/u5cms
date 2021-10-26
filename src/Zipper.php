<?php

namespace U5cms;

class Zipper
{
    private $zip;
    private $open = false;
    private $stripPath = '';

    public function __construct(string $filename) {
        $this->zip = new \ZipArchive();
        if ($this->zip->open($filename, \ZipArchive::CREATE) !== true) {
            echo "Cannot open file for writing: " . $filename;
        }
        $this->open = true;
    }

    public function isOpen() {
        return $this->open;
    }

    public function addFolder(string $folder) {
        if (!$this->isOpen()) {
            throw new \Exception('Cannot add folders to the zip file as the zip file is not open');
        }

        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            // Check for local/parent path or zipping file itself and skip
            if ($f == '.' || $f == '..' || $f == basename(__FILE__)) {
                continue;
            } else {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip
                $localPath = str_replace($this->getStripPath(), '', $filePath);
                if (is_file($filePath)) {
                    $this->zip->addFile($filePath, $localPath);
                } else if (is_dir($filePath)) {
                    // Add and empty sub-directory and recursive call ourself
                    $this->zip->addEmptyDir($localPath);
                    $this->addFolder($filePath);
                }
            }
        }
        closedir($handle);
    }

    public function addFile(string $filePath) {
        if (!$this->isOpen()) {
            throw new \Exception('Cannot add files to the zip file as the zip file is not open');
        }

        // Remove prefix from file path before add to zip
        $localPath = str_replace($this->getStripPath(), '', $filePath);
        if (file_exists($filePath)) {
            $this->zip->addFile($filePath, $localPath);
        }
    }

    public function setStripPath(string $stripPath) {
        if (substr($stripPath, -1) != DIRECTORY_SEPARATOR) {
            $stripPath = $stripPath . DIRECTORY_SEPARATOR;
        }
        $this->stripPath = $stripPath;
    }

    public function getStripPath() {
        return $this->stripPath;
    }

    public function write() {
        $this->zip->close();
        $this->open = false;
    }
}
