<?php
namespace Pico;

class PicoDatabaseCredentials
{
	private $driver = 'mysql';
	private $host = 'localhost';
	private $port = 3306;
	private $username = "";
	private $password = "";
	private $databaseName = "";
	private $timeZone = "Asia/Jakarta";

	/**
	 * Constructor
	 * @param string $driver Driver
	 * @param string $host Server host
	 * @param int $port Server port
	 * @param string $username Database username
	 * @param string $password Database user password
	 * @param string $databaseName Database name
	 * @param string $timeZone Application time zone
	 */
	public function __construct($driver = null, $host = null, $port = 0, $username = null, $password = null, $databaseName = null, $timeZone = null)
	{
		if($driver != null)
		{
			$this->driver = $driver;
		}
		if($host != null)
		{
			$this->host = $host;
		}
		if($port != 0)
		{
			$this->port = $port;
		}
		if($username != null)
		{
			$this->username = $username;
		}
		if($password != null)
		{
			$this->password = $password;
		}
		if($databaseName != null)
		{
			$this->databaseName = $databaseName;
		}
		if($timeZone != null)
		{
			$this->timeZone = $timeZone;
		}	
	}

	/**
	 * Load ini file
	 * @param string $path Configuration path
	 * @return \Pico\PicoDatabaseCredentials
	 */
	public function load($path)
	{
		$obj = parse_ini_file($path);
		if($obj['timezone_system'])
		{
			$timeZone = ini_get('date.timezone');
			if(!empty($timeZone))
			{
				$obj['timezone'] = $timeZone;
			}
		}
		$this->driver = $obj['driver'];
		$this->host = $obj['host'];
		$this->port = $obj['port'];
		$this->username = $obj['username'];
		$this->password = $obj['password'];
		$this->databaseName = $obj['database_name'];
		$this->timeZone = $obj['timezone'];	
		return $this;
	}

	/**
	 * Get driver
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Get server host
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get server port
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Get the value of username
	 */ 
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Get the value of password
	 */ 
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Get the value of databaseName
	 */ 
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	

	/**
	 * Get the value of timeZone
	 */ 
	public function getTimeZone()
	{
		return $this->timeZone;
	}
}
