<?php



class AdminAuth
{
	public $admin_id = '';
	public $admin_level = 0;
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
	public $real_school_id = '';
	public $school_name = '';
	public $school_code = '';
	public $use_token = 0;

	public function __construct($database, $username, $password, $createlog = false)
	{
		global $picoEdu;
		if ($username != '') {
			$sql = "SELECT `edu_admin`.`admin_id`, `edu_admin`.`username`, `edu_admin`.`name`, `edu_admin`.`gender`, 
		`edu_admin`.`birth_place`, `edu_admin`.`birth_day`, `edu_admin`.`email`, `edu_admin`.`phone`, 
		`edu_admin`.`country_id`, `edu_admin`.`state_id`, `edu_admin`.`city_id`, `edu_admin`.`school_id`, 
		`edu_school`.`name` as `school_name`, `edu_school`.`school_code` as `school_code`, 
		`edu_school`.`school_id` as `real_school_id`, `edu_school`.`use_token`, `edu_admin`.`admin_level`,
		`edu_school`.`use_token`
		from `edu_admin` 
		left join(`edu_school`) on(`edu_school`.`school_id` = `edu_admin`.`school_id`)
		where `edu_admin`.`username` like '$username' and `edu_admin`.`password` = md5('$password') 
		and `edu_admin`.`active` = '1'
		and `edu_admin`.`blocked` = '0'
		";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount()) {
				$admin_login = $stmt->fetchObject();
				$this->admin_id = $admin_login->admin_id;
				$this->admin_level = $admin_login->admin_level;
				$this->username = ($admin_login->username != '') ? $admin_login->username : $admin_login->member_id;
				$this->name = trim($admin_login->name);
				$this->gender = $admin_login->gender;
				$this->birth_place = $admin_login->birth_place;
				$this->birth_day = $admin_login->birth_day;
				$this->email = $admin_login->email;
				$this->phone = $admin_login->phone;
				$this->country_id = $admin_login->country_id;
				$this->state_id = $admin_login->state_id;
				$this->city_id = $admin_login->city_id;
				$this->school_id = $admin_login->school_id;
				$this->real_school_id = $admin_login->real_school_id;
				$this->school_name = $admin_login->school_name;
				$this->school_code = $admin_login->school_code;
				$this->use_token = $admin_login->use_token;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $picoEdu->getLocalDateTime();
					$sql = "update `edu_admin` set `ip_last_activity` = '$ip', `time_last_activity` = '$now' where `admin_id` = '" . $this->admin_id . "'";
					$database->execute($sql);
				}

			}
		}
	}
}