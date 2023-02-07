<?php

class PicoDatabaseServer
{
	public string $driver = 'mysql';
	public string $host = 'localhost';
	public int $port = 3306;
	public function __construct($driver, $host, $port)
	{
		$this->driver = $driver;
		$this->host = $host;
		$this->port = $port;
	}
}

class PicoDatabaseSyncConfig
{
	public string $baseDir = '';
	public string $poolName = '';
	public string $rollingPrefix = '';
	public string $extension = '';
	public int $maximumlength = 1000000;
	public string $delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';
	const NEW_LINE = "\r\n";

	/**
	 * Constructor of PicoDatabaseSyncConfig
	 * @param string $baseDir Base directory of sync file
	 * @param string $poolName Pooling file name
	 * @param string $rollingPrefix Rolling prefix file name
	 * @param string $extension File extension
	 * @param int $maximumlength Maximum length of sync file
	 * @param string $delimiter Extra query delimiter
	 */
	public function __construct($baseDir, $poolName, $rollingPrefix, $extension, $maximumlength, $delimiter)
	{
		$this->baseDir = $baseDir;
		$this->poolName = $poolName;
		$this->rollingPrefix = $rollingPrefix;
		$this->extension = $extension;
		$this->maximumlength = $maximumlength;
		$this->delimiter = $delimiter;
	}

	/**
	 * Get pooling path
	 * @return string Polling path
	 */
	public function getPoolPath()
	{
		$poolPath = $this->baseDir . "/" . $this->poolName . $this->extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->baseDir . "/" . $this->rollingPrefix.date('Y-m-d-H-i-s').$this->extension;
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
}

class PicoDatabase
{

	private string $username = "";
	private string $password = "";
	private string $databaseName = "";
	private string $timezone = "00:00";

	private \PDO $conn;

	public \PicoDatabaseServer $databaseServer;
	public \PicoDatabaseSyncConfig $databaseSyncConfig;

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
	 * Connect to database
	 * @return bool true if success and false if failed
	 */
	public function connect()
	{
		$ret = false;
		date_default_timezone_set($this->timezone);
		$timezoneOffset = date("P");
		try {
			$connectionString = $this->databaseServer->driver . ':host=' . $this->databaseServer->host . '; port=' . $this->databaseServer->port . '; dbname=' . $this->databaseName;

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

