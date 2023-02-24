<?php
namespace Pico;

class PicoDatabase
{

	private $conn;
	private $databaseServer;
	private $databaseSyncConfig;

	/**
	 * Summary of __construct
	 * @param PicoDatabaseCredentials $databaseServer
	 * @param string $username
	 * @param string $password
	 * @param string $databaseName
	 * @param string $timezone
	 * @param PicoDatabaseSyncConfig $databaseSyncConfig
	 */
	public function __construct($databaseServer, $databaseSyncConfig) //NOSONAR
	{
		$this->databaseServer = $databaseServer;
		$this->databaseSyncConfig = $databaseSyncConfig;

	}

	/**
	 * Get database server information
	 * @return PicoDatabaseCredentials Database server information
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
		date_default_timezone_set($this->databaseServer->getTimezone());
		$timezoneOffset = date("P");
		try {
			$connectionString = $this->databaseServer->getDriver() . ':host=' . $this->databaseServer->getHost() . '; port=' . $this->databaseServer->getPort() . '; dbname=' . $this->databaseServer->getDatabaseName();

			$this->conn = new \PDO(
				$connectionString, 
				$this->databaseServer->getUsername(), 
				$this->databaseServer->getPassword(),
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
	 * @return \PDO Represents a connection between PHP and a database server.
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
			// Do nothing
		}
	}

	/**
	 * Execute query
	 * @param string $sql Query string to be executed
	 * @return \PDOStatement
	 */
	public function executeQuery($sql) 
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
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement|bool
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
	 * @return \PDOStatement
	 */
	public function executeInsert($sql, $sync) 
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute update query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement|bool
	 */
	public function executeUpdate($sql, $sync) 
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute delete query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement
	 */
	public function executeDelete($sql, $sync) 
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute transaction query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement
	 */
	public function executeTransaction($sql, $sync) 
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

	/**
	 * Get system variable
	 * @param string $variableName Variable name
	 * @param mixed $defaultValue Default value
	 * @return mixed System variable value of return default value if not exists
	 */
	public function getSystemVariable($variableName, $defaultValue = null)
	{
		$variableName = addslashes($variableName);
		$sql = "SELECT * FROM `edu_system_variable` 
		WHERE `system_variable_id` = '$variableName' ";
		$data = $this->executeQuery($sql)->fetch(\PDO::FETCH_ASSOC);
		if(isset($data) && is_array($data) && !empty($data))
		{
			return $data['system_value'];
		}
		else
		{
			return $defaultValue;
		}
	}

	/**
	 * Set system variable
	 * @param string $variableName Variable name
	 * @param mixed $value Value to be set
	 */
	public function setSystemVariable($variableName, $value)
	{
		$currentTime = date('Y-m-d H:i:s');
		$variableName = addslashes($variableName);
		$value = addslashes($value);
		$sql = "SELECT * FROM `edu_system_variable` 
		WHERE `system_variable_id` = '$variableName' ";
		if($this->executeQuery($sql)->rowCount() > 0)
		{
			$sql = "UPDATE `edu_system_variable` 
			SET `system_value` = '$value', `time_edit` = '$currentTime' 
			WHERE `system_variable_id` = '$variableName' ";
			$this->executeUpdate($sql, false);
		}
		else
		{
			$sql = "INSERT INTO `edu_system_variable` 
			(`system_variable_id`, `system_value`, `time_create`, `time_edit`) VALUES
			('$variableName', '$value', '$currentTime' , '$currentTime')
			";
			$this->executeInsert($sql, false);
		}
	}

	/**
	 * Get local date time
	 * @return string Local date time
	 */
	public function getLocalDateTime()
	{
		return date('Y-m-d H:i:s');
	}

	
}

