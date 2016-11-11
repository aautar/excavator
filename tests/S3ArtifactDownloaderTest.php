<?php

namespace Excavator\Tests;

use Aws\S3\S3Client;
use phpmock\phpunit\PHPMock;
use Excavator\S3ArtifactDownloader;

class S3ArtifactDownloaderTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    public function testDownloadCallsGetObject()
    {
        $getTempDir = $this->getFunctionMock("Excavator", "sys_get_temp_dir");
        $getTempDir->expects($this->any())->willReturn("/tempy");

        $getTempName = $this->getFunctionMock("Excavator", "tempnam");
        $getTempName->expects($this->any())->willReturn("/tempy/excavator-artifact-3243.temp");

        $s3Client = $this->createMock(S3Client::class);
        $downloader = new S3ArtifactDownloader($s3Client);

        $s3Client->expects($this->once())
                ->method('__call')
                ->with(
                        $this->equalTo('getObject'),
                        [[
                            'Bucket' => "bucket",
                            'Key' => "artifact.zip",
                            'SaveAs' => "/tempy/excavator-artifact-3243.temp"
                        ]]
                );
       
        $downloader->download("bucket", "artifact.zip", "version.txt");

    }
}