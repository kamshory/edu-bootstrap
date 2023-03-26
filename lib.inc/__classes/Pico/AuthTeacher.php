<?php

namespace Pico;

class AuthTeacher
{
	public $teacher_id = "";
	public $username = "";
	public $name = "";
	public $gender = 'M';
	public $birth_place = "";
	public $birth_day = "";
	public $email = "";
	public $phone = "";
	public $country_id = "";
	public $state_id = "";
	public $city_id = "";
	public $school_id = "";
	public $school_name = "";
	public $school_code = "";
	public $use_token = 0;
	private $password;
	private $createlog = false;
	private $database;

	/**
	 * Constructor of TeacherAuth
	 * @param \Pico\PicoDatabase $database
	 * @param string $username
	 * @param string $password
	 * @param bool $createlog
	 */
	public function __construct($database, $username, $password, $createlog = false)
	{
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->createlog = $createlog;
	}

	public function login()
	{
		$database = $this->database;
		$username = $this->username;
		$password = $this->password;
		$createlog = $this->createlog;

		$passwordHash = md5($password);

		if ($username != '') {
			$sql = "SELECT `edu_teacher`.`teacher_id`, `edu_teacher`.`username`, `edu_teacher`.`name`, `edu_teacher`.`gender`, 
				`edu_teacher`.`birth_place`, `edu_teacher`.`birth_day`, `edu_teacher`.`email`, `edu_teacher`.`phone`, 
				`edu_teacher`.`country_id`, `edu_teacher`.`state_id`, `edu_teacher`.`city_id`, `edu_teacher`.`school_id`, 
				`edu_school`.`name` AS `school_name`, `edu_school`.`school_code` AS `school_code`, `edu_school`.`use_token`
				FROM `edu_teacher` 
				LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_teacher`.`school_id`)
				WHERE `edu_teacher`.`username` LIKE '$username' 
				AND `edu_teacher`.`password` = '$passwordHash' 
				AND `edu_teacher`.`active` = true
				AND `edu_teacher`.`blocked` = false
				";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$teacherLoggedIn = $stmt->fetchObject();
				$this->teacher_id = $teacherLoggedIn->teacher_id . "";
				$this->username = ($teacherLoggedIn->username != '') ? $teacherLoggedIn->username . "" : $teacherLoggedIn->member_id . "";
				$this->name = trim($teacherLoggedIn->name . "");
				$this->gender = $teacherLoggedIn->gender . "";
				$this->birth_place = $teacherLoggedIn->birth_place . "";
				$this->birth_day = $teacherLoggedIn->birth_day . "";
				$this->email = $teacherLoggedIn->email . "";
				$this->phone = $teacherLoggedIn->phone . "";
				$this->country_id = $teacherLoggedIn->country_id . "";
				$this->state_id = $teacherLoggedIn->state_id . "";
				$this->city_id = $teacherLoggedIn->city_id . "";
				$this->school_id = $teacherLoggedIn->school_id . "";
				$this->school_name = $teacherLoggedIn->school_name . "";
				$this->school_code = $teacherLoggedIn->school_code . "";
				$this->use_token = $teacherLoggedIn->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $database->getLocalDateTime();
					$sql = "UPDATE `edu_teacher` SET 
						`ip_last_activity` = '$ip', `time_last_activity` = '$now' 
						WHERE `teacher_id` = '" . $this->teacher_id . "'";
					$database->executeUpdate($sql, true);
				}
			}
			return $this;
		}
	}

	/**
	 * Get the value of teacher_id
	 * @return string
	 */
	public function getTeacherId()
	{
		return $this->teacher_id;
	}

	/**
	 * Get the value of username
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Get the value of name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the value of gender
	 * @return string
	 */
	public function getGender()
	{
		return $this->gender;
	}

	/**
	 * Get the value of birth_place
	 * @return string
	 */
	public function getBirthPlace()
	{
		return $this->birth_place;
	}

	/**
	 * Get the value of birth_day
	 * @return string
	 */
	public function getBirthDay()
	{
		return $this->birth_day;
	}

	/**
	 * Get the value of email
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Get the value of phone
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * Get the value of country_id
	 * @return string
	 */
	public function getCountryId()
	{
		return $this->country_id;
	}

	/**
	 * Get the value of state_id
	 * @return string
	 */
	public function getStateId()
	{
		return $this->state_id;
	}

	/**
	 * Get the value of city_id
	 * @return string
	 */
	public function getCityId()
	{
		return $this->city_id;
	}

	/**
	 * Get the value of school_id
	 * @return string
	 */
	public function getSchoolId()
	{
		return $this->school_id;
	}

	/**
	 * Get the value of school_name
	 * @return string
	 */
	public function getSchoolName()
	{
		return $this->school_name;
	}

	/**
	 * Get the value of school_code
	 * @return string
	 */
	public function getSchoolCode()
	{
		return $this->school_code;
	}

	/**
	 * Get the value of use_token
	 * @return bool|int
	 */
	public function getUseToken()
	{
		return $this->use_token;
	}
}
