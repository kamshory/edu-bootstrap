<?php

class WSDatabase
{

	private string $username = "";
	private string $password = "";
	private string $databaseName = "";
	private string $timezone = "00:00";
	private \PDO $conn;

    private string $databaseDriver = "mysql";
    private string $databaseHost = "localhost";
    private string $databasePort = 3066;
    
	/**
	 * Constructor
	 * @param string $databaseDriver
	 * @param string $databaseHost
	 * @param int $databasePort
	 * @param string $username
	 * @param string $password
	 * @param string $databaseName
	 * @param string $timezone
	 */
	public function __construct($databaseDriver, $databaseHost, $databasePort, $username, $password, $databaseName, $timezone) //NOSONAR
	{
		$this->databaseDriver = $databaseDriver;
        $this->databaseHost = $databaseHost;
        $this->databasePort = $databasePort;

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
			$connectionString = $this->databaseDriver . ':host=' . $this->databaseHost . '; port=' . $this->databasePort . '; dbname=' . $this->databaseName;

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
			// Do nothing
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
	 * @param string $variable_name Variable name
	 * @param mixed $default_value Default value
	 * @return mixed System variable value of return default value if not exists
	 */
	public function getSystemVariable($variable_name, $default_value = null)
	{
		$variable_name = addslashes($variable_name);
		$sql = "SELECT * FROM `edu_system_variable` 
		WHERE `system_variable_id` = '$variable_name' ";
		$data = $this->executeQuery($sql)->fetch(PDO::FETCH_ASSOC);
		if(isset($data) && is_array($data) && !empty($data))
		{
			return $data['system_value'];
		}
		else
		{
			return $default_value;
		}
	}

	/**
	 * Set system variable
	 * @param string $variable_name Variable name
	 * @param mixed $value Value to be set
	 */
	public function setSystemVariable($variable_name, $value)
	{
		$current_time = date('Y-m-d H:i:s');
		$variable_name = addslashes($variable_name);
		$value = addslashes($value);
		$sql = "SELECT * FROM `edu_system_variable` 
		WHERE `system_variable_id` = '$variable_name' ";
		if($this->executeQuery($sql)->rowCount() > 0)
		{
			$sql = "UPDATE `edu_system_variable` 
			SET `system_value` = '$value', `time_edit` = '$current_time' 
			WHERE `system_variable_id` = '$variable_name' ";
			$this->executeQuery($sql);
		}
		else
		{
			$sql = "INSERT INTO `edu_system_variable` 
			(`system_variable_id`, `system_value`, `time_create`, `time_edit`) VALUES
			('$variable_name', '$value', '$current_time' , '$current_time')
			";
			$this->executeQuery($sql);
		}
	}

    public function getLoginStudent($username, $password, $resourceId)
    {
        $student = new stdClass;

        $student->student_id = "";
        $student->username = "";
        $student->name = "";
        $student->gender = "";
        $student->class_id = "";
        $student->school_id = "";
        $student->resourceId = $resourceId;

        if ($username != '') {
			$sql = "SELECT 
            `edu_student`.`student_id`, 
            `edu_student`.`username`, 
            `edu_student`.`name`, 
            `edu_student`.`gender`, 
			`edu_student`.`school_id`, `edu_student`.`class_id`,
			`edu_school`.`name` AS `school_name`, `edu_school`.`school_code` AS `school_code`
			FROM `edu_student` 
			LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_student`.`school_id`)
			WHERE `edu_student`.`username` like '$username' AND `edu_student`.`password` = md5('$password') 
			AND `edu_student`.`active` = true
			AND `edu_student`.`blocked` = false
			";
			$stmt = $this->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$studentLoggedIn = $stmt->fetchObject();
				$student->student_id = $studentLoggedIn->student_id;
				$student->username = ($studentLoggedIn->username != '') ? $studentLoggedIn->username : $studentLoggedIn->member_id;
				$student->name = trim($studentLoggedIn->name);
				$student->gender = $studentLoggedIn->gender;
				$student->class_id = $studentLoggedIn->class_id;
				$student->school_id = $studentLoggedIn->school_id;
			}
		}

        return $student;
    }


	/**
	 * Set the value of username
	 *
	 * @return  self
	 */ 
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * Set the value of password
	 *
	 * @return  self
	 */ 
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * Get the value of databaseName
	 */ 
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	/**
	 * Set the value of databaseName
	 *
	 * @return  self
	 */ 
	public function setDatabaseName($databaseName)
	{
		$this->databaseName = $databaseName;

		return $this;
	}

	/**
	 * Get the value of timezone
	 */ 
	public function getTimezone()
	{
		return $this->timezone;
	}

	/**
	 * Set the value of timezone
	 *
	 * @return  self
	 */ 
	public function setTimezone($timezone)
	{
		$this->timezone = $timezone;

		return $this;
	}

    /**
     * Get the value of databaseDriver
     */ 
    public function getDatabaseDriver()
    {
        return $this->databaseDriver;
    }

    /**
     * Set the value of databaseDriver
     *
     * @return  self
     */ 
    public function setDatabaseDriver($databaseDriver)
    {
        $this->databaseDriver = $databaseDriver;

        return $this;
    }

    /**
     * Get the value of databaseHost
     */ 
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * Set the value of databaseHost
     *
     * @return  self
     */ 
    public function setDatabaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;

        return $this;
    }

    /**
     * Get the value of databasePort
     */ 
    public function getDatabasePort()
    {
        return $this->databasePort;
    }

    /**
     * Set the value of databasePort
     *
     * @return  self
     */ 
    public function setDatabasePort($databasePort)
    {
        $this->databasePort = $databasePort;

        return $this;
    }
}
