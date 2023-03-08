<?php
namespace WS;

class WSRemoteConnection {
    
    private $host = "";
    private $port = 0;

    /**
     * Constructor of \WS\WSRemoteConnection
     *
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}