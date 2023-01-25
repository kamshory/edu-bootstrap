<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(!@$admin_id)
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}
if(isset($_POST['upload']) && isset($_FILES['file']['name']))
{ 
	$country_id = 'ID';
	$admin_id = $admin_login->admin_id;
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	$admin_create = $admin_edit = $admin_id;
	/** Include PHPExcel_IOFactory */
	require_once dirname(dirname(__FILE__)) . '/lib.inc/PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php';
	
	if(isset($_FILES['file']['tmp_name']))
	{
		$school_id = '';

		$myschool = true;
		$ip = str_replace(array(':','.'), '-', $_SERVER['REMOTE_ADDR']);

		if(!file_exists(dirname(__FILE__)."/tmp"))
		{
			mkdir(dirname(__FILE__) . "/tmp", 755);
		}

		$path = dirname(__FILE__)."/tmp/$ip-".mt_rand(1000000, 9000000).".xlsx";
		$success = move_uploaded_file($_FILES['file']['tmp_name'], $path);
		$errors = 0;
		if($success && file_exists($path))
		{
			$callStartTime = microtime(true);
			
			
			$name_school = '';
			
			// import data school
			// mulai
			$objWorksheet = PHPExcel_IOFactory::load($path);
			try{
				$objWorksheet = $objWorksheet->setActiveSheetIndexByName('SCHOOL');
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
						$data[$fieldArray[$col]] = trim($objWorksheet->getCellByColumnAndRow($col, $row)->getValue(), " \r\n\t ");
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

					$principal = addslashes(@$data['principal']);
					$public_private = addslashes(@$data['public_private']);
					$school_grade = addslashes(@$data['school_grade']);
					$country_id = strtoupper(addslashes(@$data['country']));
					$language = strtolower(addslashes(@$data['language']));
					
					$token_school = md5($name.'-'.time().'-'.mt_rand(111111, 999999));
					
					$name_school = $name;
					if($name == '')
					{
						continue;
					}

					$school_id = $database->generateNewId();
					
					$sql = "INSERT INTO `edu_school` 
					(`school_id`, `school_code`, `token_school`, `name`, `school_grade_id`, `public_private`, `principal`, `address`, `phone`, 
					`email`, `language`, `country_id`, `time_import_first`, `admin_import_first`, `ip_import_first`, 
					`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
					('$school_id', '$school_code', '$token_school', '$name', '$school_grade', '$public_private', '$principal', '$address', '$phone', 
					'$email', '$language', '$country_id', '$time_create', '$admin_create', '$ip_create', 
					'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 1);
					";

					$stmt = $database->executeInsert($sql);
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
				$sql = "SELECT `school_id` from `edu_school` where `name` like '$name_school' ";
				$stmt = $database->executeQuery($sql);
				$data_school = $stmt->fetch(PDO::FETCH_ASSOC);
				$school_id = $data_school['school_id'];
			}
			
			if(!$myschool)
			{
				$sql = "SELECT `edu_school`.*
				from `edu_school`
				left join(`edu_member_school`) on(`edu_member_school`.`school_id` = `edu_school`.`school_id` and `edu_member_school`.`role` = 'A')
				where `edu_school`.`school_id` = '$school_id' and `edu_member_school`.`member_id` = '$admin_id' 
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
				$sql = "select * from `member` where `member_id` = '$admin_id' ";
				$stmt2 = $database->executeQuery($sql);



				if ($stmt2->rowCount() == 0) {
					$sqlInsert = $picoEdu->generateCreateMmeberFromAdmin($admin_id);
					$stmt2 = $database->executeInsert($sqlInsert);
					if ($stmt2->rowCount() > 0) {
						$stmt2 = $database->executeQuery($sql);
					}
				}

				if ($stmt2->rowCount() > 0) {
					$data2 = $stmt2->fetch(PDO::FETCH_ASSOC);
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

					if ($name != '') {

						if ($email != '' && $name != '') {


							/*
							Commented
							$db_fixed = 'NULL';
							if(!empty($birth_day))
							{
								$db_fixed = "'" . $birth_day . "'";
							}

							$sql = "INSERT INTO `edu_admin` 
							(`admin_id`, `school_id`, `username`, `name`, `token_admin`, `email`, `phone`, `password`, `gender`, `birth_day`, 
							`time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
							('$admin_id', '$school_id', '$username', '$name', '$token_admin', '$email', '$phone', '$password', '$gender', $db_fixed, 
							'$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', '0', '1');
							";
							try {
								$database->executeInsert($sql);
							} catch (PDOException $e) {
								// Do nothing
							}
							*/

							$sql2 = "UPDATE `edu_admin` SET
							`school_id` = '$school_id'
							WHERE `admin_id` = '$admin_id'
							";
							try {
								$database->executeUpdate($sql2);
							} catch (PDOException $e) {
								// Do nothing
							}

							$sql2 = "INSERT INTO `edu_member_school` 
							(`member_id`, `school_id`, `role`, `time_create`, `active`) values
							('$admin_id', '$school_id', 'A', '$time_create', '1')
							";
							try {
								$database->executeInsert($sql2);
							} catch (PDOException $e) {
								// Do nothing
							}
						}


						// import data school
						// selesai


						// import data admin school
						// mulai
						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheet->setActiveSheetIndexByName('ADMIN');
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
									$data[$fieldArray[$col]] = trim($objWorksheet->getCellByColumnAndRow($col, $row)->getValue(), " \r\n\t ");
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

								if ($name == '') {
									continue;
								}

								$time_create = $time_edit = $picoEdu->getLocalDateTime();
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
								if ($name != '' && $email != '') {
									$chk = $picoEdu->getExistsingUser($user_data);
									$admin_id = $chk['member_id'];
									$username = $chk['username'];

									$db_fixed = 'NULL';
									if(!empty($birth_day))
									{
										$db_fixed = "'" . $birth_day . "'";
									}

									$sql = "INSERT INTO `edu_admin` 
									(`admin_id`, `school_id`, `admin_level`, `username`, `name`, `token_admin`, `email`, `phone`, `password`, 
									`password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
									`ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
									('$admin_id', '$school_id', '2', '$username', '$name', '$token_admin', '$email', '$phone', md5(md5('$password')), 
									'$password', '$gender', $db_fixed, '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
									'$ip_create', '$ip_edit', '0', '1');
									";

									$database->executeInsert($sql);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `time_create`, `active`) values
									('$admin_id', '$school_id', 'A', '$time_create', '1')
									";
									$database->executeInsert($sql2);
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
							$objWorksheet = $objWorksheet->setActiveSheetIndexByName('CLASS');
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
									$data[$fieldArray[$col]] = trim($objWorksheet->getCellByColumnAndRow($col, $row)->getValue(), " \r\n\t ");
								}

								$name = $picoEdu->filterSanitizeName(@$data['name']);

								$class_code = addslashes(trim(str_replace(' ', '', @$data['name'])));
								$grade_id = addslashes(trim(@$data['grade']));
								$school_program = addslashes(trim(@$data['school_program']));

								$token_class = md5($school_id . '-' . $name . '-' . time() . '-' . mt_rand(111111, 999999));

								$time_create = $time_edit = $picoEdu->getLocalDateTime();
								$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
								$admin_create = $admin_edit = $admin_id;
								if ($name == '') {
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
								$order = $o[$grade_id];
								if ($name != '') {
									$sql = "select * from `edu_class` where `class_code` = '$class_code' and `school_id` = '$school_id' ";
									$stmt3 = $database->executeQuery($sql);
									if ($stmt3->rowCount() == 0) {

										$class_id = $database->generateNewId();
										$school_program_id = $picoEdu->getSchoolProgramId($school_program, $school_id);

										$sql = "INSERT INTO `edu_class` 
										(`class_id`, `token_class`, `school_id`, `class_code`, `grade_id`, `school_program_id`, `name`, 
										`time_create`, `time_edit`, `ip_create`, `ip_edit`, `admin_create`, `admin_edit`, `order`, `default`, `active`) VALUES
										('$class_id', '$token_class', '$school_id', '$class_code', '$grade_id', '$school_program_id', '$name', 
										'$time_create', '$time_edit', '$ip_create', '$ip_edit', '$admin_create', '$admin_edit', '$order', 0, 1)
										";
										$database->executeInsert($sql);
									}
								}
							}
						} catch (Exception $e) {
							// Do nothing
						}
						// import data class
						// selesai
						$sql = "update `edu_school_program` set 
						`time_create` = '$time_create', `time_edit` = '$time_edit', `ip_create` = '$ip_create', `ip_edit` = '$ip_edit', 
						`admin_create` = '$admin_create', `admin_edit` = '$admin_edit'
						where `school_id` = '$school_id'; 
						";
						$database->executeUpdate($sql);

						// import data student
						// mulai


						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheet->setActiveSheetIndexByName('STUDENT');
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
									$data[$fieldArray[$col]] = trim($objWorksheet->getCellByColumnAndRow($col, $row)->getValue(), " \r\n\t ");
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

								$time_create = $time_edit = $picoEdu->getLocalDateTime();
								$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
								$admin_create = $admin_edit = $admin_id;

								$password_initial = substr($token_student, 5, 6);
								$password = md5(md5($password_initial));


								$phone = trim($phone, " ._-/\\ ");
								$email = trim($email, " ._-/\\ ");
								$email = $picoEdu->filterEmailAddress($email);

								if ($name == '') {
									continue;
								}
								if ($email == '') {
									$email = $picoEdu->generateAltEmail('planetbiru.com', ($reg_number_national != '') ? 'st_' . $reg_number_national . '_' . $school_id : '', ($reg_number != '') ? 'st_' . $reg_number . '_' . $school_id : '', ($phone != '') ? 'ph_' . $country_id . '_' . $phone : '');
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
								if ($name != '') {
									if ($picoEdu->checkStudent($school_id, $reg_number, $reg_number_national, $name)) {
										continue;
									}
									$chk = $picoEdu->getExistsingUser($user_data);
									$student_id = $chk['member_id'];
									$username = $chk['username'];

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


									$database->executeInsert($sql);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) values
									('$student_id', '$school_id', 'S', '$class_id', '$time_create', '1')
									";

									$database->executeInsert($sql2);

									$sql3 = "update `edu_student` set `school_id` = '$school_id' where `student_id` = '$student_id' 
									and (`school_id` = '0' or `school_id` is null)
									";


									$database->executeUpdate($sql3);
								} else {
									break;
								}
							}

							$sql = "update `edu_student` 
							set `edu_student`.`grade_id` = (select `edu_class`.`grade_id` from `edu_class` 
							where `edu_class`.`class_id` = `edu_student`.`class_id`),
							`prevent_change_school` = '1', `prevent_resign` = '1' 
							where `edu_student`.`school_id` = '$school_id' ";
							$database->executeUpdate($sql);


							$sql1 = "update `edu_school` set `prevent_change_school` = '1', `prevent_resign` = '1'
							where `school_id` = '$school_id' 
							";

							
							$database->executeUpdate($sql1);
						} catch (Exception $e) {
							// Do nothing
						}
						// import data student
						// selesai


						// import data teacher
						// mulai
						$objWorksheet = PHPExcel_IOFactory::load($path);
						try {
							$objWorksheet = $objWorksheet->setActiveSheetIndexByName('TEACHER');
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
									$data[$fieldArray[$col]] = trim($objWorksheet->getCellByColumnAndRow($col, $row)->getValue(), " \r\n\t ");
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


								$phone = trim($phone, " ._-/\\ ");
								$email = trim($email, " ._-/\\ ");

								if ($name == '') {
									continue;
								}
								if ($email == '') {
									$email = $picoEdu->generateAltEmail('planetbiru.com', ($reg_number_national != '') ? 'tc_' . $reg_number_national . '_' . $school_id : '', ($reg_number != '') ? 'tc_' . $reg_number . '_' . $school_id : '', ($phone != '') ? 'ph_' . $country_id . '_' . $phone : '');
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
								if ($name != '' && $email != '') {
									$chk = $picoEdu->getExistsingUser($user_data);
									$teacher_id = $chk['member_id'];
									$username = $chk['username'];
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
									$database->executeInsert($sql);

									$sql2 = "INSERT INTO `edu_member_school` 
									(`member_id`, `school_id`, `role`, `time_create`, `active`) values
									('$teacher_id', '$school_id', 'T', '$time_create', '1')
									";
									$database->executeInsert($sql2);

									$sql3 = "update `edu_teacher` 
									set `school_id` = '$school_id' 
									where `teacher_id` = '$teacher_id' 
									and (`school_id` = '0' or `school_id` is null)
									";
									$database->executeUpdate($sql3);
								} else {
									break;
								}

							}
						} catch (Exception $e) {
							// Do nothing
						}
						$sql3 = "update `edu_school` set
						`time_import_last` = '$time_edit',
						`admin_import_last` = '$admin_edit',
						`ip_import_last` = '$ip_edit'
						where `school_id` = '$school_id'
						";
						$database->executeUpdate($sql3);

						// import data teacher
						// delete file
					}
				}
				
				
				@unlink($path);
				header("Location: ".basename($_SERVER['PHP_SELF'])."?option=success&school_id=$school_id");
			}
			else
			{
				@unlink($path);
				// delete file
				header("Location: ".basename($_SERVER['PHP_SELF'])."?option=duplicated");
			}
			@unlink($path);
			// delete file
		}
	}
	exit();
}


$cfg->module_title = "Impor Data";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(@$_GET['option'] == 'success')
{
if(isset($_GET['school_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$nt = '';

$sql = "SELECT `edu_school`.* $nt,
(select `edu_admin1`.`name` from `edu_admin` as `edu_admin1` where `edu_admin1`.`admin_id` = `edu_school`.`admin_create` limit 0,1) as `admin_create`,
(select `edu_admin2`.`name` from `edu_admin` as `edu_admin2` where `edu_admin2`.`admin_id` = `edu_school`.`admin_edit` limit 0,1) as `admin_edit`,
(select `edu_admin3`.`name` from `edu_admin` as `edu_admin3` where `edu_admin3`.`admin_id` = `edu_school`.`admin_import_first` limit 0,1) as `admin_import_first`,
(select `edu_admin4`.`name` from `edu_admin` as `edu_admin4` where `edu_admin4`.`admin_id` = `edu_school`.`admin_import_last` limit 0,1) as `admin_import_last`,
(select count(distinct `edu_admin`.`admin_id`) from `edu_admin` where `edu_admin`.`school_id` = `edu_school`.`school_id` group by `edu_admin`.`school_id` limit 0,1) as `num_admin`,
(select count(distinct `edu_class`.`class_id`) from `edu_class` where `edu_class`.`school_id` = `edu_school`.`school_id` group by `edu_class`.`school_id` limit 0,1) as `num_class`,
(select count(distinct `edu_teacher`.`teacher_id`) from `edu_teacher` where `edu_teacher`.`school_id` = `edu_school`.`school_id` group by `edu_teacher`.`school_id` limit 0,1) as `num_teacher`,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id` group by `edu_student`.`school_id` limit 0,1) as `num_student`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formschool" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Kode Sekolah</td>
		<td><?php echo $data['school_code'];?></td>
		</tr>
		<tr>
		<td>Jenjang Pendidikan</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></td>
		</tr>
		<tr>
		<td>Negeri Swasta</td>
		<td><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
        <tr>
          <td>Jumlah Kelas</td>
          <td><?php echo ($data['num_class']);?></td>
        </tr>
        <tr>
          <td>Jumlah Siswa</td>
          <td><?php echo ($data['num_student']);?> orang</td>
        </tr>
        <tr>
          <td>Jumlah Guru</td>
          <td><?php echo ($data['num_teacher']);?> orang</td>
        </tr>
		<tr>
		  <td>Jumlah Admin</td>
		  <td><?php echo ($data['num_admin']);?> orang</td>
      </tr>
		<tr>
		<td>Cegah Siswa Pindah</td>
		<td><?php echo ($data['prevent_change_school'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td>Cegah Siswa Keluar</td>
		<td><?php echo ($data['prevent_resign'])?'Ya':'Tidak';?></td>
		</tr>

		<tr>
		<td>Impor Pertama</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_import_first'])));?></td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_first'];?></td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_first'];?></td>
		</tr>

		<tr>
		<td>Impor Terakhir</td>
		<td></td>
		</tr>
		<tr>
		<td>Waktu</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_import_last'])));?></td>
		</tr>
		<tr>
		<td>Admin</td>
		<td><?php echo $data['admin_import_last'];?></td>
		</tr>
		<tr>
		<td>IP</td>
		<td><?php echo $data['ip_import_last'];?></td>
		</tr>


		<tr>
		<td>Waktu Buat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Waktu Ubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>IP Buat</td>
		<td><?php echo $data['ip_create'];?></td>
		</tr>
		<tr>
		<td>IP Ubah</td>
		<td><?php echo $data['ip_edit'];?></td>
		</tr>
		<tr>
		<td></td>
		<td><input type="button" name="update" id="update" value="Ubah Data" class="com-button" onclick="window.location='sekolah-profil.php?option=edit'" />		  <input type="button" name="import" id="import" value="Impor Data" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
exit();
}
}
else if(@$_GET['option'] == 'duplicated')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<div class="warning">GAGAL! Data sekolah dengan name yang sama telah dimasukkan sebelumnya. Mohon periksa kembali data yang Anda masukkan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Impor lagi</a>.</div>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
	
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<div class="info">
Modul ini digunakan untuk mengimpor data sekolah, kelas, siswa, dan guru. Contoh data dapat didownload <a href="planetedu.xlsx">di sini</a>. Apabila terjadi kesalahan saat melakukan import data, segera hapus data tersebut sebelum mengimpor data yang lain.</div>
<p>Pilih file</p>
<style type="text/css">
.input-file{
	display:inline-block;
	position:relative;
	border:1px solid #DDDDDD;
	background-color:#FFFFFF;
	width:200px;
	max-width:100%;
	overflow:hidden;
	vertical-align:bottom;

}
input[type="button"].input-file-button{
	width:50px;
	border-width:0 1px 0 0;
}
.input-file-button{
}
.input-file-label{
	display: inline-block;
    width: calc(100% - 56px);
    box-sizing: border-box;
    padding-top: 1px;
    padding-left: 2px;
    padding-right: 2px;
    padding-bottom: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: bottom;
    line-height: 1.5;
}
</style>
<form action="" method="post" enctype="multipart/form-data" name="form1">
  <input type="file" name="file" id="file" accept=".xlsx" style="position:absolute; left:-10000px; top:-10000px;">
  <span class="input-file" id="input-file-data">
  	<input type="button" class="input-file-button" value="Pilih" />
    <span class="input-file-label"></span>
  </span>
  <input type="submit" name="upload" id="upload" value="Upload File">
  <input type="button" name="cancel" id="cancel" value="Batalkan" onclick="window.location='sekolah.php'">
</form>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', 'input[type="file"]', function(e){
		var files = $(this)[0].files;
		var fileName = files[0].name;
		$(this).closest('form').find('.input-file .input-file-label').text(fileName);
	});
    $(document).on('click', '.input-file input.input-file-button', function(e){
		$(this).closest('form').find('input[type="file"]').click();
	});
});
</script>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>
