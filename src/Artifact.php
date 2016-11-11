<?php

namespace Excavator;

use \Exception;
use \ZipArchive;

class Artifact
{
    protected $artifactZipFile;

    protected $versionTag;

    protected $zip;

    public function __construct(string $artifactZipFile, string $versionTag)
    {
        $this->artifactZipFile = $artifactZipFile;
        $this->versionTag = $versionTag;

        $this->zip = new ZipArchive();
        $res = $this->zip->open($this->artifactZipFile);
        if(!$res) {
            throw new Exception("Failed to open " . $this->artifactZipFile . ".");
        }
    }

    public function cleanup()
    {
        $this->zip->close();
    }

    public function unzipAll(string $destinationFolder)
    {
        $this->zip->extractTo($destinationFolder);
    }

    public function getVersionTag() : string
    {
        return $this->versionTag;
    }

    public function getDBMigrationScript(string $scriptPath)
    {
        $versionTag = $this->getVersionTag();
        $tempDest = sys_get_temp_dir() . "/";
        $extractOk = $this->zip->extractTo($tempDest,  $scriptPath);

        if($extractOk === false) {
            return null;
        }

        return file_get_contents($tempDest . $scriptPath);
    }
}
