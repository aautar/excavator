<?php

namespace Excavator;

use Excavator\Artifact;
use \Exception;

class DataMigrator
{

    /**
     * @var array
     */
    protected $dbConnectionParams = [];

    /**
     * @var string
     */
    protected $dbMigrationsFolder;

    /**
     * @var Artifact
     */
    protected $artifact;

    /**
     * @param string $connectionString
     * @param string $dbMigrationsFolder
     * @param Artifact $artifact
     * @throws Exception
     */
    public function __construct(string $connectionString, string $dbMigrationsFolder, Artifact $artifact)
    {
        $urlParts = parse_url($connectionString);

        if($urlParts === false) {
            throw new Exception("Invalid connections string: " . $connectionString);
        }

        $this->dbConnectionParams[$connectionString] = $urlParts;
        $this->dbMigrationsFolder = $dbMigrationsFolder;
        $this->artifact = $artifact;
    }

    /**
     * 
     * @return string
     */
    public function getDatabaseName() : string
    {
        $connParams = reset($this->dbConnectionParams);
        return substr($connParams['path'],1);
    }


    /**
     *
     * @return bool
     * @throws Exception
     */
    public function checkDatabaseConnections() : bool
    {
        foreach($this->dbConnectionParams as $connString => $connParams) {
            if($connParams['scheme'] !== 'mysql') {
                throw new Exception("Unsupported database: " . $connString);
            }

            $dataLink = mysqli_connect($connParams['host'], $connParams['user'], $connParams['pass'], $this->getDatabaseName(), $connParams['port']);
            if(!$dataLink) {
                throw new Exception("Unable to connect to " . $connString);
            }
        }

        return true;
    }

    public function executeMigration() : bool
    {
        $script = $this->artifact->getDBMigrationScript($this->dbMigrationsFolder, $this->getDatabaseName());
        if($script === null) {
            return false;
        }

        $connParams = reset($this->dbConnectionParams);
        $dataLink = mysqli_connect($connParams['host'], $connParams['user'], $connParams['pass'], $this->getDatabaseName(), $connParams['port']);
        $executeOk = mysqli_multi_query($dataLink, $script);

        if($executeOk === false) {
            throw new Exception(mysqli_error($dataLink));
        }

        return true;
    }
}
