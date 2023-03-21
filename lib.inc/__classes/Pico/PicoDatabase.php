<?php

namespace Pico;

class PicoDatabase
{

	/**
	 * Database connection
	 *
	 * @var \PDO
	 */
	private $conn;

	/**
	 * Database credential
	 *
	 * @var \Pico\PicoDatabaseCredentials
	 */
	private $databaseCredentials;

	/**
	 * Database synchronization config
	 *
	 * @var \Pico\PicoDatabaseSyncConfig
	 */
	private $databaseSyncConfig;

	/**
	 * Summary of __construct
	 * @param \Pico\PicoDatabaseCredentials $databaseCredentials
	 * @param string $username
	 * @param string $password
	 * @param string $databaseName
	 * @param string $timezone
	 * @param \Pico\PicoDatabaseSyncConfig $databaseSyncConfig
	 */
	public function __construct($databaseCredentials, $databaseSyncConfig) //NOSONAR
	{
		$this->databaseCredentials = $databaseCredentials;
		$this->databaseSyncConfig = $databaseSyncConfig;
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
		date_default_timezone_set($this->databaseCredentials->getTimezone());
		$timezoneOffset = date("P");
		try {
			$connectionString = $this->databaseCredentials->getDriver() . ':host=' . $this->databaseCredentials->getHost() . '; port=' . $this->databaseCredentials->getPort() . '; dbname=' . $this->databaseCredentials->getDatabaseName();

			$this->conn = new \PDO(
				$connectionString,
				$this->databaseCredentials->getUsername(),
				$this->databaseCredentials->getPassword(),
				array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '$timezoneOffset';",
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
	 * Fetch result
	 *
	 * @param string $sql
	 * @param array $defaultValue
	 * @return array|null
	 */
	public function fetchAssoc($sql, $defaultValue = null)
	{
		$result = array();
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
			if($stmt->rowCount() > 0)
			{
				$result = $stmt->fetch(\PDO::FETCH_ASSOC);
			}
			else
			{
				$result = $defaultValue;
			}
		} catch (\PDOException $e) {
			$result = $defaultValue;
		}
		return $result;
	}

	/**
	 * Fetch result all
	 *
	 * @param string $sql
	 * @param array $defaultValue
	 * @return array|null
	 */
	public function fetchAssocAll($sql, $defaultValue = null)
	{
		$result = array();
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
			if($stmt->rowCount() > 0)
			{
				$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			}
			else
			{
				$result = $defaultValue;
			}
		} catch (\PDOException $e) {
			$result = $defaultValue;
		}
		return $result;
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
		} catch (\PDOException $e) {
			// Do nothing
		}
	}

	/**
	 * Execute query
	 * @param string $sql Query string to be executed
	 * @return \PDOStatement|bool
	 */
	public function executeQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			$stmt->execute();
		} catch (\PDOException $e) {
			echo $e->getMessage() . "\r\nERROR &raquo; $sql";
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
		} catch (\PDOException $e) {
			echo $e->getMessage() . "\r\nERROR &raquo; $sql";
		}
		if ($sync) {
			$this->createSync($sql);
		}
		return $stmt;
	}

	/**
	 * Execute query and sync to hub
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement|bool
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
	 * @return \PDOStatement|bool
	 */
	public function executeDelete($sql, $sync)
	{
		return $this->executeAndSync($sql, $sync);
	}

	/**
	 * Execute transaction query
	 * @param string $sql Query string to be executed
	 * @param bool $sync Flag synchronizing
	 * @return \PDOStatement|bool
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
		if ((strlen($uuid) % 2) == 1) {
			$uuid = '0' . $uuid;
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
		if (isset($data) && is_array($data) && !empty($data)) {
			return $data['system_value'];
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Set system variable
	 * @param string $variableName Variable name
	 * @param mixed $value Value to be set
	 * @param bool $sync Flag to synchronize data
	 */
	public function setSystemVariable($variableName, $value, $sync = false)
	{
		$currentTime = date('Y-m-d H:i:s');
		$variableName = addslashes($variableName);
		$value = addslashes($value);
		$sql = "SELECT * FROM `edu_system_variable` 
		WHERE `system_variable_id` = '$variableName' ";
		if ($this->executeQuery($sql)->rowCount() > 0) {
			$sql = "UPDATE `edu_system_variable` 
			SET `system_value` = '$value', `time_edit` = '$currentTime' 
			WHERE `system_variable_id` = '$variableName' ";
			$this->executeUpdate($sql, $sync);
		} else {
			$sql = "INSERT INTO `edu_system_variable` 
			(`system_variable_id`, `system_value`, `time_create`, `time_edit`) VALUES
			('$variableName', '$value', '$currentTime' , '$currentTime')
			";
			$this->executeInsert($sql, $sync);
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

	/**
	 * Get the value of databaseCredentials
	 * @return \Pico\PicoDatabaseCredentials
	 */
	public function getDatabaseCredentials()
	{
		return $this->databaseCredentials;
	}
}
