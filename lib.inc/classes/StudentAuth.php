<?php


class StudenAuth
{
	public $student_id = 0;
	public $username = '';
	public $name = '';
	public $gender = 'M';
	public $birth_place = '';
	public $birth_day = '';
	public $email = '';
	public $phone = '';
	public $country_id = '';
	public $state_id = '';
	public $city_id = '';
	public $class_id = 0;
	public $school_id = "";
	public $school_name = '';
	public $school_code = '';
	public $use_token = 0;

	/**
	 * Constructor of StudenAuth
	 * @param \PicoDatabase $database
	 * @param string $username
	 * @param string $password
	 * @param bool $createlog
	 */
	public function __construct($database, $username, $password, $createlog = false)
	{
		global $picoEdu;
		if ($username != '') {
			$sql = "SELECT `edu_student`.`student_id`, `edu_student`.`username`, `edu_student`.`name`, `edu_student`.`gender`, 
			`edu_student`.`birth_place`, `edu_student`.`birth_day`, `edu_student`.`email`, `edu_student`.`phone`, `edu_student`.`country_id`, 
			`edu_student`.`state_id`, `edu_student`.`city_id`, `edu_student`.`school_id`, `edu_student`.`class_id`,
			`edu_school`.`name` AS `school_name`, `edu_school`.`school_code` AS `school_code`, `edu_school`.`use_token`
			FROM `edu_student` 
			LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_student`.`school_id`)
			WHERE `edu_student`.`username` like '$username' AND `edu_student`.`password` = md5('$password') 
			AND `edu_student`.`active` = true
			AND `edu_student`.`blocked` = false
			";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$studentLoggedIn = $stmt->fetchObject();
				$this->student_id = $studentLoggedIn->student_id;
				$this->username = ($studentLoggedIn->username != '') ? $studentLoggedIn->username : $studentLoggedIn->member_id;
				$this->name = trim($studentLoggedIn->name);
				$this->gender = $studentLoggedIn->gender;
				$this->birth_place = $studentLoggedIn->birth_place;
				$this->birth_day = $studentLoggedIn->birth_day;
				$this->email = $studentLoggedIn->email;
				$this->phone = $studentLoggedIn->phone;
				$this->country_id = $studentLoggedIn->country_id;
				$this->state_id = $studentLoggedIn->state_id;
				$this->city_id = $studentLoggedIn->city_id;
				$this->class_id = $studentLoggedIn->class_id;
				$this->school_id = $studentLoggedIn->school_id;
				$this->school_name = $studentLoggedIn->school_name;
				$this->school_code = $studentLoggedIn->school_code;
				$this->use_token = $studentLoggedIn->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $picoEdu->getLocalDateTime();
					$sql = "UPDATE `edu_student` SET `ip_last_activity` = '$ip', `time_last_activity` = '$now' WHERE `student_id` = '" . $this->student_id . "'";
					$database->executeUpdate($sql, true);
				}
			}
		}
	}
}
