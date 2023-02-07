<?php



class TeacherAuth
{
	public $teacher_id = 0;
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
	public $school_id = '';
	public $school_name = '';
	public $school_code = '';
	public $use_token = 0;

	/**
	 * Constructor of TeacherAuth
	 * @param \PicoDatabase $database
	 * @param string $username
	 * @param string $password
	 * @param bool $createlog
	 */
	public function __construct($database, $username, $password, $createlog = false)
	{
		global $cfg;
		global $picoEdu;
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
				$teacher_login = $stmt->fetchObject();
				$this->teacher_id = $teacher_login->teacher_id;
				$this->username = ($teacher_login->username != '') ? $teacher_login->username : $teacher_login->member_id;
				$this->name = trim($teacher_login->name);
				$this->gender = $teacher_login->gender;
				$this->birth_place = $teacher_login->birth_place;
				$this->birth_day = $teacher_login->birth_day;
				$this->email = $teacher_login->email;
				$this->phone = $teacher_login->phone;
				$this->country_id = $teacher_login->country_id;
				$this->state_id = $teacher_login->state_id;
				$this->city_id = $teacher_login->city_id;
				$this->school_id = $teacher_login->school_id;
				$this->school_name = $teacher_login->school_name;
				$this->school_code = $teacher_login->school_code;
				$this->use_token = $teacher_login->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $picoEdu->getLocalDateTime();
					$sql = "UPDATE `edu_teacher` SET 
						`ip_last_activity` = '$ip', 
						`time_last_activity` = '$now' 
						WHERE `teacher_id` = '" . $this->teacher_id . "'";
					$database->execute($sql);
				}
			}
		}
	}
}
