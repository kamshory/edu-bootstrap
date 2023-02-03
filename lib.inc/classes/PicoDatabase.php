<?php

class PicoDatabase
{

	public $driver = "mysql";
	public $host = "localhost";
	public $port = 3306;
	public $username = "";
	public $password = "";
	public $database = "";
	public $timezone = "00:00";
	public $syncDatabaseDir = "";
	public $syncDatabaseFileName = "";
	public $maxSize = 1000000;

	public $delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';
	const NEW_LINE = "\r\n";

	private \PDO $conn = null;

	public function __construct($driver, $host, $port, $username, $password, $database, $timezone, $syncDatabaseDir = null, $syncDatabaseFileName = null) //NOSONAR
	{
		$this->driver = $driver;
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->timezone = $timezone;
		if($syncDatabaseDir != null)
		{
			$this->syncDatabaseDir = $syncDatabaseDir;
		}
		if($syncDatabaseFileName != null)
		{
			$this->syncDatabaseFileName = $syncDatabaseFileName;
		}
	}

	public function connect()
	{
		$ret = false;
		try {
			$connectionString = $this->driver . ':host=' . $this->host . '; port=' . $this->port . '; dbname=' . $this->database;

			$this->conn = new \PDO($connectionString, $this->username, $this->password);
			$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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

	public function executeInsert($sql, $sync = false) : \PDOStatement
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
	public function executeUpdate($sql, $sync = false) : \PDOStatement
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
	public function executeDelete($sql, $sync = false) : \PDOStatement
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
	public function executeTransaction($sql, $sync = false) : \PDOStatement
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
		$poolPath = $this->syncDatabaseDir . "/" . $this->syncDatabaseFileName;
		if(filesize($poolPath) > $this->maxSize)
		{
			$newPath = $this->syncDatabaseDir . "/" . 'pool_'.date('Y-m-d-H-i-s').'.txt';
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}

	public function createSync($sql)
	{
		$syncPath = $this->getPoolPath();
		$fp = fopen($syncPath, 'a');
		fwrite($fp, $this->delimiter."\r\n");  
		fwrite($fp, $sql.";".self::NEW_LINE);  
		fclose($fp);  
	}

	/**
	 * Generate 20 bytes unique ID
	 * @return string 20 bytes
	 */
	public function generateNewId()
	{
		$uuid = uniqid();
		while((strlen($uuid) % 2) == 1)
		{
			$uuid = '0'.$uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));

		return sprintf('%s%s', $uuid, $random);
	}
}

