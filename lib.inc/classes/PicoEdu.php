<?php

class PicoEdo 
{
	const SPAN_OPEN = '<span>';
	const SPAN_CLOSE = '</span>';
	const SPAN_TITLE = '<span title="';
	const TRIM_EXTRA_SPACE = "/\s+/";
	const TRIM_NON_NUMERIC = "/[^0-9]/i";

	public PicoDatabase $database;
	public function __construct(PicoDatabase $database)
	{
		$this->database = $database;
	}
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
	public function checkValidName($full_name)
	{
		$full_name = trim(preg_replace(self::TRIM_EXTRA_SPACE, " ", $full_name));
		$arr_name = explode(" ", $full_name);
		$valid_name = 1;
		foreach ($arr_name as $name) {
			$valid_part = 1;
			$name = str_replace(
				array('A', 'E', 'I', 'O', 'U', 'Y', 'a', 'e', 'i', 'o', 'u', 'y'),
				' ',
				$name
			);
			$name = trim(preg_replace(self::TRIM_EXTRA_SPACE, " ", $name));
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
		return $valid_name;
	}
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

	
	public function timeToText($tm) //NOSONAR
	{
		global $language_res;
		global $language_id;
		$text = '';
		$fulltime = date('j F Y H:i:s', time() - $tm);
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
	public function get_country_name($country_id)
	{
		$sql = "SELECT `name` from `country` where `country_id` = '$country_id' ";
		$stmt = $this->database->executeQuery($sql);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return @$data['name'];
	}



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

	public function getExistsingUser($user_data)
	{
		$now = $this->getLocalDateTime();
		$ip = $_SERVER['REMOTE_ADDR'];

		$name = $user_data['name'];

		$name = trim(preg_replace("/[^a-zA-Z 0-9\.\-]+/", " ", $name), " -. ");
		$name = trim(preg_replace(self::TRIM_EXTRA_SPACE, " ", $name));

		$gender = $user_data['gender'];
		$email = trim($user_data['email'], " \r\n\t ");
		$phone = $user_data['phone'];
		$password = $user_data['password'];
		$birth_day = $user_data['birth_day'];
		$language = $user_data['language'];
		$country_id = $user_data['country_id'];

		$uname = str_replace(" ", "", $name);
		$uname = substr($uname, 0, 16);
		$oke = false;
		$username = "";
		while ($oke === false || $oke == '') {
			$username = $oke = $this->isValidUsername($uname);
			$this->log($oke." ".__LINE__);
			if ($oke == '' || $oke === false) {
				$uname = $uname . mt_rand(11, 99);
				$username = $uname;
			}
		}
		$username = addslashes($username);
		$sql = "SELECT `member`.* 
			from `member` 
			where `name` like '$name' and `birth_day` like '$birth_day' 
			";
		$this->log(__LINE__." ".$sql);
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount()) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return array(
				'member_id' => $data['member_id'],
				'name' => $data['name'],
				'birth_day' => $data['birth_day'],
				'username' => $data['username'],
				'email' => $data['email']
			);
		} else {
			$member_id = $this->database->generateNewId();
			$auth = md5($username . $email);
			$sql = "INSERT INTO `member` 
			(`member_id`, `name`, `username`, `email`, `gender`, `birth_day`, `password`, `auth`, `language`, `phone`, `country_id`, 
			`time_register`, `last_activity_ip`, `last_activity_time`, `last_seen_ip`, `last_seen_time`, `active`) VALUES 
			('$member_id', '$name', '$username', '$email', '$gender', '$birth_day', '$password', '$auth', '$language', '$phone', '$country_id', 
			'$now', '$ip', '$now', '$ip', '$now', '1');
			";
			$this->log(__LINE__." ".$sql);
			$this->database->executeInsert($sql);
			
			return array(
				'member_id' => $member_id,
				'name' => stripslashes($name),
				'birth_day' => stripslashes($birth_day),
				'username' => stripslashes($name),
				'email' => stripslashes($email)
			);
		}
	}
	public function isValidUsername($name)
	{
		$username = $this->getValidUsername($name);
		if ($username != '') {
			$sql = "SELECT `member_id`, `email`, `username`
			from `member`
			where `username` like '$username'
			";
			$stmt = $this->database->executeQuery($sql);
			if($stmt->rowCount() == 0)
			{
				return $username;
			}
		}
		return false;
	}

	public function createPagination($module, $totalrecord, $resultperpage = 1, $numberofpage = 1, $offset = 0, $arrayget, $showfirstandlast = true, $first = "First", $last = "Last", $prev = "Prev", $next = "Next")
	{
		$result = array();
		$result[0] = new StdClass();
		$result[1] = new StdClass();
		$arg = "";
		$pg = new StdClass();
		$pg->text = "";
		$pg->ref = "";
		if ($totalrecord <= $resultperpage) {
			return array();
		}
		if (!is_array($arrayget)) {
			$arrayget = array($arrayget);
		}
		foreach ($arrayget as $item) {
			$arg .= "&$item=" . @$_GET[$item];
		}
		$arg = "$module?" . trim($arg, "&");
		$allpage = ceil($totalrecord / $resultperpage);
		$curpage = abs(ceil($offset / $resultperpage)) + 1;
		$startpage = abs(ceil($curpage - floor($numberofpage / 2)));
		if ($startpage < 1) {
			$startpage = 1;
		}
		$endpage = $startpage + $numberofpage - 1;
		$lastpage = ceil($totalrecord / $resultperpage);


		if ($endpage > $lastpage) {
			$endpage = $lastpage;
		}
		$pg->text = "";
		$pg->ref = "";
		$pg->ref_first = 0;
		$pg->str_first = $first;
		$pg->str_prev = $prev;
		$pg->ref_prev = ($curpage - 2) * $resultperpage;
		if ($pg->ref_prev < 0) {
			$pg->ref_prev = 0;
		}
		$pg->str_next = $next;
		$pg->ref_next = ($curpage) * $resultperpage;
		$pg->str_last = $last;
		$pg->ref_last = floor($totalrecord / $resultperpage) * $resultperpage;
		if ($pg->ref_last == $totalrecord) {
			$pg->ref_last = $totalrecord - $resultperpage;
		}

		$result[0]->text = $pg->str_first;
		$result[0]->ref = str_replace("?&", "?", $arg . "&offset=" . $pg->ref_first);
		$result[0]->sel = 0;
		if ($curpage >= 0) {
			$result[1]->text = $pg->str_prev;
			$result[1]->ref = str_replace("?&", "?", $arg . "&offset=" . $pg->ref_prev);
			$result[1]->sel = 0;
		}
		for ($j = 2, $i = $startpage; $i <= ($endpage); $i++, $j++) {
			$pn = $i;
			$result[$j] = new StdClass();
			$result[$j]->text = "$pn";
			$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . (($i - 1) * $resultperpage));
			if ($curpage == $i) {
				$result[$j]->sel = 1;
			} else {
				$result[$j]->sel = 0;
			}
		}
		if ($endpage < $lastpage) {
			$result[$j] = new StdClass();
			$result[$j]->text = $pg->str_next;
			$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . $pg->ref_next);
			$result[$j]->sel = 0;
			$j++;
		}
		$result[$j] = new StdClass();
		$result[$j]->text = $pg->str_last;
		$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . $pg->ref_last);
		$result[$j]->sel = 0;
		return $result;
	}

	public function getArrayClass($school_id)
	{
		$sql = "SELECT `class_id`, `name` from `edu_class` where `school_id` = '$school_id' ";
		$stmt = $this->database->executeQuery($sql);
		$ret = array();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
			$arr = json_decode($json);
			foreach ($arr as $question) {
				$question_id = $question[0] * 1;
				$option_id = $question[1] * 1;
				$sql2 = "SELECT `edu_option`.`question_id`, `edu_option`.`option_id`, 
				(select `edu_question`.`basic_competence` 
					from `edu_question` 
					where `edu_question`.`question_id` = `edu_option`.`question_id`) as `basic_competence`,
				`edu_option`.`score`
				from `edu_option`
				where `edu_option`.`question_id` = '$question_id' and `edu_option`.`option_id` = '$option_id';
				";
				$stmt = $this->database->executeQuery($sql2);
				if ($stmt->rowCount() > 0) {
					$data2 = $stmt->fetch(PDO::FETCH_ASSOC);
					$basic_competence = $data2['basic_competence'];
					$basic_competence = preg_replace(self::TRIM_NON_NUMERIC, ".", $basic_competence);
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
		$sql = "SELECT `basic_competence` from `edu_question` where `test_id` = '$test_id' group by `basic_competence` ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$result = array();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $data) {
				$basic_competence = $data['basic_competence'];
				$basic_competence = preg_replace(self::TRIM_NON_NUMERIC, ".", $basic_competence);
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
			foreach ($score as $key => $val) {
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
		$sql = "select * from `profile` where `name` = '$name' and `school_id` = '$school' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$sql = "update `profile` set `value` = '$value' where `name` = '$name' and `school_id` = '$school'";
		} else {
			$profil_id = $this->database->generateNewId();
			$sql = "INSERT INTO `profile` 
			(`profil_id`, `school_id`, `name`, `value`) values
			('$profil_id', '$school', '$name', '$value')";
		}
		$this->database->executeInsert($sql);
	}
	public function getProfile($school, $name, $default = "")
	{
		$sql = "select * from `profile` where `name` = '$name' and `school_id` = '$school' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data['value'];
		} else {
			return $default;
		}
	}
	public function deleteProfile($school, $name)
	{
		$sql = "DELETE FROM `profile` where `name` = '$name' and `school_id` = '$school' ";
		$this->database->executeDelete($sql);
	}
	public function getApplicationVersion()
	{
		$sql = "SELECT `version_id` from `version` where `active` = '1' and `current_version` = '1' ";
		$stmt = $this->database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
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
		$sql = "SELECT `token` from `edu_token` where `active` = '1'
		";
		$stmt = $this->database->executeQuery($sql);
		$active_token = array();
		$new_token = array();
		$temporary_token = array();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$active_token[] = $data['token'];
		}
		$temporary_token = $active_token;
		for ($i = 0; $i < $count; $i++) {
			$token = mt_rand($min, $max);
			if (in_array($token, $temporary_token)) {
				$i--;
			}
			$new_token[] = $token;
			$temporary_token[] = $token;
		}
		return $new_token;
	}
	public function logInvalidLogin($member_id, $signin_type, $time, $time_limit, $count_limit)
	{
		$start_time = date('Y-m-d H:i:s', strtotime($time) - $time_limit);

		$sql = "DELETE FROM `edu_invalid_signin` 
		where `member_id` = '$member_id' and `signin_type` = '$signin_type' and `signin_time` < '$start_time'
		";
		$this->database->executeDelete($sql);

		$sql = "INSERT INTO `edu_invalid_signin` 
		(`member_id`, `signin_type`, `signin_time`) values
		('$member_id', '$signin_type', '$time')
		";
		$this->database->executeInsert($sql);

		$sql = "select * 
		from `edu_invalid_signin` 
		where `member_id` = '$member_id' and `signin_type` = '$signin_type' and `signin_time` >= '$start_time'
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
		$email = trim(preg_replace(self::TRIM_EXTRA_SPACE, "", $email));
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
		$sql = "select * from `edu_student` 
		where `school_id` = '$school_id' 
		and (`reg_number` like '$reg_number' and `reg_number` != '') ";
		$stmt = $this->database->executeQuery($sql);
		return $stmt->rowCount() > 0;
	}
	public function checkTeacher($school_id, $reg_number, $reg_number_national, $name) //NOSONAR
	{
		$sql = "select * from `edu_teacher` 
		where `school_id` = '$school_id' 
		and (`reg_number` like '$reg_number' and `reg_number` != '') ";
		$stmt = $this->database->executeQuery($sql);
		return $stmt->rowCount() > 0;
	}

	public function getTextScore($answer_id, $compress = false) //NOSONAR
	{
		$sql = "SELECT `edu_answer`.`answer` from `edu_answer` where `edu_answer`.`answer_id` = '$answer_id' ";
		$stmt = $this->database->executeQuery($sql);
		$result = array();
		if ($stmt->rowCount() > 0) {
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($data['answer'] != '') {
				$data['answer'] = str_replace(",]", ",0]", $data['answer']);
				$json = '[' . $data['answer'] . ']';
				$arr = json_decode($json);
				foreach ($arr as $question) {
					$question_id = $question[0] * 1;
					$option_id = $question[1] * 1;
					$sql2 = "SELECT `edu_option`.`question_id`, `edu_option`.`option_id`, 
					(select `edu_question`.`basic_competence` 
						from `edu_question` 
						where `edu_question`.`question_id` = `edu_option`.`question_id`) as `basic_competence`,
					`edu_option`.`score`
					from `edu_option`
					where `edu_option`.`question_id` = '$question_id' and `edu_option`.`option_id` = '$option_id';
					";
					$stmt2 = $this->database->executeQuery($sql2);
					if ($stmt2->rowCount() > 0) {
						$data2 = $stmt2->fetch(PDO::FETCH_ASSOC);

						$basic_competence = $data2['basic_competence'];
						$basic_competence = preg_replace(self::TRIM_NON_NUMERIC, ".", $basic_competence);
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
		(`school_id`, `student_id`, `test_id`, `sessions_id`, `time_enter`, `ip_enter`, `status`) values
		('$school_id', '$student_id', '$test_id', '$sessions_id', '$time', '$ip', '1')
		";
		$this->database->executeInsert($sql);
	}
	public function logoutTest($school_id, $student_id, $test_id, $sessions_id, $time, $ip)
	{
		$sql = "update `edu_test_member` set `time_exit` = '$time', `ip_exit` = '$ip', `status` = '2'
		where `school_id` = '$school_id' and `student_id` = '$student_id' and `test_id` = '$test_id' and `sessions_id` = '$sessions_id'
		";
		$this->database->executeUpdate($sql);
	}

	public function brToNewLineEncoded($content)
	{
		return str_replace(array("\\&lt;br /&gt;", " \\\r\n", "\\\n"), array("\\\\", " \\\r\n", "\\\n"), $content);
	}

	public function getGradeName($grade)
	{
		$arr = array(
			"0"=>"",
			"1"=>"Tingkat 1",
			"2"=>"Tingkat 2",
			"3"=>"Tingkat 3",
			"4"=>"Tingkat 4",
			"5"=>"Tingkat 5",
			"6"=>"Tingkat 6",
			"7"=>"Tingkat 7",
			"8"=>"Tingkat 8",
			"9"=>"Tingkat 9",
			"10"=>"Tingkat 10",
			"11"=>"Tingkat 11",
			"12"=>"Tingkat 12",
			"13"=>"Perguruan Tinggi"

		);
		return @$arr[$grade];
	}

	public function createGradeOption($grade)
	{
		$arr = array();
		for($i = 1; $i<=12; $i++)
		{
			$sel = $i == $grade ? ' selected="selected"' : '';
			$arr[] = '<option value="'.$i.'" '.$sel.'>Tingkat '.$i.'</option>';
		}

		return implode("\r\n", $arr);
	}
	public function getGenderName($gender)
	{
		if($gender == 'M')
		{
			return 'Laki-Laki';
		} 
		else if($gender == 'W')
		{
			return 'Perempuan';
		}
		return '';
	}

	public function getLocalDateTime()
	{
		return date('Y-m-d H:i:s');
	}

	public static function arrayToObject($inputArray) {
		if (!is_array($inputArray)) 
		{
			return $inputArray;
		}
	
		$object = new stdClass();
		if (is_array($inputArray) && count($inputArray) > 0) {
			foreach ($inputArray as $name=>$value) {
				$name = strtolower(trim($name));
				if (!empty($name)) {
					$object->$name = PicoEdo::arrayToObject($value);
				}
			}
		return $object;
		}
		else {
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
		$value = trim(preg_replace(self::TRIM_EXTRA_SPACE, " ", $value));
		return trim($value, " ._-/\\ ");
	}

	
	public function log($content = "", $file = null)
	{
		if($file == null)
		{
			$file = dirname(dirname(dirname(__FILE__))) . "/log.txt";
		}
		$fp = fopen($file, 'a');
		fputs($fp, date("Y-m-d H:s:s") . " " . $content . "\r\n");
		fclose($fp);
	}

	public function sortQuestion($test_id)
	{
		$sql = "SELECT `question_id` from `edu_question` where `test_id` = '$test_id' order by `order` asc ";
		$stmt = $this->database->executeQuery($sql);
		$ret = array();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			$ret[] = $data['question_id'];
		}
		$order = 1;
		foreach ($ret as $question_id) {
			$sql = "update `edu_question` set `order` = '$order' where `question_id` = '$question_id' ";
			$this->database->executeUpdate($sql);
			$order++;
		}
	}

	/*
	Commented
	$picoEdu->createFilterDb($sql,
		array(
			'attributeList'=>array(
				array('attribute'=>'data-code', 'source'=>'phone_code'),
				array('attribute'=>'value', 'source'=>'country_id')
			),
			'selectCondition'=>array(
				'source'=>'country_id',
				'value'=>null
			),
			'caption'=>array(
				'delimiter'=>'',
				'values'=>array(
					'name'
				)
			)
		)
	);
	*/
	public function createFilterDb($sql, $params = null)
	{
		if($params == null)
		{
			$params = array();
		}

		$stmt = $this->database->executeQuery($sql);
		$options = array();
		if($stmt->rowCount() > 0)
		{
			$attributes = array();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows as $data)
			{
				$attributes = array();
				foreach($params['attributeList'] as $val)
				{
					$attributes[] = $val['attribute'] . '="' . htmlspecialchars($data[$val['source']]) . '"';
				}
				$selectConditionSource = $params['selectCondition']['source'];
				$selectConditionValueClient = $params['selectCondition']['value'];
				if($selectConditionValueClient != null 
					&& !empty($selectConditionValueClient) 
					&& $selectConditionValueClient == $data[$selectConditionSource]
					)
				{
					$attributes[] = 'selected="selected"';
				}

				$captionDelimiter = $params['caption']['delimiter'];
				$captionVals = array();

				foreach($params['caption']['values'] as $sourceVal)
				{
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
		$sql = "select * from edu_admin where admin_id = '$admin_id' ";
		$stmt = $this->database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);

			/**
			 * INSERT INTO `mini_picopi`.`edu_admin` (`admin_id`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `username`, `admin_level`, `token_admin`, `email`, `phone`, `address`, `country_id`, `state_id`, `city_id`, `password`, `password_initial`, `auth`, `time_create`, `time_edit`, `time_last_activity`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `ip_last_activity`, `blocked`, `active`) VALUES 
			 * (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			 */

			/**
			 * generateCreateMmeberFromAdmin
			 * INSERT INTO `member` (`member_id`, `name`, `username`, `email`, `phone`, `gender`, `birth_day`, `birth_place`, `password`, `auth`, `url`, `background`, `circle_avatar`, `picture_hash`, `picture_crop_position`, `img_360_compress`, `show_compass`, `autoplay_360`, `autorotate_360`, `following`, `follower`, `language`, `country_id`, `state_id`, `city_id`, `state`, `city`, `time_register`, `last_activity_ip`, `last_activity_time`, `last_update_avatar_time`, `last_seen_ip`, `last_seen_time`, `confirmed`, `blocked`, `active`) VALUES 
			 * (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			 */

			$member_id = addslashes($data['admin_id']);
			$name = addslashes($data['name']);
			$username = addslashes($data['username']);
			$email = addslashes($data['email']);
			$phone = addslashes($data['phone']);
			$gender = addslashes($data['gender']);
			$birth_day = addslashes($data['birth_day']);
			if(empty($birth_day))
			{
				$birth_day = "'" . $birth_day . "'";
			}
			else
			{
				$birth_day = 'NULL';
			}
			$birth_place = addslashes($data['birth_place']);
			$password = addslashes($data['password']);
			$country_id = addslashes($data['country_id']);
			$state_id = addslashes($data['state_id']);
			$city_id = addslashes($data['city_id']);

			$time_register = $this->getLocalDateTime();

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
		from `edu_school_program`
		where `edu_school_program`.`name` like '$school_program'
		and `edu_school_program`.`school_id` = '$school_id'
		limit 0, 1";

		$stmt = $this->database->executeQuery($sql);

		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data['school_program_id'];
		}
		else
		{
			$school_program_id = $this->database->generateNewId();
			$now = $this->getLocalDateTime();
			$sql = "INSERT INTO `edu_school_program` 
			(`school_program_id`, `school_id`, `name`, `time_create`, `time_edit`, `active`) values
			('$school_program_id', '$school_id', '$school_program', '$now', '$now', true)";
			$stmt = $this->database->executeInsert($sql);
			if($stmt->rowCount() > 0)
			{
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
		from `edu_class`
		where `edu_class`.`name` like '$class'
		and `edu_class`.`school_id` = '$school_id'
		limit 0, 1";

		$stmt = $this->database->executeQuery($sql);

		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data['class_id'];
		}
		else
		{
			$class_id = $this->database->generateNewId();
			$now = $this->getLocalDateTime();
			$sql = "INSERT INTO `edu_class` 
			(`class_id`, `school_id`, `name`, `time_create`, `time_edit`, `active`) values
			('$class_id', '$school_id', '$class', '$now', '$now', true)";
			$stmt = $this->database->executeInsert($sql);
			if($stmt->rowCount() > 0)
			{
				return $class_id;
			}
			return null;
		}

	}

	public function getSchoolGradeName($grade_id)
	{
		$arr = array(
			'1'=>'Play Group',
			'2'=>'Taman Kanak-Kanak',
			'3'=>'SD Sederajat',
			'4'=>'SMP Sederajat',
			'5'=>'SMA Sederajat',
			'6'=>'Perguruan Tinggi'
		);
		return isset($arr[$grade_id]) ? $arr[$grade_id] : '';
	}
}
