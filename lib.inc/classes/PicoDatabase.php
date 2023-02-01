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

	public $delimiter = '--------------ruihwuiethwiughweighiwehgiwe';
	const NEW_LINE = "\r\n";

	private $conn = null;

	public function __construct($driver, $host, $port, $username, $password, $database, $timezone, $syncDatabaseDir = null) //NOSONAR
	{
		$this->driver = $driver;
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->timezone = $timezone;
		$this->syncDatabaseDir = $syncDatabaseDir;
	}

	public function connect()
	{
		$ret = false;
		try {
			$connectionString = $this->driver . ':host=' . $this->host . '; port=' . $this->port . '; dbname=' . $this->database;

			$this->conn = new PDO($connectionString, $this->username, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$ret = true;
		} catch (PDOException $e) {
			echo "Connection error " . $e->getMessage();
			$ret = false;
		}
		return $ret;
	}

	public function getDatabaseConnection()
	{
		return $this->conn;
	}

	public function executeQuery($sql) : PDOStatement
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		return $stmt;
	}

	public function execute($sql)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
	}

	public function executeInsert($sql, $sync = false)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		if($sync)
		{
			$this->createSync($sql);
		}
		return $stmt;
	}
	public function executeUpdate($sql, $sync = false)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		if($sync)
		{
			$this->createSync($sql);
		}
		return $stmt;
	}
	public function executeDelete($sql, $sync = false)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		if($sync)
		{
			$this->createSync($sql);
		}
		return $stmt;
	}
	public function executeTransaction($sql, $sync = false)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		}
		catch(PDOException $e)
		{
			echo $e->getMessage()."\r\nERROR &raquo; $sql";
		}
		if($sync)
		{
			$this->createSync($sql);
		}
		return $stmt;
	}

	public function createSync($sql)
	{
		$syncPath = $this->syncDatabaseDir . "/" . "database.txt";
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

