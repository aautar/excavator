<?php

namespace Excavator;

use \Exception;
use \ZipArchive;

class Artifact
{
    protected $artifactZipFile;

    protected $versionFile;

    protected $zip;

    public function __construct(string $artifactZipFile, string $versionFile)
    {
        $this->artifactZipFile = $artifactZipFile;
        $this->versionFile = $versionFile;

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
        $tempDest = sys_get_temp_dir() . "/";
        $this->zip->extractTo($tempDest, $this->versionFile);

        return trim(file_get_contents($tempDest . $this->versionFile));
    }

    public function getDBMigrationScript(string $migrationFolder, string $databaseName)
    {
        $versionTag = $this->getVersionTag();
        $tempDest = sys_get_temp_dir() . "/";
        $extractOk = $this->zip->extractTo($tempDest,  $migrationFolder . "sheets.sql" /*$databaseName . "-migration-{$versionTag}.sql"*/);

        if($extractOk === false) {
            return null;
        }

        return file_get_contents($tempDest . $migrationFolder . "sheets.sql" /*$databaseName . "-migration-{$versionTag}.sql"*/);
    }
}
