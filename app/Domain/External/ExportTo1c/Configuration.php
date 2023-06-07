<?php

namespace App\Domain\External\ExportTo1c;

class Configuration
{
    private static Configuration $defaultConfiguration;

    /** @var string The host */
    protected string $host = 'https://srvr.rubezh.ru/EDS_MP/ws/ADE.1cws?wsdl';
    /** @var string The logger channel name */
    protected string $loggerName = 'exchange_1c';

    /**
     * @param string $host
     * @param string $auth
     */
    public function __construct(string $host, string $auth)
    {
        $this->host = $host;
        $this->auth = $auth;
    }

    /**
     * @return string
     */
    public function getLoggerName(): string
    {
        return $this->loggerName;
    }

    /**
     * @param string $loggerName
     */
    public function setLoggerName(string $loggerName): void
    {
        $this->loggerName = $loggerName;
    }

    /** @var string auth data */
    protected string $auth ;

    /**
     * @return string
     */
    public function getAuth(): string
    {
        return $this->auth;
    }

    /**
     * @param string $auth
     */
    public function setAuth(string $auth): void
    {
        $this->auth = $auth;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
