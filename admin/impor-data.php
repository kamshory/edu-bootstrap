<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}

/**
 * Import data preprocessor
 */
class ImportExcel{

	/**
	 * Check if school use national ID or not
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @return bool true if school use national ID and false if school is not use national ID
	 */
	public function isUseNationalId($objWorksheetSource, $sheetNameSchool)
	{
		$useNationalId = false;
		try
		{
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetNameSchool);
			$highestRow = $objWorksheet->getHighestRow(); 
			$highestColumn = $objWorksheet->getHighestColumn(); 
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			
			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for($row = 2; $row <= $highestRow; ++$row) 
			{
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) 
				{
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if(strtolower($data['use_national_id']) == 'y' 
					|| strtolower($data['use_national_id']) == 'yes'
					|| strtolower($data['use_national_id']) == 'ya'
					|| strtolower($data['use_national_id']) == 'true'
					)
				{
					$useNationalId = true;
					break;
				}
			}
		}
		catch(Exception $e)
		{
			// Do nothing
		}
		return $useNationalId;
	}

	/**
	 * Validate imported data
	 * @param \PHPExcel $objWorksheetSource Worksheet
	 * @param string $sheetNameSchool Sheet name for school
	 * @param string $sheetNameStudent Sheet name for student
	 * @param string $columnNameStudent Lower case of column name name student ID
	 * @param string $sheetNameTeacher Sheet name for teacher
	 * @param string $columnNameTeacher Lower case of column name name teacher ID
	 * @return array Contain response_code and response_text
	 */
	public function validate($objWorksheetSource, $sheetNameSchool, $sheetNameStudent, $columnNameStudent, $sheetNameTeacher, $columnNameTeacher)
	{
		$useNationalId = $this->isUseNationalId($objWorksheetSource, $sheetNameSchool);
		$validData1 = true;
		$validData2 = true;		
		$message = "Sukses";
		$response_code = "00";

		if($useNationalId)
		{
			$validData1 = $this->validData($objWorksheetSource, $sheetNameStudent, $columnNameStudent);
			$validData2 = $this->validData($objWorksheetSource, $sheetNameTeacher, $columnNameTeacher);		

			if($useNationalId)
			{
				if(!$validData1 && !$validData2)
				{
					$message = "Data siswa dan guru tidak lengkap";
					$response_code = "05";
				}
				else if(!$validData1)
				{
					$message = "Data siswa tidak lengkap";
					$response_code = "05";
				}
				else if(!$validData2)
				{
					$message = "Data guru tidak lengkap";
					$response_code = "05";
				}
				if($validData1 && $validData2)
				{
					$response_code = "00";
				}
			}
			
		}
		return array(
			'response_code'=>$response_code,
			'response_text'=>$message
		);
	}

	public function validData($objWorksheetSource, $sheetName, $columnName)
	{
		$validData = true;
		try
		{
			$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetName);
			$highestRow = $objWorksheet->getHighestRow(); 
			$highestColumn = $objWorksheet->getHighestColumn(); 
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			
			$fieldArray = array();
			$row = 1;
			for ($col = 0; $col < $highestColumnIndex; ++$col) {
				$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
			}
			for($row = 2; $row <= $highestRow; ++$row) 
			{
				$data = array();
				for ($col = 0; $col < $highestColumnIndex; ++$col) 
				{
					$data[$fieldArray[$col]] = $this->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				if(empty($data[$columnName]))
				{
					$validData = false;
					break;
				}
			}
		}
		catch(Exception $e)
		{
			// Do nothing
		}
		return $validData;
	}
	
	public function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}

if(isset($_POST['upload']) && isset($_FILES['file']['name']))
{ 
	$country_id = 'ID';
	$admin_id = $adminLoggedIn->admin_id;
	$time_create = $time_edit = $database->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$admin_create = $admin_edit = $admin_id;
	/** Include PHPExcel_IOFactory */
	require_once dirname(dirname(__FILE__)) . '/lib.inc/PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php';
	
	if(isset($_FILES['file']['tmp_name']))
	{
		$school_id = "";

		$myschool = true;
		$ip = str_replace(array(':','.'), '-', $_SERVER['REMOTE_ADDR']);

		if(!file_exists(dirname(__FILE__)."/tmp"))
		{
			mkdir(dirname(__FILE__) . "/tmp", 755);
		}

		$path = dirname(__FILE__)."/tmp/$ip-".mt_rand(1000000, 9000000).".xlsx";
		$success = move_uploaded_file($_FILES['file']['tmp_name'], $path);
		$fileSync->createFile($path, true);
		$errors = 0;
		if($success && file_exists($path))
		{
			$callStartTime = microtime(true);
			
			
			$name_school = '';
			
			// import data school
			// mulai
			$objWorksheetSource = PHPExcel_IOFactory::load($path);

			$importExcel = new ImportExcel();

			$response = $importExcel->validate($objWorksheetSource, 'SCHOOL', 'STUDENT', 'reg_number_national', 'TEACHER', 'reg_number_national');

			try{
				$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName('SCHOOL');
				$highestRow = $objWorksheet->getHighestRow(); 
				$highestColumn = $objWorksheet->getHighestColumn(); 
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
				
				$fieldArray = array();
				$row = 1;
				for ($col = 0; $col < $highestColumnIndex; ++$col) {
					$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
				}
				
				
				for($row = 2; $row <= $highestRow; ++$row) 
				{
					$data = array();
					for ($col = 0; $col < $highestColumnIndex; ++$col) 
					{
						$data[$fieldArray[$col]] = $picoEdu->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
					}
					$name = $picoEdu->filterSanitizeName(@$data['name'], true);
					
					$school_code = strtolower($name);
					$school_code = preg_replace("/[^a-z\-\d]/i","-",$school_code);
					$school_code = str_replace("---", "-", $school_code);
					$school_code = str_replace("--", "-", $school_code);
					$school_code = str_replace("--", "-", $school_code);
					
					$school_code = addslashes($school_code);
					
					$address = addslashes(@$data['address']);
					$phone = addslashes($picoEdu->fixPhone(@$data['phone']));
					$email = addslashes(@$data['email']);
					$email = $picoEdu->filterEmailAddress($email);
					$useNationalId = addslashes(@$data['use_national_id']);
					$principal = addslashes(@$data['principal']);
					$public_private = addslashes(@$data['public_private']);
					$school_grade = addslashes(@$data['school_grade']);
					$country_id = strtoupper(addslashes(@$data['country']));
					$language = strtolower(addslashes(@$data['language']));
					
					$token_school = md5($name.'-'.time().'-'.mt_rand(111111, 999999));
					
					$name_school = $name;
					if(empty($name))
					{
						continue;
					}

					$school_id = $database->generateNewId();
					
					$sql = "INSERT INTO `edu_school` 
					(`school_id`, `school_code`, `token_school`, `name`, `use_national_id`, `school_grade_id`, `public_private`, `principal`, `address`, `phone`, 
					`email`, `language`, `country_id`, `time_import_first`, `admin_import_first`, `ip_import_first`, 
					`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
					('$school_id', '$school_code', '$token_school', '$name', '$useNationalId', '$school_grade', '$public_private', '$principal', '$address', '$phone', 
					'$email', '$language', '$country_id', '$time_create', '$admin_create', '$ip_create', 
					'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 1);
					";

					$stmt = $database->executeInsert($sql, true);
					if($stmt->rowCount() == 0)
					{
						$myschool = false;
					}
					if($errors)
					{
						break;
					}
					break;
				}
			}
			catch(Exception $e)
			{
				// Do nothing
			}
			
			if(empty($school_id))
			{
				$sql = "SELECT `school_id` FROM `edu_school` WHERE `name` LIKE '$name_school' ";
				$stmt = $database->executeQuery($sql);
				$data_school = $stmt->fetch(\PDO::FETCH_ASSOC);
				$useNationalId = $data_school['use_national_id'];
				$school_id = $data_school['school_id'];
			}
			
			if(!$myschool)
			{
				$sql = "SELECT `edu_school`.*
				FROM `edu_school`
				LEFT JOIN (`edu_member_school`) ON (`edu_member_school`.`school_id` = `edu_school`.`school_id` AND `edu_member_school`.`role` = 'A')
				WHERE `edu_school`.`school_id` = '$school_id' AND `edu_member_school`.`member_id` = '$admin_id' 
				";
				$stmt = $database->executeQuery($sql);
				if($stmt->rowCount() == 0)
				{
					$errors++;
					// yang bersangkutan bukan administrator sekolah
				}
			}
			// jika terjadi error, maka batalkan semua proses
			
			if($errors == 0)
			{
				// add me first
				$sql = "SELECT * FROM `member` WHERE `member_id` = '$admin_id' ";
				$stmt2 = $database->executeQuery($sql);

				if ($stmt2->rowCount() == 0) {
					$sqlInsert = $picoEdu->generateCreateMmeberFromAdmin($admin_id);
					$stmt2 = $database->executeInsert($sqlInsert, true);
					if ($stmt2->rowCount() > 0) {
						$stmt2 = $database->executeQuery($sql);
					}
				}

				if ($stmt2->rowCount() > 0) {
					$data2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
					$username = addslashes($data2['username']);

					$name = $picoEdu->filterSanitizeName($data2['name']);

					$gender = $picoEdu->mapGender(addslashes($data2['gender']));
					$birth_day = addslashes($data2['birth_day']);
					$language = addslashes($data2['language']);
					$country_id = addslashes($data2['country_id']);
					$email = addslashes($data2['email']);
					$email = $picoEdu->filterEmailAddress($email);
					$phone = addslashes($data2['phone']);
					$password = addslashes($data2['password']);
					$token_admin = md5($data2['phone'] . "-" . $data2['email'] . "-" . time() . "-" . mt_rand(111111, 999999));

					if (!empty($name)) {

						if (!empty($name) && !empty($email)) {

							$sql2 = "UPDATE `edu_admin` SET
							`school_id` = '$school_id'
							WHERE `admin_id` = '$admin_id'
							";
							try {
								$database->executeUpdate($sql2, true);
							} catch (\PDOException $e) {
								// Do nothing
							}

							$sql2 = "INSERT INTO `edu_member_school` 
							(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
							('$admin_id', '$school_id', 'A', '$time_create', true)
							";
							try {
								$database->executeInsert($sql2, true);
							} catch (\PDOException $e) {
								// Do nothing
							}
						}


						// import data school
						// selesai


						// import data admin school
						// mulai
						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName('ADMIN');
							$highestRow = $objWorksheet->getHighestRow();
							$highestColumn = $objWorksheet->getHighestColumn();
							$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

							$fieldArray = array();
							$row = 1;
							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
							}

							for ($row = 2; $row <= $highestRow; ++$row) {
								$data = array();
								for ($col = 0; $col < $highestColumnIndex; ++$col) {
									$data[$fieldArray[$col]] = $picoEdu->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
								}

								$name = $picoEdu->filterSanitizeName(@$data['name']);

								$gender = $picoEdu->mapGender(addslashes(trim(@$data['gender'])));
								$bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
								$birth_day = addslashes(excel2MySQLDate($bd));
								$phone = addslashes($picoEdu->fixPhone(trim(@$data['phone'])));
								$email = addslashes(trim(@$data['email']));
								$email = $picoEdu->filterEmailAddress($email);
								$password = addslashes(trim(@$data['password']));
								$address = addslashes(trim(@$data['address']));

								if (empty($name)) {
									continue;
								}

								$time_create = $time_edit = $database->getLocalDateTime();
								$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
								$admin_create = $admin_edit = $admin_id;

								$token_admin = md5($data['phone'] . "-" . $data['email'] . "-" . time() . "-" . mt_rand(111111, 999999));

								$user_data = array();
								$user_data['name'] = $name;
								$user_data['gender'] = $gender;
								$user_data['email'] = $email;
								$user_data['phone'] = $phone;
								$user_data['password'] = md5(md5($password));
								$user_data['birth_day'] = $birth_day;
								$user_data['address'] = $address;
								$user_data['country_id'] = $country_id;
								$user_data['language'] = $language;
								if (!empty($name) && !empty($email)) {
									$chk = $picoEdu->getExistsingUser($user_data);
									$admin_id = addslashes($chk['member_id']);
									$username = addslashes($chk['username']);
									$passwordHash = md5(md5($password));

									$db_fixed = 'NULL';
									if(!empty($birth_day))
									{
										$db_fixed = "'" . $birth_day . "'";
									}

									$sql = "INSERT INTO `edu_admin` 
									(`admin_id`, `school_id`, `admin_level`, `username`, `name`, `token_admin`, `email`, `phone`, `password`, 
									`password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
									`ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
									('$admin_id', '$school_id', '2', '$username', '$name', '$token_admin', '$email', '$phone', '$passwordHash', 
									'$password', '$gender', $db_fixed, '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
									'$ip_create', '$ip_edit', '0', '1');
									";

									$database->executeInsert($sql, true);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
									('$admin_id', '$school_id', 'A', '$time_create', true)
									";
									$database->executeInsert($sql2, true);
								} else {
									// Do nothing
								}

							}
						} catch (Exception $e) {
							// Do nothing
						}
						// import data admin school
						// selesai

						// import data class
						// mulai
						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName('CLASS');
							$highestRow = $objWorksheet->getHighestRow();
							$highestColumn = $objWorksheet->getHighestColumn();
							$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

							$fieldArray = array();
							$row = 1;
							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
							}

							$o = array();
							$kode_school_program = 1;
							for ($row = 2; $row <= $highestRow; ++$row) {
								$data = array();
								for ($col = 0; $col < $highestColumnIndex; ++$col) {
									$data[$fieldArray[$col]] = $picoEdu->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
								}

								$name = $picoEdu->filterSanitizeName(@$data['name']);

								$class_code = addslashes(trim(str_replace(' ', '', @$data['name'])));
								$grade_id = addslashes(trim(@$data['grade']));
								$school_program = addslashes(trim(@$data['school_program']));

								$token_class = md5($school_id . '-' . $name . '-' . time() . '-' . mt_rand(111111, 999999));

								$time_create = $time_edit = $database->getLocalDateTime();
								$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
								$admin_create = $admin_edit = $admin_id;
								if (empty($name)) {
									continue;
								}

								if (!isset($o[$school_program])) {
									$o[$school_program] = $kode_school_program;
									$kode_school_program++;
								}
								if (!isset($o[$name])) {
									$o[$grade_id] = $grade_id * 100 + $o[$school_program] * 10 + $row;
								} else {
									$o[$grade_id]++;
								}
								$sort_order = $o[$grade_id];
								if (!empty($name)) {
									$sql = "SELECT * FROM `edu_class` WHERE `class_code` = '$class_code' AND `school_id` = '$school_id' ";
									$stmt3 = $database->executeQuery($sql);
									if ($stmt3->rowCount() == 0) {

										$class_id = $database->generateNewId();
										$school_program_id = $picoEdu->getSchoolProgramId($school_program, $school_id);

										$sql = "INSERT INTO `edu_class` 
										(`class_id`, `token_class`, `school_id`, `class_code`, `grade_id`, `school_program_id`, `name`, 
										`time_create`, `time_edit`, `ip_create`, `ip_edit`, `admin_create`, `admin_edit`, `sort_order`, `default`, `active`) VALUES
										('$class_id', '$token_class', '$school_id', '$class_code', '$grade_id', '$school_program_id', '$name', 
										'$time_create', '$time_edit', '$ip_create', '$ip_edit', '$admin_create', '$admin_edit', '$sort_order', 0, 1)
										";
										$database->executeInsert($sql, true);
									}
								}
							}
						} catch (Exception $e) {
							// Do nothing
						}
						// import data class
						// selesai
						$sql = "UPDATE `edu_school_program` SET 
						`time_create` = '$time_create', `time_edit` = '$time_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', 
						`admin_create` = '$admin_create', `admin_edit` = '$admin_edit'
						WHERE `school_id` = '$school_id'; 
						";
						$database->executeUpdate($sql, true);

						// import data student
						// mulai


						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName('STUDENT');
							$highestRow = $objWorksheet->getHighestRow();
							$highestColumn = $objWorksheet->getHighestColumn();
							$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

							$fieldArray = array();
							$row = 1;

							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
							}

							for ($row = 2; $row <= $highestRow + 3; ++$row) {
								$data = array();
								for ($col = 0; $col < $highestColumnIndex; ++$col) {
									$data[$fieldArray[$col]] = $picoEdu->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
								}
								$reg_number = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number']);
								$reg_number_national = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number_national']);
								$name = $picoEdu->filterSanitizeName(@$data['name']);

								$class = addslashes(trim(@$data['class']));
								$address = addslashes(trim(@$data['address']));
								$phone = addslashes($picoEdu->fixPhone(@$data['phone']));
								$email = addslashes(trim(@$data['email']));
								$email = $picoEdu->filterEmailAddress($email);

								$gender = $picoEdu->mapGender(addslashes(trim(@$data['gender'])));
								$birth_place = addslashes(trim(@$data['birth_place']));
								$bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
								$birth_day = addslashes(excel2MySQLDate($bd));

								$token_student = md5($school_id . '-' . $reg_number . '-' . time() . '-' . mt_rand(111111, 999999));

								$time_create = $time_edit = $database->getLocalDateTime();
								$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
								$admin_create = $admin_edit = $admin_id;

								$password_initial = substr($token_student, 5, 6);
								$password = md5(md5($password_initial));


								$phone = $picoEdu->trimPunctuation($phone);
								$email = $picoEdu->trimPunctuation($email);
								$email = $picoEdu->filterEmailAddress($email);

								if (empty($name)) {
									continue;
								}
								if (empty($email)) {
									$email = $picoEdu->generateAltEmail('local', ($reg_number_national != '') ? 'st_' . $reg_number_national : '', ($reg_number != '') ? 'st_' . $reg_number : '', ($phone != '') ? 'ph_' . $country_id . '_' . $phone : '');
								}

								$user_data = array();
								$user_data['name'] = $name;
								$user_data['gender'] = $gender;
								$user_data['email'] = $email;
								$user_data['phone'] = $phone;
								$user_data['password'] = $password;
								$user_data['birth_day'] = $birth_day;
								$user_data['address'] = $address;
								$user_data['country_id'] = $country_id;
								$user_data['language'] = $language;
								if (!empty($name)) {
									if ($picoEdu->checkStudent($school_id, $reg_number, $reg_number_national, $name)) {
										continue;
									}
									$student_id = null;
									if($useNationalId && !empty($reg_number_national))
									{
										$student_id = trim($reg_number_national);
									}
									$chk = $picoEdu->getExistsingUser($user_data, $student_id);
									$student_id = addslashes($chk['member_id']);
									$username = addslashes($chk['username']);

									$db_fixed = 'NULL';
									if(!empty($birth_day))
									{
										$db_fixed = "'" . $birth_day . "'";
									}

									$class_id = $picoEdu->getClassId($class, $school_id);


									$sql = "INSERT INTO `edu_student` 
									(`student_id`, `username`, `token_student`, `school_id`, `reg_number`, `reg_number_national`, `class_id`, 
									`name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, 
									`address`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
									('$student_id', '$username', '$token_student', '$school_id', '$reg_number', '$reg_number_national', '$class_id', 
									'$name', '$gender', '$birth_place', $db_fixed, '$phone', '$email', '$password', '$password_initial', 
									'$address', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 0, 1)
									";
									$database->executeInsert($sql, true);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) VALUES
									('$student_id', '$school_id', 'S', '$class_id', '$time_create', true)
									";
									$database->executeInsert($sql2, true);

									$sql3 = "UPDATE `edu_student` SET `school_id` = '$school_id' WHERE `student_id` = '$student_id' 
									AND (`school_id` = '' OR `school_id` is null)
									";
									$database->executeUpdate($sql3, true);
								} else {
									break;
								}
							}

							$sql = "UPDATE `edu_student` 
							SET `edu_student`.`grade_id` = (SELECT `edu_class`.`grade_id` FROM `edu_class` 
							WHERE `edu_class`.`class_id` = `edu_student`.`class_id`),
							`prevent_change_school` = '1', `prevent_resign` = '1' 
							WHERE `edu_student`.`school_id` = '$school_id' ";
							$database->executeUpdate($sql, true);

							$sql1 = "UPDATE `edu_school` SET `prevent_change_school` = '1', `prevent_resign` = '1'
							WHERE `school_id` = '$school_id' 
							";
							$database->executeUpdate($sql1, true);
						} catch (Exception $e) {
							// Do nothing
						}
						// import data student
						// selesai


						// import data teacher
						// mulai
						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheetSource->setActiveSheetIndexByName('TEACHER');
							$highestRow = $objWorksheet->getHighestRow();
							$highestColumn = $objWorksheet->getHighestColumn();
							$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

							$fieldArray = array();
							$row = 1;
							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$fieldArray[$col] = strtolower($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
							}

							for ($row = 2; $row <= $highestRow; ++$row) {
								$data = array();
								for ($col = 0; $col < $highestColumnIndex; ++$col) {
									$data[$fieldArray[$col]] = $picoEdu->trimWhitespace($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
								}

								$reg_number = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number']);
								$reg_number_national = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number_national']);

								$name = $picoEdu->filterSanitizeName(@$data['name']);

								$address = addslashes(trim(@$data['address']));
								$phone = addslashes($picoEdu->fixPhone(trim(@$data['phone'])));
								$email = addslashes(trim(@$data['email']));
								$email = $picoEdu->filterEmailAddress($email);

								$gender = $picoEdu->mapGender(addslashes(trim(@$data['gender'])));
								$birth_place = addslashes(trim(@$data['birth_place']));
								$bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
								$birth_day = addslashes(excel2MySQLDate($bd));

								$token_teacher = md5($school_id . '-' . $reg_number . '-' . time() . '-' . mt_rand(111111, 999999));


								$password_initial = substr($token_teacher, 5, 6);
								$password = md5(md5($password_initial));


								$phone = $picoEdu->trimPunctuation($phone);
								$email = $picoEdu->trimPunctuation($email);

								if (empty($name)) {
									continue;
								}
								if (empty($email)) {
									$email = $picoEdu->generateAltEmail('local', ($reg_number_national != '') ? 'tc_' . $reg_number_national : '', ($reg_number != '') ? 'tc_' . $reg_number : '', ($phone != '') ? 'ph_' . $country_id . '_' . $phone : '');
								}

								$user_data = array();
								$user_data['name'] = $name;
								$user_data['gender'] = $gender;
								$user_data['email'] = $email;
								$user_data['phone'] = $phone;
								$user_data['password'] = $password;
								$user_data['birth_day'] = $birth_day;
								$user_data['address'] = $address;
								$user_data['country_id'] = $country_id;
								$user_data['language'] = $language;
								if (!empty($name) && !empty($email)) {
									$teacher_id = null;
									if($useNationalId && !empty($reg_number_national))
									{
										$teacher_id = trim($reg_number_national);
									}
									$chk = $picoEdu->getExistsingUser($user_data, $teacher_id);
									$teacher_id = addslashes($chk['member_id']);
									$username = addslashes($chk['username']);
									if ($picoEdu->checkTeacher($school_id, $reg_number, $reg_number_national, $name)) {
										continue;
									}

									$db_fixed = 'NULL';
									if(!empty($birth_day))
									{
										$db_fixed = "'" . $birth_day . "'";
									}

									$sql = "INSERT INTO `edu_teacher` 
									(`teacher_id`, `username`, `token_teacher`, `school_id`, `reg_number`, `reg_number_national`, `name`, 
									`gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, `address`, 
									`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
									('$teacher_id', '$username', '$token_teacher', '$school_id', '$reg_number', '$reg_number_national', '$name', 
									'$gender', '$birth_place', $db_fixed, '$phone', '$email', '$password', '$password_initial', '$address', 
									'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 1)
									";
									$database->executeInsert($sql, true);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
									('$teacher_id', '$school_id', 'T', '$time_create', true)
									";
									$database->executeInsert($sql2, true);

									$sql3 = "UPDATE `edu_teacher` 
									SET `school_id` = '$school_id' 
									WHERE `teacher_id` = '$teacher_id' 
									AND (`school_id` = '' OR `school_id` is null)
									";
									$database->executeUpdate($sql3, true);
								} else {
									break;
								}

							}
						} catch (Exception $e) {
							// Do nothing
						}
						$sql3 = "UPDATE `edu_school` SET
						`time_import_last` = '$time_edit',
						`admin_import_last` = '$admin_edit',
						`ip_import_last` = '$ip_edit'
						WHERE `school_id` = '$school_id'
						";
						$database->executeUpdate($sql3, true);

						// import data teacher
						// delete file
					}
				}
				
				
				$fileSync->deleteFile($path, true);
				header("Location: ".$picoEdu->gateBaseSelfName()."?option=success&school_id=$school_id");
			}
			else
			{
				$fileSync->deleteFile($path, true);
				// delete file
				header("Location: ".$picoEdu->gateBaseSelfName()."?option=duplicated");
			}
			$fileSync->deleteFile($path, true);
			// delete file
		}
	}
	exit();
}

$pageTitle = "Impor Data";

if(@$_GET['option'] == 'success')
{
if(isset($_GET['school_id']))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$edit_key = kh_filter_input(INPUT_GET, "school_id", FILTER_SANITIZE_STRING_NEW);
$nt = '';
$nt = '';

$sql = "SELECT `edu_school`.* $nt,
(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_school`.`admin_create` limit 0,1) AS `admin_create`,
(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_school`.`admin_edit` limit 0,1) AS `admin_edit`,
(SELECT `edu_admin3`.`name` FROM `edu_admin` AS `edu_admin3` WHERE `edu_admin3`.`admin_id` = `edu_school`.`admin_import_first` limit 0,1) AS `admin_import_first`,
(SELECT `edu_admin4`.`name` FROM `edu_admin` AS `edu_admin4` WHERE `edu_admin4`.`admin_id` = `edu_school`.`admin_import_last` limit 0,1) AS `admin_import_last`,
(SELECT COUNT(DISTINCT `edu_admin`.`admin_id`) FROM `edu_admin` WHERE `edu_admin`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_admin`.`school_id` limit 0,1) AS `num_admin`,
(SELECT COUNT(DISTINCT `edu_class`.`class_id`) FROM `edu_class` WHERE `edu_class`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_class`.`school_id` limit 0,1) AS `num_class`,
(SELECT COUNT(DISTINCT `edu_teacher`.`teacher_id`) FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_teacher`.`school_id` limit 0,1) AS `num_teacher`,
(SELECT COUNT(DISTINCT `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id` GROUP BY `edu_student`.`school_id` limit 0,1) AS `num_student`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<form name="formschool" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?> </td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><?php echo $data['school_code'];?> </td>
		</tr>
		<tr>
		<td>Jenjang Pendidikan</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?> </td>
		</tr>
		<tr>
		<td>Negeri Swasta</td>
		<td><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?> </td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?> </td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?> </td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?> </td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?> </td>
		</tr>
        <tr>
          <td>Jumlah Kelas</td>
          <td><?php echo $data['num_class'];?> </td>
        </tr>
        <tr>
          <td>Jumlah Siswa</td>
          <td><?php echo $data['num_student'];?> orang</td>
        </tr>
        <tr>
          <td>Jumlah Guru</td>
          <td><?php echo $data['num_teacher'];?> orang</td>
        </tr>
		<tr>
		  <td>Jumlah Admin</td>
		  <td><?php echo $data['num_admin'];?> orang</td>
      </tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><?php echo $picoEdu->trueFalse($data['prevent_change_school'], 'Ya', 'Tidak');?> </td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><?php echo $picoEdu->trueFalse($data['prevent_resign'], 'Ya', 'Tidak');?> </td>
		</tr>

		<tr>
		<td>Impor Pertama</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_import_first'])));?> </td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_first'];?> </td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_first'];?> </td>
		</tr>

		<tr>
		<td>Impor Terakhir</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_import_last'])));?> </td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_last'];?> </td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_last'];?> </td>
		</tr>


		<tr>
		<td>Waktu Buat</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?> </td>
		</tr>
		<tr>
		<td>Waktu Ubah</td>
		<td><?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_edit'])));?> </td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?> </td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?> </td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?> </td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?> </td>
		</tr>
	</table>
	<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td></td>
		<td><input type="button" name="update" id="update" value="Ubah Data" class="btn btn-primary" onclick="window.location='sekolah-profil.php?option=edit'" />		  
		<input type="button" name="import" id="import" value="Impor Data" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
exit();
}
}
else if(@$_GET['option'] == 'duplicated')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-warning">GAGAL! Data sekolah dengan name yang sama telah dimasukkan sebelumnya. Mohon periksa kembali data yang Anda masukkan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Impor lagi</a>.</div>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
	
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="alert alert-success">
Modul ini digunakan untuk mengimpor data sekolah, kelas, siswa, dan guru. Contoh data dapat didownload <a href="planetedu.xlsx">di sini</a>. Apabila terjadi kesalahan saat melakukan import data, segera hapus data tersebut sebelum mengimpor data yang lain.</div>
<p>Pilih file</p>
<style type="text/css">
.input-group > *:first-child{
	border-top-right-radius: 0 0;
	border-bottom-right-radius: 0 0;
}
.input-group > *:last-child{
	border-top-left-radius: 0 0;
	border-bottom-left-radius: 0 0;
}
.input-file-label.form-control[readonly]{
	background-color: #FFFFFF;
}
</style>
<form action="" method="post" enctype="multipart/form-data" name="form1">
	
  <input type="file" name="file" id="file" accept=".xlsx" style="position:absolute; left:-10000px; top:-10000px;">
  <div class="input-group mb-3" id="input-file-data">
  	<input type="button" class="btn btn-secondary input-file-button" value="Pilih File" />
    <input type="text" class="input-file-label form-control" readonly>
  </div>
  <input class="btn btn-success" type="submit" name="upload" id="upload" value="Upload File">
  <input class="btn btn-secondary" type="button" name="cancel" id="cancel" value="Batalkan" onclick="window.location='sekolah.php'">
</form>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', 'input[type="file"]', function(e){
		var files = $(this)[0].files;
		var fileName = files[0].name;
		$(this).closest('form').find('.input-file-label').val(fileName);
	});
    $(document).on('click', 'input.input-file-button', function(e){
		$(this).closest('form').find('input[type="file"]').click();
	});
});
</script>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>
