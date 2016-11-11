<?php

namespace Excavator\Tests;

use Excavator\DataMigrator;
use Excavator\Artifact;
use Excavator\InvalidResourcePathException;

class DataMigratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDatabaseNameReturnsDBName()
    {
        $db = new DataMigrator(
                new \Excavator\ResourcePath("mysql://root:rootpass@localhost:3306/a_database_123"),
                $this->createMock(Artifact::class),
                "sql/script.sql"
            );
        
        $this->assertEquals("a_database_123", $db->getDatabaseName());
    }
}
