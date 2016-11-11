<?php

namespace Excavator;

class S3ResourcePath extends ResourcePath
{
    /**
     * @var string
     */
    protected $region;

    /**
     * @return string
     */
    public function getRegion() : string
    {
        return $this->region;
    }

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @return string
     */
    public function getBucket() : string
    {
        return $this->bucket;
    }

    /**
     * @param string $path
     * @throws InvalidResourcePathException
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        $host = $this->getHost();
        $hostParts = explode('.', $host);

        if(count($hostParts) !== 2) {
            throw new InvalidResourcePathException("Incorrect host for S3 path");
        }

        $this->region = $hostParts[0];
        $this->bucket = $hostParts[1];
    }
}
