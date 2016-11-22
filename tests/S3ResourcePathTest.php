<?php

namespace Excavator\Tests;

use Excavator\S3ResourcePath;
use Excavator\InvalidResourcePathException;

class S3ResourcePathTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructThrowsInvalidResourcePathException()
    {
        $this->expectException(InvalidResourcePathException::class);
        new S3ResourcePath("s3://access:secret@region/");
    }

    public function testGetBucketReturnsBucket()
    {
        $s3ResourcePath = new S3ResourcePath("s3://access:secret@region.bucket/");
        $this->assertEquals("bucket", $s3ResourcePath->getBucket());
    }

    public function testGetBucketReturnsRegion()
    {
        $s3ResourcePath = new S3ResourcePath("s3://access:secret@region.bucket/");
        $this->assertEquals("region", $s3ResourcePath->getRegion());
    }
}
