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
	public $school_id = '';
	public $school_name = '';
	public $school_code = '';
	public $use_token = 0;

	public function __construct($database, $username, $password, $createlog = false)
	{
		global $cfg;
		global $picoEdu;
		if ($username != '') {
			$sql = "SELECT `edu_student`.`student_id`, `edu_student`.`username`, `edu_student`.`name`, `edu_student`.`gender`, 
			`edu_student`.`birth_place`, `edu_student`.`birth_day`, `edu_student`.`email`, `edu_student`.`phone`, `edu_student`.`country_id`, 
			`edu_student`.`state_id`, `edu_student`.`city_id`, `edu_student`.`school_id`, `edu_student`.`class_id`,
			`edu_school`.`name` as `school_name`, `edu_school`.`school_code` as `school_code`, `edu_school`.`use_token`
			from `edu_student` 
			left join(`edu_school`) on(`edu_school`.`school_id` = `edu_student`.`school_id`)
			where `edu_student`.`username` like '$username' and `edu_student`.`password` = md5('$password') 
			and `edu_student`.`active` = '1'
			and `edu_student`.`blocked` = '0'
			";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount() > 0) {
				$student_login = $stmt->fetchObject();
				$this->student_id = $student_login->student_id;
				$this->username = ($student_login->username != '') ? $student_login->username : $student_login->member_id;
				$this->name = trim($student_login->name);
				$this->gender = $student_login->gender;
				$this->birth_place = $student_login->birth_place;
				$this->birth_day = $student_login->birth_day;
				$this->email = $student_login->email;
				$this->phone = $student_login->phone;
				$this->country_id = $student_login->country_id;
				$this->state_id = $student_login->state_id;
				$this->city_id = $student_login->city_id;
				$this->class_id = $student_login->class_id;
				$this->school_id = $student_login->school_id;
				$this->school_name = $student_login->school_name;
				$this->school_code = $student_login->school_code;
				$this->use_token = $student_login->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $picoEdu->getLocalDateTime();
					$sql = "update `edu_student` set `ip_last_activity` = '$ip', `time_last_activity` = '$now' where `student_id` = '" . $this->student_id . "'";
					$database->execute($sql);
				}
			}
		}
	}
}
