<?php

namespace Excavator;

use \Exception;

class DataMigrator
{

    /**
     * @var array
     */
    protected $dbConnectionParams = [];

    /**
     * @param string $connectionString
     */
    public function __construct(string $connectionString)
    {
        $urlParts = parse_url($connectionString);

        if($urlParts === false) {
            throw new Exception("Invalid connections string: " . $connectionString);
        }

        $this->dbConnectionParams[$connectionString] = $urlParts;
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

            $dataLink = mysqli_connect($connParams['host'], $connParams['user'], $connParams['pass'], substr($connParams['path'],1), $connParams['port']);
            if(!$dataLink) {
                throw new Exception("Unable to connect to " . $connString);
            }
        }

        return true;
    }
}
