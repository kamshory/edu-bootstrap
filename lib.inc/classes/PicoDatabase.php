<?php

class PicoDatabaseServer
{
	private $driver = 'mysql';
	private $host = 'localhost';
	private $port = 3306;
	
	public function __construct($driver, $host, $port)
	{
		$this->driver = $driver;
		$this->host = $host;
		$this->port = $port;
	}
	public function getDriver()
	{
		return $this->driver;
	}
	public function getHost()
	{
		return $this->host;
	}
	public function getPort()
	{
		return $this->port;
	}
	
}

class PicoDatabaseSyncConfig
{
	private string $applicationDir = '';
	private string $baseDir = '';
	private string $poolName = '';
	private string $rollingPrefix = '';
	private string $extension = '';
	private int $maximumlength = 1000000;
	private string $delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';
	const NEW_LINE = "\r\n";

	/**
	 * Constructor of PicoDatabaseSyncConfig
	 * @param string $applicationDir Base directory of sync file
	 * @param string $baseDir Base directory of sync file
	 * @param string $poolName Pooling file name
	 * @param string $rollingPrefix Rolling prefix file name
	 * @param string $extension File extension
	 * @param int $maximumlength Maximum length of sync file
	 * @param string $delimiter Extra query delimiter
	 */
	public function __construct($applicationDir, $baseDir, $poolName, $rollingPrefix, $extension, $maximumlength, $delimiter)
	{
		$this->applicationDir = $applicationDir;
		$this->baseDir = $baseDir;
		$this->poolName = $poolName;
		$this->rollingPrefix = $rollingPrefix;
		$this->extension = $extension;
		$this->maximumlength = $maximumlength;
		$this->delimiter = $delimiter;
	}

	/**
	 * Generate 20 bytes unique ID
	 * @return string 20 bytes
	 */
	public function generateNewId()
	{
		$uuid = uniqid();
		if((strlen($uuid) % 2) == 1)
		{
			$uuid = '0'.$uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));
		return sprintf('%s%s', $uuid, $random);
	}

	/**
	 * Get pooling path
	 * @return string Polling path
	 */
	public function getPoolPath()
	{
		if(!file_exists($this->baseDir))
		{
			$this->prepareDirectory($this->baseDir, $this->applicationDir, 0777);
		}
		$poolPath = $this->baseDir . "/" . $this->poolName . $this->extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->baseDir . "/" . $this->rollingPrefix.date('Y-m-d-H-i-s')."-".$this->generateNewId().$this->extension;
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}

	/**
	 * Append query to sync file
	 * @param string $sql Query to be synchronized
	 * @return int Number of byte written to sync file include delimiter
	 */
	public function createSync($sql)
	{
		$syncPath = $this->getPoolPath();
		$fp = fopen($syncPath, 'a');
		$l1 = fwrite($fp, $this->delimiter.self::NEW_LINE);
		$l2 = fwrite($fp, $sql.";".self::NEW_LINE);
		fclose($fp);
		return $l1 + $l2;
	}

	/**
	 * Get sync file delimiter
	 * @return string Sync file delimiter
	 */
	public function getDelimiter()
	{
		return $this->delimiter;
	}

	/**
	 * Prepare directory
	 * @param string $dir2prepared Path to be pepared
	 * @param string $dirBase Base directory
	 * @param int $permission File permission
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return void
	 */
	public function prepareDirectory($dir2prepared, $dirBase, $permission)
	{
		$dir = str_replace("\\", "/", $dir2prepared);
		$base = str_replace("\\", "/", $dirBase);
		$arrDir = explode("/", $dir);
		$arrBase = explode("/", $base);
		$base = implode("/", $arrBase);
		$dir2created = "";
		foreach($arrDir as $val)
		{
			$dir2created .= $val;
			if(stripos($base, $dir2created) !== 0 && !file_exists($dir2created))
			{
				$this->createDirecory($dir2created, $permission);
			}
			$dir2created .= "/";
		}
	}

	/**
	 * Create directory
	 * @param string $path Path to be created
	 * @param int $permission File permission
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return bool true on success or false on failure.
	 */
	public function createDirecory($path, $permission)
	{
		return @mkdir($path, $permission);
	}
}

class PicoDatabase
{

	private string $username = "";
	private string $password = "";
	private string $databaseName = "";
	private string $timezone = "00:00";
	private \PDO $conn;
	private \PicoDatabaseServer $databaseServer;
	private \PicoDatabaseSyncConfig $databaseSyncConfig;

	/**
	 * Summary of __construct
	 * @param PicoDatabaseServer $databaseServer
	 * @param string $username
	 * @param string $password
	 * @param mixed $databaseName
	 * @param mixed $timezone
	 * @param \PicoDatabaseSyncConfig $databaseSyncConfig
	 */
	public function __construct($databaseServer, $username, $password, $databaseName, $timezone, $databaseSyncConfig) //NOSONAR
	{
		$this->databaseServer = $databaseServer;
		$this->databaseSyncConfig = $databaseSyncConfig;

		$this->username = $username;
		$this->password = $password;
		$this->databaseName = $databaseName;
		$this->timezone = $timezone;	
	}

	/**
	 * Get database server information
	 * @return PicoDatabaseServer Database server information
	 */
	public function getDatabaseServer()
	{
		return $this->databaseServer;
	}

	/**
	 * Get database sync configuration
	 * @return PicoDatabaseSyncConfig Database sync configuration
	 */
	public function getDatabaseSyncConfig()
	{
		return $this->databaseSyncConfig;
	}

	/**
	 * Connect to database
	 * @return bool true if success and false if failed
	 */
	public function connect()
	{
		$ret = false;
		date_default_timezone_set($this->timezone);
		$timezoneOffset = date("P");
		try {
			$connectionString = $this->databaseServer->getDriver() . ':host=' . $this->databaseServer->getHost() . '; port=' . $this->databaseServer->getPort() . '; dbname=' . $this->databaseName;

			$this->conn = new \PDO(
				$connectionString, 
				$this->username, 
				$this->password,
				array(
					\PDO::MYSQL_ATTR_INIT_COMMAND =>"SET time_zone = '$timezoneOffset';",
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
					)
			);

			$ret = true;


		} catch (\PDOException $e) {
			echo "Connection error " . $e->getMessage();
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Get database connection
	 * @return PDO Represents a connection between PHP and a database server.
	 */
	public function getDatabaseConnection()
	{
		return $this->conn;
	}

	/**
	 * Execute query without return anything
	 * @param string $sql Query string to be executed
	 */
	public function execute($sql)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(\PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
	}

	/**
	 * Execute query
	 * @param string $sql Query string to be executed
	 * @return PDOStatement
	 */
	public function executeQuery($sql) : \PDOStatement
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(\PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		return $stmt;
	}

	/**
	 * Execute query and sync
	 * @param mixed $sql Query string to be executed
	 * @param mixed $sync Flag synchronizing
	 * @return PDOStatement|bool
	 */
	private function executeAndSync($sql, $sync)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(\PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		if($sync)
		{
			$this->createSync($sql);
		}
		return $stmt;
	}

	/**
	 * Execute query and sync to hub
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return PDOStatement
	 */
	public function executeInsert($sql, $sync) : \PDOStatement
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute update query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return PDOStatement
	 */
	public function executeUpdate($sql, $sync) : \PDOStatement
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute delete query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return PDOStatement
	 */
	public function executeDelete($sql, $sync) : \PDOStatement
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute transaction query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return PDOStatement
	 */
	public function executeTransaction($sql, $sync) : \PDOStatement
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Create database synchronizer
	 * @param string $sql
	 * @return int Number of byte written to sync file include delimiter
	 */
	public function createSync($sql)
	{
		return $this->databaseSyncConfig->createSync($sql);
	}

	/**
	 * Generate 20 bytes unique ID
	 * @return string 20 bytes
	 */
	public function generateNewId()
	{
		$uuid = uniqid();
		if((strlen($uuid) % 2) == 1)
		{
			$uuid = '0'.$uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));
		return sprintf('%s%s', $uuid, $random);
	}
}

