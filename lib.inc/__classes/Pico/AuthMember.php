<?php
namespace Pico;

class AuthMember
{
	public $member_id = '';
	public $username = '';
	public $name = '';
	public $gender = 'M';
	public $birth_place = '';
	public $birth_day = '';
	public $email = '';
	public $phone = '';
	public $url = '';
	public $img_360_compress = '';
	public $autoplay_360 = '';
	public $autorotate_360 = '';
	public $show_compass = '';
	public $picture_hash = '';
	public $image_url = '';
	public $image_url_50 = '';
	public $image_url_100 = '';
	public $background = '';
	public $circle_avatar = 0;
	public $language = 'en';
	public $country_id = '';
	public $state_id = '';
	public $city_id = '';
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

		global $cfg;
		$sql = "SELECT `member_id`, `username`, `name`, `gender`, `birth_place`, `birth_day`, `email`, `phone`, `url`, `show_compass`,
		`autoplay_360`, `autorotate_360`, `img_360_compress`, `picture_hash`, `background`, `language`, `country_id`, `state_id`, `city_id`, `circle_avatar`
		FROM `member` 
		WHERE `username` = '$username' AND `password` = '$passwordHash' 
		AND `active` = true
		AND `blocked` = false
		";
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$memberLoggedIn = $stmt->fetchObject();
			$this->member_id = $memberLoggedIn->member_id;
			$this->username = ($memberLoggedIn->username != '') ? $memberLoggedIn->username : $memberLoggedIn->member_id;
			$this->name = trim($memberLoggedIn->name);
			$this->gender = $memberLoggedIn->gender;
			$this->birth_place = $memberLoggedIn->birth_place;
			$this->birth_day = $memberLoggedIn->birth_day;
			$this->email = $memberLoggedIn->email;
			$this->phone = $memberLoggedIn->phone;
			$this->country_id = $memberLoggedIn->country_id;
			$this->state_id = $memberLoggedIn->state_id;
			$this->city_id = $memberLoggedIn->city_id;
		
			if ($createlog) {
				$ip = addslashes($_SERVER['REMOTE_ADDR']);
				$now = $database->getLocalDateTime();
				$sql = "UPDATE `member` SET `last_seen_ip` = '$ip', `last_seen_time` = '$now' WHERE `member_id` = '" . $this->member_id . "'";
				$database->executeUpdate($sql, false);
			}
		}
		return $this;
	}
}
