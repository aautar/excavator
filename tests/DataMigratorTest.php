<?php

namespace Excavator\Tests;

use Excavator\DataMigrator;
use Excavator\Artifact;
use Excavator\InvalidConnectionStringException;

class DataMigratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructThrowsExceptionForInvalidConnectionString()
    {
        $this->expectException(InvalidConnectionStringException::class);
        new DataMigrator("bad-conn-string", "/sql", $this->createMock(Artifact::class));
    }
}
