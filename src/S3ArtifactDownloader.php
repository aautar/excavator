<?php

namespace Excavator;

use Aws\S3\S3Client;

class S3ArtifactDownloader
{
    /**
     * @var S3Client 
     */
    protected $s3Client;

    /**
     * @param S3Client $s3Client
     */
    public function __construct(S3Client $s3Client)
    {
        $this->s3Client = $s3Client;
    }

    /**
     * @param string $bucket
     * @param string $artifactZip
     * @return string
     */
    public function download(string $bucket, string $artifactZip, string $versionTag) : Artifact
    {
        $saveToFilename = tempnam(sys_get_temp_dir(), 'excavator-artifact-');
        $result = $this->s3Client->getObject([
            'Bucket' => $bucket,
            'Key' => $artifactZip,
            'SaveAs' => $saveToFilename
        ]);

        return new Artifact($saveToFilename, $versionTag);
    }
}
