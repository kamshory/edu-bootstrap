<?php

namespace Pico;

class PicoEdu //NOSONAR
{
	const SPAN_OPEN = '<span>';
	const SPAN_CLOSE = '</span>';
	const SPAN_TITLE = '<span title="';


	/**
	 * Database object
	 * @var \Pico\PicoDatabase
	 */
	private $database;

	/**
	 * Constuctor of PicoEdu
	 * @param \Pico\PicoDatabase $database Database
	 */
	public function __construct(PicoDatabase $database)
	{
		$this->database = $database;
	}
	/**
	 * Get valid username
	 * @param string $username Username to be validate
	 * @return string Valid username
	 */
	public function getValidUsername($username)
	{
		$username = preg_replace("/[^A-Za-z\d_]/i", "", $username); //NOSONAR
		if (!@preg_match('#[a-z]#', $username)) {
			$username = ltrim($username, "123456789");
		}
		$username = substr($username, 0, 30);
		$username = str_replace("__", "_", $username);
		$username = str_replace("__", "_", $username);
		$username = str_replace("__", "_", $username);
		$username = trim($username, "_");
		return $username;
	}

	/**
	 * Chek valid full name
	 * @param string $fullName Full name to be checked
	 * @return bool true if valid and false if invalid
	 */
	public function checkValidName($fullName)
	{
		$fullName = trim(preg_replace(\Pico\PicoConst::TRIM_EXTRA_SPACE, " ", $fullName));
		$arr_name = explode(" ", $fullName);
		$valid_name = 1;
		foreach ($arr_name as $name) {
			$valid_part = 1;
			$name = str_replace(
				array('A', 'E', 'I', 'O', 'U', 'Y', 'a', 'e', 'i', 'o', 'u', 'y'),
				' ',
				$name
			);
			$name = trim(preg_replace(\Pico\PicoConst::TRIM_EXTRA_SPACE, " ", $name));
			$arr_part = explode(" ", $name);
			foreach ($arr_part as $val2) {
				if (strlen($val2) > 4 && $this->containingLowercase($val2)) {
					$valid_part = $valid_part * 0;
					break;
				}
			}
			if (!$valid_part) {
				$valid_name = $valid_name * 0;
			}
			if (!$valid_name) {
				break;
			}
		}
		return $valid_name != 0;
	}

	/**
	 * Check that text is containing lower letter
	 * @param string $text Text to be checked
	 */
	public function containingLowercase($text)
	{
		for ($i = 0; $i < strlen($text); $i++) {
			$j = $text[$i];
			if (ctype_lower($j)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Convert time to human readable string
	 * @param int $tm Unix time stamp
	 * @return string String of the time
	 */
	public function timeToText($tm) //NOSONAR
	{
		global $language_res;
		global $language_id;
		$text = '';
		$fulltime = date(\Pico\PicoConst::FULL_DATE_TIME_INDONESIA_FORMAT, time() - $tm);
		if ($tm < 1) {
			$text = self::SPAN_OPEN . $language_res[$language_id]['txt_left_now'] . self::SPAN_CLOSE;
		} else if ($tm >= 1 && $tm < 60) {
			$text = self::SPAN_OPEN . $language_res[$language_id]['txt_left_just_now'] . self::SPAN_CLOSE;
		} else if ($tm >= 60 && $tm < 3600) {
			$ni = floor($tm / 60);
			if ($ni > 1) {
				$text = self::SPAN_OPEN . $ni . ' ' . $language_res[$language_id]['txt_left_minute2'] . self::SPAN_CLOSE;
			} else {
				$text = self::SPAN_OPEN . $ni . ' ' . $language_res[$language_id]['txt_left_minute'] . self::SPAN_CLOSE;
			}
		} else if ($tm >= 3600 && $tm < 86400) {
			$nh = floor($tm / 3600);
			if ($nh > 1) {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nh . ' ' . $language_res[$language_id]['txt_left_hour2'] . self::SPAN_CLOSE;
			} else {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nh . ' ' . $language_res[$language_id]['txt_left_hour'] . self::SPAN_CLOSE;
			}
		} else if ($tm >= 86400 && $tm < 2592000) {
			$nd = floor($tm / 86400);
			if ($nd > 1) {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nd . ' ' . $language_res[$language_id]['txt_left_day2'] . self::SPAN_CLOSE;
			} else {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nd . ' ' . $language_res[$language_id]['txt_left_day'] . self::SPAN_CLOSE;
			}
		} else if ($tm >= 2592000 && $tm < 31536000) {
			$nm = floor($tm / 2592000);
			if ($nm > 1) {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nm . ' ' . $language_res[$language_id]['txt_left_month2'] . self::SPAN_CLOSE;
			} else {
				$text = self::SPAN_TITLE . $fulltime . '">' . $nm . ' ' . $language_res[$language_id]['txt_left_month'] . self::SPAN_CLOSE;
			}
		} else if ($tm >= 31536000) {
			$ny = floor($tm / 31536000);
			if ($ny > 1) {
				$text = self::SPAN_TITLE . $fulltime . '">' . $ny . ' ' . $language_res[$language_id]['txt_left_year2'] . self::SPAN_CLOSE;
			} else {
				$text = self::SPAN_TITLE . $fulltime . '">' . $ny . ' ' . $language_res[$language_id]['txt_left_year'] . self::SPAN_CLOSE;
			}
		}
		return $text;
	}

	/**
	 * Get country name of given ID
	 * @param string $name
	 * @return string Country name if exists of null if not exists
	 * Get country name
	 * @param string $country_id Country ID
	 * @return string Country name
	 */
	public function getCountryName($country_id)
	{
		$sql = "SELECT `name` FROM `country` WHERE `country_id` = '$country_id' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() == 0) {
			return null;
		}
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		return @$data['name'];
	}

	/**
	 * Get country ID of given name
	 * @param string $name
	 * @return string Country ID if exists of null if not exists
	 */
	public function getCountryId($name)
	{
		$sql = "SELECT `country_id` FROM `country` WHERE `name` like '$name' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() == 0) {
			return null;
		}
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		return @$data['country_id'];
	}

	/**
	 * Fix phone number
	 * @param string $phone Phone number
	 * @return string Valid phone number
	 */
	public function fixPhone($phone)
	{
		if (substr($phone, 0, 1) != '0' && substr($phone, 0, 1) != '+' && substr($phone, 0, 2) != '62') {
			$phone = "0" . $phone;
		}
		if (strlen($phone) < 5) {
			$phone = '';
		}
		return $phone;
	}

	/**
	 * Generate alternative email address (dummy)
	 * @param string $server Server
	 * @param string $alt1 Alternate ID 1
	 * @param string $alt2 Alternate ID 2
	 * @param string $alt3 Alternate ID 3
	 * @return string Dummy email address
	 */
	public function generateAltEmail($server, $alt1, $alt2, $alt3)
	{
		$email = "";
		if ($alt1 != '') {
			$email = $alt1 . "@" . $server;
		} else if ($alt2 != '') {
			$email = $alt2 . "@" . $server;
		} else if ($alt3 != '') {
			$email = $alt3 . "@" . $server;
		}
		return $email;
	}

	/**
	 * Get existsing user data and create new if not exists
	 * @param array $user_data
	 * @return array User data
	 */
	public function getExistsingUser($user_data, $member_id = null)
	{
		$use_national_id = $member_id != null && !empty($member_id);

		$now = $this->database->getLocalDateTime();
		$ip = $_SERVER['REMOTE_ADDR'];

		$name = $user_data['name'];
		$name = trim(preg_replace("/[^a-zA-Z 0-9\.\-]+/", " ", $name), " -. ");
		$name = trim(preg_replace(\Pico\PicoConst::TRIM_EXTRA_SPACE, " ", $name));
		$gender = $user_data['gender'];
		$email = $this->trimWhitespace($user_data['email']);
		$phone = $user_data['phone'];
		$password = $user_data['password'];
		$birth_day = $user_data['birth_day'];
		$language = $user_data['language'];
		$country_id = $user_data['country_id'];

		if (!$use_national_id) {
			$uname = str_replace(" ", "", $name);
			$uname = substr($uname, 0, 16);
			$oke = false;
			$username = "";
			while ($oke === false || $oke == '') {
				$username = $oke = $this->isValidUsername($uname);
				if ($oke == '' || $oke === false) {
					$uname = $uname . mt_rand(11, 99);
					$username = $uname;
				}
			}
			$username = addslashes($username);
		} else {
			$username = addslashes($member_id);
		}

		$filter = "";

		if (!$use_national_id) {
			$member_id = $this->database->generateNewId();
			$filter = " AND `member_id` LIKE '$member_id' ";
		} else {
			$filter = " AND `name` LIKE '$name' AND `birth_day` LIKE '$birth_day' ";
		}

		$sql = "SELECT `member`.* 
			FROM `member` 
			WHERE (1=1) $filter
			";

		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount()) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			return array(
				'member_id' => $data['member_id'],
				'name' => $data['name'],
				'birth_day' => $data['birth_day'],
				'username' => $data['username'],
				'email' => $data['email']
			);
		} else {
			$auth = md5($username . $email);
			$sql = "INSERT INTO `member` 
			(`member_id`, `name`, `username`, `email`, `gender`, `birth_day`, `password`, `auth`, `language`, `phone`, `country_id`, 
			`time_register`, `last_activity_ip`, `last_activity_time`, `last_seen_ip`, `last_seen_time`, `active`) VALUES 
			('$member_id', '$name', '$username', '$email', '$gender', '$birth_day', '$password', '$auth', '$language', '$phone', '$country_id', 
			'$now', '$ip', '$now', '$ip', '$now', '1');
			";

			$this->database->executeInsert($sql, true);

			return array(
				'member_id' => $member_id,
				'name' => stripslashes($name),
				'birth_day' => stripslashes($birth_day),
				'username' => stripslashes($name),
				'email' => stripslashes($email)
			);
		}
	}

	/**
	 * Check if username is valid or not
	 * @return string|bool String valid username or false if username given is invalid
	 */
	public function isValidUsername($name)
	{
		$username = $this->getValidUsername($name);
		if ($username != '') {
			$sql = "SELECT `member_id`, `email`, `username`
			FROM `member`
			WHERE `username` LIKE '$username'
			";
			$stmt = $this->database->executeQuery($sql);
			if ($stmt->rowCount() == 0) {
				return $username;
			}
		}
		return false;
	}

	/**
	 * Get class list
	 * @param string $school_id School ID
	 * @return array Array contain class list of a school
	 */
	public function getArrayClass($school_id)
	{
		$sql = "SELECT `class_id`, `name` FROM `edu_class` WHERE `school_id` = '$school_id' ";
		$stmt = $this->database->executeQuery($sql);
		$ret = array();
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$ret[$data['class_id']] = $data['name'];
		}
		return $ret;
	}
	public function textClass($array_kelas, $teks_kelas, $max = 0)
	{
		$arr = explode(",", $teks_kelas);
		$cnt = count($arr);
		$arr2 = array();
		foreach ($arr as $key => $val) {
			if (isset($array_kelas[$val])) {
				$arr2[] = $array_kelas[$val];
				if ($max > 0 && $key >= $max - 1) {
					if ($max < $cnt) {
						$arr2[] = "&hellip; ($cnt)";
					}
					break;
				}
			}
		}
		return implode(", ", $arr2);
	}

	public function numberFormatTrans($bilangan, $keepnull = false)
	{
		if ($keepnull && ($bilangan === '' || $bilangan === null)) {
			return '';
		}
		global $cfg;
		return number_format($bilangan, $cfg->dec_precision, $cfg->dec_separator, $cfg->dec_thousands_separator);
	}

	public function secondsToTime($seconds)
	{
		// extract hours
		$hours = floor($seconds / (60 * 60));

		// extract minutes
		$divisor_for_minutes = $seconds % (60 * 60);
		$minutes = floor($divisor_for_minutes / 60);

		// extract the remaining seconds
		$divisor_for_seconds = $divisor_for_minutes % 60;
		$seconds = ceil($divisor_for_seconds);

		// return the final array
		return array(
			"h" => sprintf('%02d', $hours),
			"m" => sprintf('%02d', $minutes),
			"s" => sprintf('%02d', $seconds),
		);
	}

	public function getTextScoreFromString($answer, $compress = false) //NOSONAR
	{
		$result = array();
		$result2 = array();
		if ($answer != '') {
			$answer = str_replace(",]", ",0]", $answer);
			$json = '[' . $answer . ']';
			$arr = json_decode($json, true);
			if ($arr != null && is_array($arr)) {
				foreach ($arr as $question) {
					$question_id = $question[0] * 1;
					$option_id = $question[1] * 1;
					$sql2 = "SELECT `edu_option`.`question_id`, `edu_option`.`option_id`, 
					(SELECT `edu_question`.`basic_competence` 
						FROM `edu_question` 
						WHERE `edu_question`.`question_id` = `edu_option`.`question_id`) AS `basic_competence`,
					`edu_option`.`score`
					FROM `edu_option`
					WHERE `edu_option`.`question_id` = '$question_id' AND `edu_option`.`option_id` = '$option_id';
					";
					$stmt = $this->database->executeQuery($sql2);
					if ($stmt->rowCount() > 0) {
						$data2 = $stmt->fetch(\PDO::FETCH_ASSOC);
						$basic_competence = $data2['basic_competence'];
						$basic_competence = preg_replace(\Pico\PicoConst::TRIM_NON_NUMERIC, ".", $basic_competence);
						$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");
						$score = $data2['score'];
						$index = 0;
						if (stripos($basic_competence, ".") !== false) {
							$sp = explode(".", $basic_competence);
							$index = ($sp[0] * 1000) + $sp[1] * 1;
						} else {
							$index = $basic_competence * 1;
						}
						$index = floor($index);
						if (!isset($result[$index])) {
							$result[$index] = array();
							$len = 0;
						} else {
							$len = count($result[$index]['data']);
						}
						$result[$index]['basic_competence'] = $basic_competence;
						$result[$index]['data'][$len] = array(
							'question_id' => $question_id,
							'option_id' => $option_id,
							'basic_competence' => $basic_competence,
							'score' => $score
						);
					}
				}
			}
			foreach ($result as $key => $value) {
				$data = $value['data'];
				$count_data = count($data);
				$sum_score = 0;
				$max_score = $count_data;
				foreach ($data as $value2) {
					$sum_score += $value2['score'];
				}
				$result[$key]['score'] = 100 * $sum_score / $max_score;
			}
			$keys = array_keys($result);
			$keys2 = $keys;
			sort($keys2);
			$result2 = array();
			foreach ($keys2 as $val) {
				$result2[$val] = $result[$val];
			}
			if ($compress) {
				return $this->compressScore($result2);
			} else {
				return $result2;
			}
		}
	}
	public function getBasicCompetence($test_id)
	{
		$sql = "SELECT `basic_competence` FROM `edu_question` WHERE `test_id` = '$test_id' GROUP BY `basic_competence` ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$result = array();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($rows as $data) {
				$basic_competence = $data['basic_competence'];
				$basic_competence = preg_replace(\Pico\PicoConst::TRIM_NON_NUMERIC, ".", $basic_competence);
				$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");
				if (stripos($basic_competence, ".") !== false) {
					$sp = explode(".", $basic_competence);
					$index = ($sp[0] * 1000) + $sp[1] * 1;
				} else {
					$index = $basic_competence * 1;
				}
				$index = floor($index);
				$result[$index] = $basic_competence;
			}
			$keys = array_keys($result);
			$keys2 = $keys;
			sort($keys2);
			$result2 = array();
			foreach ($keys2 as $val) {
				$result2[$val] = $result[$val];
			}
			return $result2;
		} else {
			return null;
		}
	}
	public function compressScore($score)
	{
		if (count($score)) {
			$result = array();
			foreach ($score as $val) {
				$result[] = array($val['basic_competence'], $val['score']);
			}
		} else {
			$result = null;
		}
		return $result;
	}
	public function changeIndexScore($score)
	{
		if (count($score)) {
			$result = array();
			foreach ($score as $val) {
				$arr = explode(".", $val[0]);
				$index = (@$arr[0] * 1000) + (@$arr[1] * 1);
				$index = floor($index);
				$result[$index] = array($val[0], $val[1]);
			}
		} else {
			$result = null;
		}
		return $result;
	}
	public function writeprofile($school, $name, $value)
	{
		$sql = "SELECT * FROM `profile` WHERE `name` = '$name' AND `school_id` = '$school' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$sql = "UPDATE `profile` SET `value` = '$value' WHERE `name` = '$name' AND `school_id` = '$school'";
		} else {
			$profil_id = $this->database->generateNewId();
			$sql = "INSERT INTO `profile` 
			(`profil_id`, `school_id`, `name`, `value`) VALUES
			('$profil_id', '$school', '$name', '$value')";
		}
		$this->database->executeInsert($sql, true);
	}
	public function getProfile($school, $name, $default = "")
	{
		$sql = "SELECT * FROM `profile` WHERE `name` = '$name' AND `school_id` = '$school' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			return $data['value'];
		} else {
			return $default;
		}
	}
	public function deleteProfile($school, $name)
	{
		$sql = "DELETE FROM `profile` WHERE `name` = '$name' AND `school_id` = '$school' ";
		$this->database->executeDelete($sql, true);
	}
	public function getApplicationVersion()
	{
		$sql = "SELECT `version_id` FROM `version` WHERE `active` = true AND `current_version` = '1' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			$version_id = $data['version_id'];
			if ($version_id == "") {
				$version_id = "1.0.0";
			}
			return $version_id;
		} else {
			return "1.0.0";
		}
	}

	public function generateToken($count = 1, $length = 6)
	{
		$min = pow(10, $length);
		$max = ($min * 10) - 1;
		$sql = "SELECT `token` FROM `edu_token` WHERE `active` = true
		";
		$stmt = $this->database->executeQuery($sql);
		$active_token = array();
		$new_token = array();
		$temporary_token = array();
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$active_token[] = $data['token'];
		}
		$temporary_token = $active_token;
		for ($i = 0; $i < $count; $i++) {
			$token = mt_rand($min, $max);
			if (in_array($token, $temporary_token)) {
				$i--; //NOSONAR
			}
			$new_token[] = $token;
			$temporary_token[] = $token;
		}
		return $new_token;
	}
	public function logInvalidLogin($member_id, $signin_type, $time, $time_limit, $count_limit)
	{
		$start_time = date(\Pico\PicoConst::DATE_TIME_MYSQL, strtotime($time) - $time_limit);

		$sql = "DELETE FROM `edu_invalid_signin` 
		WHERE `member_id` = '$member_id' AND `signin_type` = '$signin_type' AND `signin_time` < '$start_time'
		";
		$this->database->executeDelete($sql, true);

		$sql = "INSERT INTO `edu_invalid_signin` 
		(`member_id`, `signin_type`, `signin_time`) VALUES
		('$member_id', '$signin_type', '$time')
		";
		$this->database->executeInsert($sql, true);

		$sql = "SELECT * 
		FROM `edu_invalid_signin` 
		WHERE `member_id` = '$member_id' AND `signin_type` = '$signin_type' AND `signin_time` >= '$start_time'
		";
		$stmt = $this->database->executeQuery($sql);

		$num = $stmt->rowCount();
		return $num <= $count_limit;
	}
	public function mapGender($gender)
	{
		$gender = str_replace(array('L', 'M'), 'M', $gender); // replace Laki-Laki and Male to Man
		$gender = str_replace(array('P', 'F'), 'W', $gender); // replace Perempuan and Female to Woman
		return $gender;
	}
	public function filterEmailAddress($email)
	{
		$email = trim(preg_replace(\Pico\PicoConst::TRIM_EXTRA_SPACE, "", $email));
		if (
			stripos($email, '@domain.com') !== false ||
			stripos($email, '@example.com') !== false ||
			stripos($email, '@contoh.com') !== false ||
			stripos($email, '@contoh.sch.id') !== false ||
			stripos($email, '@sekolah.sch.id') !== false
		) {
			return '';
		}
		return $email;
	}
	public function checkStudent($school_id, $reg_number, $reg_number_national, $name) //NOSONAR
	{
		$sql = "SELECT * FROM `edu_student` 
		WHERE `school_id` = '$school_id' 
		AND (`reg_number` LIKE '$reg_number' AND `reg_number` != '') ";
		$stmt = $this->database->executeQuery($sql);
		return $stmt->rowCount() > 0;
	}
	public function checkTeacher($school_id, $reg_number, $reg_number_national, $name) //NOSONAR
	{
		$sql = "SELECT * FROM `edu_teacher` 
		WHERE `school_id` = '$school_id' 
		AND (`reg_number` LIKE '$reg_number' AND `reg_number` != '') ";
		$stmt = $this->database->executeQuery($sql);
		return $stmt->rowCount() > 0;
	}

	public function getTextScore($answer_id, $compress = false) //NOSONAR
	{
		$sql = "SELECT `edu_answer`.`answer` FROM `edu_answer` WHERE `edu_answer`.`answer_id` = '$answer_id' ";
		$stmt = $this->database->executeQuery($sql);
		$result = array();
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($data['answer'] != '') {
				$data['answer'] = str_replace(",]", ",0]", $data['answer']);
				$json = '[' . $data['answer'] . ']';
				$arr = json_decode($json, true);
				if ($arr != null && is_array($arr)) {
					foreach ($arr as $question) {
						$question_id = $question[0] * 1;
						$option_id = $question[1] * 1;
						$sql2 = "SELECT `edu_option`.`question_id`, `edu_option`.`option_id`, 
						(SELECT `edu_question`.`basic_competence` 
							FROM `edu_question` 
							WHERE `edu_question`.`question_id` = `edu_option`.`question_id`) AS `basic_competence`,
						`edu_option`.`score`
						FROM `edu_option`
						WHERE `edu_option`.`question_id` = '$question_id' AND `edu_option`.`option_id` = '$option_id';
						";
						$stmt2 = $this->database->executeQuery($sql2);
						if ($stmt2->rowCount() > 0) {
							$data2 = $stmt2->fetch(\PDO::FETCH_ASSOC);

							$basic_competence = $data2['basic_competence'];
							$basic_competence = preg_replace(\Pico\PicoConst::TRIM_NON_NUMERIC, ".", $basic_competence);
							$basic_competence = trim(str_replace("..", ".", $basic_competence), " . ");
							$score = $data2['score'];
							$index = 0;
							if (stripos($basic_competence, ".") !== false) {
								$sp = explode(".", $basic_competence);
								$index = ($sp[0] * 1000) + $sp[1] * 1;
							} else {
								$index = $basic_competence * 1;
							}
							$index = floor($index);
							if (!isset($result[$index])) {
								$result[$index] = array();
								$len = 0;
							} else {
								$len = count($result[$index]['data']);
							}
							$result[$index]['basic_competence'] = $basic_competence;
							$result[$index]['data'][$len] = array(
								'question_id' => $question_id,
								'option_id' => $option_id,
								'basic_competence' => $basic_competence,
								'score' => $score
							);
						}
					}
				}
			}
		}

		return $this->finalizeBasicCompetenceData($result, $compress);
	}

	private function finalizeBasicCompetenceData($result, $compress = false)
	{
		foreach ($result as $key => $value) {
			$data = $value['data'];
			$count_data = count($data);
			$sum_score = 0;
			$max_score = $count_data;
			foreach ($data as $value2) {
				$sum_score += $value2['score'];
			}
			$result[$key]['score'] = 100 * $sum_score / $max_score;
		}
		$keys = array_keys($result);
		$keys2 = $keys;
		sort($keys2);
		$result2 = array();
		foreach ($keys2 as $val) {
			$result2[$val] = $result[$val];
		}
		if ($compress) {
			return $this->compressScore($result2);
		} else {
			return $result2;
		}
	}


	public function loginTest($school_id, $student_id, $test_id, $sessions_id, $time, $ip)
	{
		$sql = "INSERT INTO `edu_test_member` 
		(`school_id`, `student_id`, `test_id`, `sessions_id`, `time_enter`, `ip_enter`, `status`) VALUES
		('$school_id', '$student_id', '$test_id', '$sessions_id', '$time', '$ip', '1')
		";
		$this->database->executeInsert($sql, true);
	}
	public function logoutTest($school_id, $student_id, $test_id, $sessions_id, $time, $ip)
	{
		$sql = "UPDATE `edu_test_member` SET `time_exit` = '$time', `ip_exit` = '$ip', `status` = '2'
		WHERE `school_id` = '$school_id' AND `student_id` = '$student_id' AND `test_id` = '$test_id' AND `sessions_id` = '$sessions_id'
		";
		$this->database->executeUpdate($sql, true);
	}

	public function brToNewLineEncoded($content)
	{
		return str_replace(array("\\&lt;br /&gt;", " \\\r\n", "\\\n"), array("\\\\", " \\\r\n", "\\\n"), $content);
	}

	public function getGradeName($grade)
	{
		$arr = array(
			"0" => "",
			"1" => "Tingkat 1",
			"2" => "Tingkat 2",
			"3" => "Tingkat 3",
			"4" => "Tingkat 4",
			"5" => "Tingkat 5",
			"6" => "Tingkat 6",
			"7" => "Tingkat 7",
			"8" => "Tingkat 8",
			"9" => "Tingkat 9",
			"10" => "Tingkat 10",
			"11" => "Tingkat 11",
			"12" => "Tingkat 12",
			"13" => "Perguruan Tinggi"

		);
		return @$arr[$grade];
	}

	public function createGradeOption($grade)
	{
		$arr = array();
		for ($i = 1; $i <= 12; $i++) {
			$sel = $i == $grade ? \Pico\PicoConst::SELECT_OPTION_SELECTED : '';
			$arr[] = '<option value="' . $i . '" ' . $sel . '>Tingkat ' . $i . '</option>';
		}

		return implode("\r\n", $arr);
	}
	public function getGenderName($gender)
	{
		if ($gender == 'M') {
			return 'Laki-Laki';
		} else if ($gender == 'W') {
			return 'Perempuan';
		}
		return '';
	}

	public static function arrayToObject($inputArray)
	{
		if (!is_array($inputArray)) {
			return $inputArray;
		}

		$object = new \stdClass();
		if (is_array($inputArray) && count($inputArray) > 0) {
			foreach ($inputArray as $name => $value) {
				$name = strtolower(trim($name));
				if (!empty($name)) {
					$object->$name = \Pico\PicoEdu::arrayToObject($value);
				}
			}
			return $object;
		} else {
			return false;
		}
	}

	public function filterSanitizeName($name, $strict = false)
	{
		$name = addslashes($name);
		if ($strict) {
			$name = preg_replace("/[^A-Za-z\.\-\d_]/i", " ", $name); //NOSONAR
			$name = trim($name, " ._- ");
		}
		$name = preg_replace('/(\s)+/', ' ', $name);
		return trim($name, " ._- ");
	}
	public function filterSanitizeDoubleSpace($value)
	{
		$value = addslashes($value);
		$value = trim(preg_replace(\Pico\PicoConst::TRIM_EXTRA_SPACE, " ", $value));
		return $this->trimPunctuation($value);
	}

	public function trimPunctuation($value)
	{
		return trim($value, " ._-/\\ ");
	}

	public function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}


	public function log($content = "", $file = null)
	{
		if ($file == null) {
			$file = dirname(dirname(dirname(__FILE__))) . "/log.txt";
		}
		$fp = fopen($file, 'a');
		fputs($fp, date("Y-m-d H:s:s") . " " . $content . "\r\n");
		fclose($fp);
	}

	public function sortQuestion($test_id)
	{
		$sql = "SELECT `question_id` FROM `edu_question` WHERE `test_id` = '$test_id' ORDER BY `sort_order` ASC ";
		$stmt = $this->database->executeQuery($sql);
		$ret = array();
		$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$ret[] = $data['question_id'];
		}
		$sort_order = 1;
		foreach ($ret as $question_id) {
			$sql = "UPDATE `edu_question` SET `sort_order` = '$sort_order' WHERE `question_id` = '$question_id' ";
			$this->database->executeUpdate($sql, true);
			$sort_order++;
		}
	}

	public function createFilterDb($sql, $params = null)
	{
		if ($params == null) {
			$params = array();
		}

		$stmt = $this->database->executeQuery($sql);
		$options = array();
		if ($stmt->rowCount() > 0) {
			$attributes = array();
			$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($rows as $data) {
				$attributes = array();
				foreach ($params['attributeList'] as $val) {
					$attributes[] = $val['attribute'] . '="' . htmlspecialchars($data[$val['source']]) . '"';
				}
				$selectConditionSource = $params['selectCondition']['source'];
				$selectConditionValueClient = $params['selectCondition']['value'];
				if (
					$selectConditionValueClient != null
					&& !empty($selectConditionValueClient)
					&& $selectConditionValueClient == $data[$selectConditionSource]
				) {
					$attributes[] = 'selected="selected"';
				}

				$captionDelimiter = $params['caption']['delimiter'];
				$captionVals = array();

				foreach ($params['caption']['values'] as $sourceVal) {
					$captionVals[] = $data[$sourceVal];
				}

				$stringAttr = implode(' ', $attributes);
				$stringVal = implode($captionDelimiter, $captionVals);
				$option = '<option ' . $stringAttr . '>' . $stringVal . '</option>';
				$options[] = $option;
			}
		}
		return implode("\r\n", $options);
	}
	public function generateCreateMmeberFromAdmin($admin_id)
	{
		$sql = "SELECT * from edu_admin where admin_id = '$admin_id' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);

			$member_id = addslashes($data['admin_id']);
			$name = addslashes($data['name']);
			$username = addslashes($data['username']);
			$email = addslashes($data['email']);
			$phone = addslashes($data['phone']);
			$gender = addslashes($data['gender']);
			$birth_day = addslashes($data['birth_day']);
			if (empty($birth_day)) {
				$birth_day = "'" . $birth_day . "'";
			} else {
				$birth_day = 'NULL';
			}
			$birth_place = addslashes($data['birth_place']);
			$password = addslashes($data['password']);
			$country_id = addslashes($data['country_id']);
			$state_id = addslashes($data['state_id']);
			$city_id = addslashes($data['city_id']);

			$time_register = $this->database->getLocalDateTime();

			return "
			INSERT INTO `member` (`member_id`, `name`, `username`, `email`, `phone`, `gender`, `birth_day`, `birth_place`, `password`, `country_id`, `state_id`, `city_id`, `time_register`, `active`) VALUES 
			('$member_id', '$name', '$username', '$email', '$phone', '$gender', $birth_day, '$birth_place', '$password', '$country_id', '$state_id', '$city_id', '$time_register', true);
			";
		}
		return null;
	}


	public function getSchoolProgramId($school_program, $school_id)
	{
		$school_program = addslashes($school_program);
		$school_id = addslashes($school_id);

		$sql = "SELECT `edu_school_program`.`school_program_id` 
		FROM `edu_school_program`
		WHERE `edu_school_program`.`name` LIKE '$school_program'
		AND `edu_school_program`.`school_id` = '$school_id'
		LIMIT 0, 1 ";

		$stmt = $this->database->executeQuery($sql);

		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			return $data['school_program_id'];
		} else {
			$school_program_id = $this->database->generateNewId();
			$now = $this->database->getLocalDateTime();
			$sql = "INSERT INTO `edu_school_program` 
			(`school_program_id`, `school_id`, `name`, `time_create`, `time_edit`, `active`) VALUES
			('$school_program_id', '$school_id', '$school_program', '$now', '$now', true)";
			$stmt = $this->database->executeInsert($sql, true);
			if ($stmt->rowCount() > 0) {
				return $school_program_id;
			}
			return null;
		}
	}

	public function getClassId($class, $school_id)
	{
		$class = addslashes($class);
		$school_id = addslashes($school_id);

		$sql = "SELECT `edu_class`.`class_id` 
		FROM `edu_class`
		WHERE `edu_class`.`name` LIKE '$class'
		AND `edu_class`.`school_id` = '$school_id'
		LIMIT 0, 1 ";

		$stmt = $this->database->executeQuery($sql);

		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			return $data['class_id'];
		} else {
			$class_id = $this->database->generateNewId();
			$now = $this->database->getLocalDateTime();
			$sql = "INSERT INTO `edu_class` 
			(`class_id`, `school_id`, `name`, `time_create`, `time_edit`, `active`) VALUES
			('$class_id', '$school_id', '$class', '$now', '$now', true)";
			$stmt = $this->database->executeInsert($sql, true);
			if ($stmt->rowCount() > 0) {
				return $class_id;
			}
			return null;
		}
	}

	public function ifMatch($v1, $v2, $out)
	{
		return $v1 == $v2 ? $out : '';
	}

	public function trueFalse($val, $trueVal, $falseVal)
	{
		return $val ? $trueVal : $falseVal;
	}

	public function selectFromMap($value, $map)
	{
		if ($value == null || $map == null || !is_array($map)) {
			return "";
		}
		if (isset($map[$value])) {
			return $map[trim($value)];
		}
		return "";
	}

	/**
	 * Create row class
	 * @param array $data Array containing data
	 * @param bool $defaultCondition Default condition
	 * @return string
	 */
	public function getRowClass($data, $defaultCondition = false)
	{
		if (!isset($data) || empty($data)) {
			return "";
		}
		$rowclass = array();
		if ($defaultCondition || (isset($data['default']) && $data['default'] == 1)) {
			$rowclass[] = "data-default";
		}
		if (isset($data['blocked']) && $data['blocked'] == 1) {
			$rowclass[] = "data-blocked";
		}
		if (isset($data['active'])) {
			if ($data['active'] == 1) {
				$rowclass[] = "data-active";
			} else if ($data['active'] == 0) {
				$rowclass[] = "data-inactive";
			}
		}
		$rowclass = array_unique($rowclass);
		return trim(implode(' ', $rowclass));
	}

	public function getSchoolGradeList()
	{
		return array(
			'1' => 'Play Group',
			'2' => 'Taman Kanak-Kanak',
			'3' => 'SD Sederajat',
			'4' => 'SMP Sederajat',
			'5' => 'SMA Sederajat',
			'6' => 'Perguruan Tinggi'
		);
	}

	public function getSchoolGradeOption($selected = null)
	{
		$html = "";
		$arr = $this->getSchoolGradeList();
		foreach ($arr as $key => $val) {
			if ($selected != null && $selected == $key) {
				$sel = \Pico\PicoConst::SELECT_OPTION_SELECTED;
			} else {
				$sel = "";
			}
			$html .= "\r\n\t\t<option value=\"" . $key . "\"$sel>$val</option>"; //NOSONAR
		}
		return $html;
	}

	public function getSchoolTypeOption($selected = null)
	{
		$html = "";
		$arr = array('U' => 'Negeri', 'I' => 'Swasta');
		foreach ($arr as $key => $val) {
			if ($selected != null && $selected == $key) {
				$sel = \Pico\PicoConst::SELECT_OPTION_SELECTED;
			} else {
				$sel = "";
			}
			$html .= "\r\n\t\t<option value=\"" . $key . "\"$sel>$val</option>"; //NOSONAR
		}
		return $html;
	}

	/**
	 * Get school grade name
	 * @param string $grade_id School grade ID
	 */
	public function getSchoolGradeName($grade_id)
	{
		$arr = $this->getSchoolGradeList();
		return isset($arr[$grade_id]) ? $arr[$grade_id] : '';
	}

	/**
	 * Fixing input date time for database
	 * @param string $input Date time to be fixed and match to SQL query
	 * @return string Fixed date time for SQL query
	 */
	public function fixInputTimeSQL($input)
	{
		if ($input == null || empty($input)) {
			$input = 'null';
		} else {
			$input = "'$input'";
		}
		return $input;
	}

	/**
	 * Get page title to be printed 
	 */
	public function printPageTitle($pageTitle, $appName)
	{
		return trim($pageTitle . ' - ' . $appName, ' ');
	}

	/**
	 * Add subject to cache
	 * @param string $subject Subject
	 */
	public function addSubject($subject)
	{
		$subject = trim($subject);
		$subject_id = md5(stripslashes($subject));

		$sql = "SELECT `edu_subject`.`subject_id` 
		FROM `edu_subject`
		WHERE `edu_subject`.`subject_id` = '$subject_id'
		";

		$stmt = $this->database->executeQuery($sql);

		if (!$stmt->rowCount() > 0) {
			$sql = "INSERT INTO `edu_subject` 
			(`subject_id`, `name`, `sort_order`) VALUES
			('$subject_id', '$subject', 0)";
			$this->database->executeInsert($sql, true);
		}
	}

	/**
	 * Get subject from cache as list
	 * @return mixed 
	 */
	public function getSubjectList()
	{
		$sql = "SELECT `edu_subject`.*
		FROM `edu_subject`
		ORDER BY `sort_order` ASC, `name` ASC
		";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$ret = array();
			foreach ($list as $val) {
				$ret[$val['name']] = $val['subject_id'];
			}
			return $ret;
		}
		return new \stdClass;
	}

	/**
	 * Get search query from URL
	 * @return string Search query
	 */
	public function getSearchQueryFromUrl()
	{
		return htmlspecialchars(rawurldecode((trim(@$_GET['q']))));
	}


	public function getWebsocketHost()
	{

		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		if (stripos($host, ":") !== false) {
			$arr = explode(":", $host);
			$host = $arr[0];
		}
		if ($host == '::1') {
			$host = '127.0.0.1'; // Many browser not support ::1
		}
		global $cfg;
		return 'ws://' . $host . ':' . $cfg->ws_port;
	}

	public function selectOptionNumbering($selected = null)
	{
		global $cfg;
		$numberring = $cfg->numbering;
		foreach ($numberring as $key => $val) {
			while (count($numberring[$key]) > 4) {
				array_pop($numberring[$key]);
			}
			$numberring[$key][] = "&#8230;";
		}
		$html = "";
		$sel = "";
		foreach ($numberring as $key => $val) {
			$label = implode(", ", $val);
			if ($selected != null && $selected == $key) {
				$sel = \Pico\PicoConst::SELECT_OPTION_SELECTED;
			} else {
				$sel = "";
			}
			$html .= "\r\n\t\t<option value=\"" . $key . "\"$sel>$label</option>";
		}
		return $html;
	}

	public function selectOptionAlertTime($selected = null)
	{
		$alertTimeArray = array(
			'120' => '2 menit',
			'300' => '5 menit',
			'600' => '10 menit',
			'900' => '15 menit'
		);

		$html = "";
		$sel = "";
		foreach ($alertTimeArray as $key => $val) {
			if ($selected != null && $selected == $key) {
				$sel = \Pico\PicoConst::SELECT_OPTION_SELECTED;
			} else {
				$sel = "";
			}
			$html .= "\r\n\t\t<option value=\"" . $key . "\"$sel>$val</option>";
		}
		return $html;
	}

	/**
	 * Get actual file name accessed by client
	 *
	 * @return string
	 */
	public function gateBaseSelfName()
	{
		return basename($_SERVER['PHP_SELF']);
	}
}
