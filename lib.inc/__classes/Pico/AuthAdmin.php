<?php

namespace Pico;

class AuthAdmin
{
	public $admin_id = "";
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
	public $school_id = "";
	public $real_school_id = "";
	public $school_name = '';
	public $school_code = '';
	public $use_token = 0;
	public $use_national_id = 0;
	private $database;
	private $createlog = false;
	private $password;

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
			$sql = "SELECT `edu_admin`.`admin_id`, `edu_admin`.`username`, `edu_admin`.`name`, `edu_admin`.`gender`, 
			`edu_admin`.`birth_place`, `edu_admin`.`birth_day`, `edu_admin`.`email`, `edu_admin`.`phone`, 
			`edu_admin`.`country_id`, `edu_admin`.`state_id`, `edu_admin`.`city_id`, `edu_admin`.`school_id`, 
			`edu_school`.`name` AS `school_name`, `edu_school`.`school_code` AS `school_code`, 
			`edu_school`.`school_id` AS `real_school_id`, `edu_school`.`use_token`, `edu_admin`.`admin_level`,
			`edu_school`.`use_token`, `edu_school`.`use_national_id`
			FROM `edu_admin` 
			LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_admin`.`school_id`)
			WHERE `edu_admin`.`username` LIKE '$username' AND `edu_admin`.`password` = '$passwordHash' 
			AND `edu_admin`.`active` = true
			AND `edu_admin`.`blocked` = false
			";
			$stmt = $database->executeQuery($sql);
			if ($stmt->rowCount()) {
				$adminLoggedIn = $stmt->fetchObject();
				$this->admin_id = $adminLoggedIn->admin_id . "";
				$this->admin_level = $adminLoggedIn->admin_level;
				$this->username = ($adminLoggedIn->username != '') ? $adminLoggedIn->username . "" : $adminLoggedIn->member_id . "";
				$this->name = trim($adminLoggedIn->name . "");
				$this->gender = $adminLoggedIn->gender . "";
				$this->birth_place = $adminLoggedIn->birth_place . "";
				$this->birth_day = $adminLoggedIn->birth_day . "";
				$this->email = $adminLoggedIn->email . "";
				$this->phone = $adminLoggedIn->phone . "";
				$this->country_id = $adminLoggedIn->country_id . "";
				$this->state_id = $adminLoggedIn->state_id . "";
				$this->city_id = $adminLoggedIn->city_id . "";
				$this->school_id = $adminLoggedIn->school_id . "";
				$this->real_school_id = $adminLoggedIn->real_school_id . "";
				$this->school_name = $adminLoggedIn->school_name . "";
				$this->school_code = $adminLoggedIn->school_code . "";
				$this->use_token = $adminLoggedIn->use_token;
				$this->use_national_id = $adminLoggedIn->use_national_id;
				if ($createlog) {
					$ip = addslashes($_SERVER['REMOTE_ADDR']);
					$now = $database->getLocalDateTime();
					$sql = "UPDATE `edu_admin` SET `ip_last_activity` = '$ip', `time_last_activity` = '$now' WHERE `admin_id` = '" . $this->admin_id . "'";
					$database->executeUpdate($sql, true);
				}
			}
		}
		return $this;
	}
}
