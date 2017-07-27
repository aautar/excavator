<?php

namespace Excavator;

class ResourcePath
{
    /**
     * @var string
     */
    protected $scheme;

    public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
     * @var string
     */
    protected $host;

    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @var string
     */
    protected $user;

    public function getUser() : string
    {
        return $this->user;
    }

    /**
     * @var string
     */
    protected $pass;

    public function getPass() : string
    {
        return $this->pass;
    }

    /**
     * @var int
     */
    protected $port;

    public function getPort() : int
    {
        return $this->port;
    }

    /**
     * @var string
     */
    protected $path;

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     *
     * @param string $path
     * @throws InvalidResourcePathException
     */
    public function __construct(string $path)
    {
        $urlParts = parse_url($path);

        if($urlParts === false ||
           !isset($urlParts['scheme']) ||
           !isset($urlParts['host']) ||
           !isset($urlParts['user']) ||
           !isset($urlParts['pass']))
        {
            throw new InvalidResourcePathException("Invalid resource path string: " . $path);
        }

        $this->scheme = urldecode($urlParts['scheme']);
        $this->host = urldecode($urlParts['host']);
        $this->user = urldecode($urlParts['user']);
        $this->pass = urldecode($urlParts['pass']);
        $this->port = (int)($urlParts['port'] ?? 0);
        
        $this->path = $urlParts['path'] ?? "";
        $this->path = urldecode(trim($this->path, "/"));
    }
}
