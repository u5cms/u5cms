<?php
namespace U5cms;

class Zipper
{
    private $zip;
    private $open=false;
    private $stripPath='';
    private $filename;
    private $temporary;
    private $activeFolders=[];

    public function __construct(string $filename) {
        if (!class_exists('ZipArchive')) throw new \RuntimeException('The PHP zip extension is required for file backups');
        $this->filename=$filename;
        $directory=realpath(dirname($filename));
        if ($directory===false) throw new \RuntimeException('Backup directory does not exist');
        $this->temporary=tempnam($directory,'u5zip_');
        if ($this->temporary===false || dirname($this->temporary)!==$directory) {
            if ($this->temporary!==false) @unlink($this->temporary);
            throw new \RuntimeException('Cannot create temporary backup');
        }
        $this->zip=new \ZipArchive();
        if ($this->zip->open($this->temporary,\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true) {
            @unlink($this->temporary);
            throw new \RuntimeException('Cannot open backup for writing');
        }
        $this->open=true;
    }

    public function __destruct() {
        if ($this->open) $this->zip->close();
        if ($this->temporary && is_file($this->temporary)) @unlink($this->temporary);
    }

    public function isOpen() { return $this->open; }

    private function localPath(string $path) {
        if ($this->stripPath!=='') {
            if (strpos($path,$this->stripPath)!==0) throw new \RuntimeException('Backup path is outside its configured root');
            $path=substr($path,strlen($this->stripPath));
        } else $path=ltrim($path,'/\\');
        $path=str_replace('\\','/',$path);
        if ($path==='' || in_array('..',explode('/',$path),true)) throw new \RuntimeException('Invalid backup member path');
        return $path;
    }

    public function addFolder(string $folder) {
        if (!$this->open) throw new \RuntimeException('Cannot add folders to a closed backup');
        $real=realpath($folder);
        if ($real===false || isset($this->activeFolders[$real])) throw new \RuntimeException('Missing folder or directory cycle in backup');
        $handle=@opendir($folder);
        if ($handle===false) throw new \RuntimeException('Cannot read backup folder');
        $this->activeFolders[$real]=true;
        try {
            while (false!==($entry=readdir($handle))) {
                if ($entry==='.' || $entry==='..') continue;
                $file=$folder.DIRECTORY_SEPARATOR.$entry;
                if ($file===$this->filename || realpath($file)===$this->temporary || (is_file($this->filename) && realpath($file)===realpath($this->filename))) continue;
                if (is_file($file)) $this->addFile($file);
                elseif (is_dir($file)) {
                    if (!$this->zip->addEmptyDir($this->localPath($file))) throw new \RuntimeException('Cannot add backup directory');
                    $this->addFolder($file);
                }
            }
        } finally {
            unset($this->activeFolders[$real]);
            closedir($handle);
        }
    }

    public function addFile(string $file) {
        if (!$this->open) throw new \RuntimeException('Cannot add files to a closed backup');
        if (!is_file($file) || !$this->zip->addFile($file,$this->localPath($file))) throw new \RuntimeException('Cannot add backup file');
    }

    public function setStripPath(string $path) { $this->stripPath=rtrim($path,'/\\').DIRECTORY_SEPARATOR; }
    public function getStripPath() { return $this->stripPath; }

    public function write() {
        if (!$this->open) throw new \RuntimeException('Backup is already closed');
        $ok=$this->zip->close();
        $this->open=false;
        if (!$ok) throw new \RuntimeException('Cannot finish backup');
        $mode=is_file($this->filename) ? (fileperms($this->filename)&0777) : (0666&~umask());
        if (!chmod($this->temporary,$mode) || !rename($this->temporary,$this->filename)) throw new \RuntimeException('Cannot publish backup; the previous backup has been kept');
        $this->temporary=null;
        clearstatcache(true,$this->filename);
    }
}
