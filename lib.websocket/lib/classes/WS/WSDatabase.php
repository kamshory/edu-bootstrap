<?php
namespace WS;

class WSDatabase
{
	private $username = "";
	private $password = "";
	private $databaseName = "";
	private $timeZone = "Asia/Jakarta";
	private $conn;

	private $databaseDriver = "mysql";
	private $databaseHost = "localhost";
	private $databasePort = 3066;

	/**
	 * Constructor
	 * @param string $databaseDriver Driver
	 * @param string $databaseHost Server host
	 * @param int $databasePort Server port
	 * @param string $username Database username
	 * @param string $password Database user password
	 * @param string $databaseName Database name
	 * @param string $timeZone Application time zone
	 */
	public function __construct($databaseDriver, $databaseHost, $databasePort, $username, $password, $databaseName, $timeZone)
	{
		$this->databaseDriver = $databaseDriver;
		$this->databaseHost = $databaseHost;
		$this->databasePort = $databasePort;
		$this->username = $username;
		$this->password = $password;
		$this->databaseName = $databaseName;
		$this->timeZone = $timeZone;
	}

	/**
	 * Connect to database
	 * @return bool true if success and false if failed
	 */
	public function connect()
	{
		$ret = false;
		date_default_timezone_set($this->getTimeZone());
		$timezoneOffset = date("P");
		try {
			$connectionString = $this->databaseDriver . ':host=' . $this->databaseHost . '; port=' . $this->databasePort . '; dbname=' . $this->databaseName;

			$this->conn = new \PDO(
				$connectionString,
				$this->username,
				$this->password,
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
	 * Disconnect database
	 *
	 * @return void
	 */
	public function disconnect()
	{
		try {
			$this->conn = new \PDO(''); //NOSONAR
			$this->conn->query('KILL CONNECTION_ID()'); //NOSONAR
			$this->conn = null; //NOSONAR
		} catch (\Exception $e) {
			// Do nothing
		}
	}

	/**
	 * Execute query without return anything
	 * @param string $sql Query string to be executed
	 * @return \PDOStatement
	 */
	public function execute($sql)
	{
		$stmt = $this->conn->prepare($sql);
		try {
			return $stmt->execute();
		} catch (\PDOException $e) {
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
		} catch (\PDOException $e) {
			echo $e->getMessage() . "\r\nERROR &raquo; $sql";
		}
		return $stmt;
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
	 * Get student login information
	 *
	 * @param string $username
	 * @param string $password
	 * @param mixed $resourceId
	 * @return object
	 */
	public function getLoginStudent($username, $password, $resourceId)
	{
		$student = new \stdClass;

		$student->student_id = "";
		$student->name = "";
		$student->username = "";
		$student->gender = "";
		$student->class_id = "";
		$student->school_id = "";
		$student->resourceId = $resourceId;
		$student->image = '';

		$passwordHash = md5($password);

		if ($username != '') {
			$sql = "SELECT 
            `edu_student`.`student_id`, 
            `edu_student`.`username`, 
            `edu_student`.`name`, 
            `edu_student`.`gender`, 
			`edu_student`.`school_id`, 
			`edu_student`.`class_id`,
			`edu_student`.`picture_rand`
			FROM `edu_student` 
			WHERE `edu_student`.`username` LIKE '$username' 
			AND `edu_student`.`password` = '$passwordHash' 
			AND `edu_student`.`active` = true
			AND `edu_student`.`blocked` = false
			";

			$stmt = $this->executeQuery($sql);
			if($stmt->rowCount() > 0) {
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

	/**
	 * Get student image URL
	 *
	 * @param string $school_id
	 * @param string $student_id
	 * @param string $rand
	 * @return string
	 */
	public function getImageUrl($school_id, $student_id, $rand)
	{
		return 'media.edu/school/' . $school_id . '/user.avatar/student/' . $student_id . '/img-300x300.jpg?rand=' . $rand;
	}

	/**
	 * Get the value of databaseName
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	/**
	 * Set the value of databaseName
	 *
	 * @return self
	 */
	public function setDatabaseName($databaseName)
	{
		$this->databaseName = $databaseName;

		return $this;
	}

	/**
	 * Get the value of databaseDriver
	 * @return string
	 */
	public function getDatabaseDriver()
	{
		return $this->databaseDriver;
	}

	/**
	 * Get the value of databaseHost
	 * @return string
	 */
	public function getDatabaseHost()
	{
		return $this->databaseHost;
	}

	/**
	 * Get the value of databasePort
	 * @return int
	 */
	public function getDatabasePort()
	{
		return $this->databasePort;
	}

	/**
	 * Get the value of timeZone
	 * @return string
	 */
	public function getTimeZone()
	{
		return $this->timeZone;
	}
}
