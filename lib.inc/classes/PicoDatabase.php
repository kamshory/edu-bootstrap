<?php

class PicoDatabaseServer
{
	public $driver = 'mysql';
	public $host = 'localhost';
	public $port = 3306;
	public function __construct($driver, $host, $port)
	{
		$this->driver = $driver;
		$this->host = $host;
		$this->port = $port;
	}
}

class PicoDatabaseSyncConfig
{
	public $sync_database_base_dir = '';
	public $sync_database_pool_name = '';
	public $sync_database_rolling_prefix = '';
	public $sync_database_extension = '';
	public $sync_database_maximum_length = 1000000;
	public $sync_database_delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';
	const NEW_LINE = "\r\n";
	public function __construct($sync_database_base_dir, $sync_database_pool_name, $sync_database_rolling_prefix, $sync_database_extension, $sync_database_maximum_length, $sync_database_delimiter)
	{
		$this->sync_database_base_dir = $sync_database_base_dir;
		$this->sync_database_pool_name = $sync_database_pool_name;
		$this->sync_database_rolling_prefix = $sync_database_rolling_prefix;
		$this->sync_database_extension = $sync_database_extension;
		$this->sync_database_maximum_length = $sync_database_maximum_length;
		$this->sync_database_delimiter = $sync_database_delimiter;
	}

	public function getPoolPath()
	{
		$poolPath = $this->sync_database_base_dir . "/" . $this->sync_database_pool_name . $this->sync_database_extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->sync_database_maximum_length)
		{
			$newPath = $this->sync_database_base_dir . "/" . $this->sync_database_rolling_prefix.date('Y-m-d-H-i-s').$this->sync_database_extension;
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}

	public function createSync($sql)
	{
		$syncPath = $this->getPoolPath();
		$fp = fopen($syncPath, 'a');
		$l1 = fwrite($fp, $this->sync_database_delimiter.self::NEW_LINE);  
		$l2 = fwrite($fp, $sql.";".self::NEW_LINE);  
		fclose($fp);
		return $l1 + $l2;
	}

}

class PicoDatabase
{

	private $username = "";
	private $password = "";
	private $database = "";
	private $timezone = "00:00";

	private \PDO $conn;

	public \PicoDatabaseServer $databaseServer;
	public \PicoDatabaseSyncConfig $databaseSyncConfig;

	public function __construct($databaseServer, $username, $password, $database, $timezone, $databaseSyncConfig) //NOSONAR
	{
		$this->databaseServer = $databaseServer;
		$this->databaseSyncConfig = $databaseSyncConfig;

		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->timezone = $timezone;
		
		
	}

	public function connect()
	{
		$ret = false;
		$timezone_str = date("P");
		try {
			$connectionString = $this->databaseServer->driver . ':host=' . $this->databaseServer->host . '; port=' . $this->databaseServer->port . '; dbname=' . $this->database;

			$this->conn = new \PDO(
				$connectionString, 
				$this->username, 
				$this->password,
				array(
					\PDO::MYSQL_ATTR_INIT_COMMAND =>"SET time_zone = '$timezone_str';",
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

	public function getDatabaseConnection()
	{
		return $this->conn;
	}

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

	public function executeInsert($sql, $sync) : \PDOStatement
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
	public function executeUpdate($sql, $sync) : \PDOStatement
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
	public function executeDelete($sql, $sync) : \PDOStatement
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
	public function executeTransaction($sql, $sync) : \PDOStatement
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

	public function getPoolPath()
	{
		return $this->databaseSyncConfig->getPoolPath();
	}

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

