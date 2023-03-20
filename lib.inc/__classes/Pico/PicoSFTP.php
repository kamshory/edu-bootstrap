<?php
namespace Pico;
class PicoSFTP {
    /**
     * Host
     *
     * @var string
     */
    private $host = 'localhost';
    /**
     * Port
     *
     * @var integer
     */
    private $port = 22;
    /**
     * Username
     *
     * @var string
     */
    private $username = 'root';
    /**
     * Password
     *
     * @var string
     */
    private $password = 'centos';
    
    /**
     * Constructor
     *
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host = 'localhost', $port = 22, $username = 'root', $password = 'centos')
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Load configuration file
     *
     * @param string $path
     * @return \Pico\PicoSFTP
     */
    public function load($path)
    {
        $obj = parse_ini_file($path);
		$this->host = $obj['host'];
		$this->port = $obj['port'];
		$this->username = $obj['username'];
		$this->password = $obj['password'];
		return $this;
    }

    

    /**
     * Get host
     *
     * @return  string
     */ 
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get port
     *
     * @return  integer
     */ 
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get username
     *
     * @return  string
     */ 
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get password
     *
     * @return  string
     */ 
    public function getPassword()
    {
        return $this->password;
    }
}