<?php

class TeacherAuth
{
	public $teacherId = '';
	public $username = '';
	public $name = '';
	public $gender = 'M';
	public $birthPlace = '';
	public $birthDay = '';
	public $email = '';
	public $phone = '';
	public $countryId = '';
	public $stateId = '';
	public $cityId = '';
	public $schoolId = "";
	public $schoolName = '';
	public $schoolCode = '';
	public $useToken = 0;

	/**
	 * Constructor of TeacherAuth
	 * @param \PicoDatabase $database
	 * @param string $username
	 * @param string $password
	 * @param bool $createlog
	 */
	public function __construct($database, $username, $password, $createlog = false)
	{
		if ($username != '') {
			$sql = "SELECT `edu_teacher`.`teacher_id`, `edu_teacher`.`username`, `edu_teacher`.`name`, `edu_teacher`.`gender`, 
				`edu_teacher`.`birth_place`, `edu_teacher`.`birth_day`, `edu_teacher`.`email`, `edu_teacher`.`phone`, 
				`edu_teacher`.`country_id`, `edu_teacher`.`state_id`, `edu_teacher`.`city_id`, `edu_teacher`.`school_id`, 
				`edu_school`.`name` AS `school_name`, `edu_school`.`school_code` AS `school_code`, `edu_school`.`use_token`
				FROM `edu_teacher` 
				LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_teacher`.`school_id`)
				WHERE `edu_teacher`.`username` like '$username' 
				AND `edu_teacher`.`password` = md5('$password') 
				AND `edu_teacher`.`active` = true
				AND `edu_teacher`.`blocked` = false
				";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$teacherLoggedIn = $stmt->fetchObject();
				$this->teacherId = $teacherLoggedIn->teacher_id;
				$this->username = ($teacherLoggedIn->username != '') ? $teacherLoggedIn->username : $teacherLoggedIn->member_id;
				$this->name = trim($teacherLoggedIn->name);
				$this->gender = $teacherLoggedIn->gender;
				$this->birthPlace = $teacherLoggedIn->birth_place;
				$this->birthDay = $teacherLoggedIn->birth_day;
				$this->email = $teacherLoggedIn->email;
				$this->phone = $teacherLoggedIn->phone;
				$this->countryId = $teacherLoggedIn->country_id;
				$this->stateId = $teacherLoggedIn->state_id;
				$this->cityId = $teacherLoggedIn->city_id;
				$this->schoolId = $teacherLoggedIn->school_id;
				$this->schoolName = $teacherLoggedIn->school_name;
				$this->schoolCode = $teacherLoggedIn->school_code;
				$this->useToken = $teacherLoggedIn->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $database->getLocalDateTime();
					$sql = "UPDATE `edu_teacher` SET 
						`ip_last_activity` = '$ip', 
						`time_last_activity` = '$now' 
						WHERE `teacher_id` = '" . $this->teacherId . "'";
					$database->executeUpdate($sql, true);
				}
			}
		}
	}

	/**
	 * Get the value of teacher_id
	 * @return string
	 */ 
	public function getTeacherId()
	{
		return $this->teacherId;
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
	 * Get the value of birthPlace
	 * @return string
	 */ 
	public function getBirthPlace()
	{
		return $this->birthPlace;
	}

	/**
	 * Get the value of birthDay
	 * @return string
	 */ 
	public function getBirthDay()
	{
		return $this->birthDay;
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
	 * Get the value of countryId
	 * @return string
	 */ 
	public function getCountryId()
	{
		return $this->countryId;
	}

	/**
	 * Get the value of stateId
	 * @return string
	 */ 
	public function getStateId()
	{
		return $this->stateId;
	}

	/**
	 * Get the value of cityId
	 * @return string
	 */ 
	public function getCityId()
	{
		return $this->cityId;
	}

	/**
	 * Get the value of schoolId
	 * @return string
	 */ 
	public function getSchoolId()
	{
		return $this->schoolId;
	}

	/**
	 * Get the value of schoolName
	 * @return string
	 */ 
	public function getSchoolName()
	{
		return $this->schoolName;
	}

	/**
	 * Get the value of schoolCode
	 * @return string
	 */ 
	public function getSchoolCode()
	{
		return $this->schoolCode;
	}

	/**
	 * Get the value of useToken
	 * @return bool|int
	 */ 
	public function getUseToken()
	{
		return $this->useToken;
	}
}
