<?php

namespace Excavator\Tests;

use Excavator\DataMigrator;
use Excavator\Artifact;
use Excavator\InvalidResourcePathException;

class DataMigratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructThrowsExceptionForInvalidConnectionString()
    {
        $this->expectException(InvalidResourcePathException::class);
        new DataMigrator("bad-conn-string", "/sql", $this->createMock(Artifact::class));
    }

    public function testGetDatabaseNameReturnsDBName()
    {
        $db = new DataMigrator("mysql://root:rootpass@localhost:3306/a_database_123", "/sql", $this->createMock(Artifact::class));
        $this->assertEquals("a_database_123", $db->getDatabaseName());
    }
}
