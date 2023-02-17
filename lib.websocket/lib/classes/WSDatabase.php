<?php

class WSDatabase
{

	private $username = "";
	private $password = "";
	private $databaseName = "";
	private $timezone = "00:00";
	private $conn;

    private $databaseDriver = "mysql";
    private $databaseHost = "localhost";
    private $databasePort = 3066;
    
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

	public function disconnect()
	{
		try
		{
			$this->conn = new PDO(''); //NOSONAR
			$this->conn->query('KILL CONNECTION_ID()'); //NOSONAR
			$this->conn = null; //NOSONAR
		}
		catch(Exception $e)
		{
			// Do nothing
		}
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

    public function getLoginStudent($username, $password, $resourceId)
    {
        $student = new stdClass;

        $student->student_id = "";
        $student->name = "";
        $student->username = "";
        $student->gender = "";
        $student->class_id = "";
        $student->school_id = "";
        $student->resourceId = $resourceId;
		$student->image = '';
        if ($username != '') {
			$sql = "SELECT 
            `edu_student`.`student_id`, 
            `edu_student`.`username`, 
            `edu_student`.`name`, 
            `edu_student`.`gender`, 
			`edu_student`.`school_id`, `edu_student`.`class_id`,
			`edu_student`.`picture_rand`
			FROM `edu_student` 
			WHERE `edu_student`.`username` like '$username' AND `edu_student`.`password` = md5('$password') 
			AND `edu_student`.`active` = true
			AND `edu_student`.`blocked` = false
			";
	
			$stmt = $this->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$studentLoggedIn = $stmt->fetchObject();
				$student->student_id = $studentLoggedIn->student_id;
				$student->username = $studentLoggedIn->username;
				$student->name = trim($studentLoggedIn->name);
				$student->gender = $studentLoggedIn->gender;
				$student->class_id = $studentLoggedIn->class_id;
				$student->school_id = $studentLoggedIn->school_id;
				$student->image = $this->getImageUrl($studentLoggedIn->school_id, $studentLoggedIn->student_id, $studentLoggedIn->picture_rand);
			}
		}

        return $student;
    }

	public function getImageUrl($school_id, $student_id, $rand)
	{
		return 'media.edu/school/'.$school_id.'/user.avatar/student/'.$student_id.'/img-300x300.jpg?rand='.$rand;
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
     * Get the value of databaseDriver
     */ 
    public function getDatabaseDriver()
    {
        return $this->databaseDriver;
    }

    /**
     * Get the value of databaseHost
     */ 
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * Get the value of databasePort
     */ 
    public function getDatabasePort()
    {
        return $this->databasePort;
    }

}
