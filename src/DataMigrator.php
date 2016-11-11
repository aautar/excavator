<?php

namespace Excavator;

use Excavator\Artifact;
use Excavator\ResourcePath;

class DataMigrator
{

    /**
     * @var ResourcePath
     */
    protected $dbConnectionPath;

    /**
     * @var string
     */
    protected $sqlScriptPath;

    /**
     * @var Artifact
     */
    protected $artifact;

    public function __construct(ResourcePath $connectionPath, Artifact $artifact, string $sqlScriptPath)
    {
        $this->dbConnectionPath = $connectionPath;
        $this->artifact = $artifact;
        $this->sqlScriptPath = $sqlScriptPath;
    }

    /**
     * 
     * @return string
     */
    public function getDatabaseName() : string
    {
        return $this->dbConnectionPath->getPath();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function checkDatabaseConnection() : bool
    {
        if($this->dbConnectionPath->getScheme() !== 'mysql') {
            throw new Exception("Unsupported database: " . $connString);
        }

        $dataLink = mysqli_connect(
                $this->dbConnectionPath->getHost(),
                $this->dbConnectionPath->getUser(),
                $this->dbConnectionPath->getPass(),
                $this->getDatabaseName(),
                $this->dbConnectionPath->getPort() );
        
        if(!$dataLink) {
            throw new \Exception("Unable to connect to " . $connString);
        }

        return true;
    }

    public function executeMigration() : bool
    {
        $script = $this->artifact->getDBMigrationScript($this->sqlScriptPath);
        if($script === null) {
            return false;
        }

        $dataLink = mysqli_connect(
                $this->dbConnectionPath->getHost(),
                $this->dbConnectionPath->getUser(),
                $this->dbConnectionPath->getPass(),
                $this->getDatabaseName(),
                $this->dbConnectionPath->getPort() );

        $executeOk = mysqli_multi_query($dataLink, $script);
        if($executeOk === false) {
            throw new Exception(mysqli_error($dataLink));
        }

        return true;
    }
}
