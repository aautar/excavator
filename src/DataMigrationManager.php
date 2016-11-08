<?php

namespace Excavator;

use Excavator\Artifact;
use Excavator\Output;

class DataMigrationManager
{
    /**
     * @var DataMigrator[]
     */
    protected $dataMigrators;

    public function __construct(array $databaseConnectionStrings, string $dbMigrationsFolder, Artifact $artifact)
    {
        foreach($databaseConnectionStrings as $cs) {
            $dm = new DataMigrator($cs, $dbMigrationsFolder, $artifact);
            $this->dataMigrators[] = $dm;
        }
    }

    public function checkConnections() : bool
    {
        foreach($this->dataMigrators as $dm) {
            $dm->checkDatabaseConnections();
        }

        return true;
    }

    public function executeMigrations() : bool
    {
        foreach($this->dataMigrators as $dm) {
            $scriptExecuted = $dm->executeMigration();
        }

        return true;
    }
}
