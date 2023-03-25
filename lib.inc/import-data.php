<?php
require_once __DIR__ . '/PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php';

/**
 * Import data preprocessor
 */

$school_id = "";
$country_id = 'ID';
$admin_id = $adminLoggedIn->admin_id;
$time_create = $time_edit = $database->getLocalDateTime();
$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
$admin_create = $admin_edit = $admin_id;

$myschool = true;
$ip = str_replace(array(':', '.'), '-', $_SERVER['REMOTE_ADDR']);

if(!file_exists(dirname(__DIR__)."/tmp"))
{
    mkdir(dirname(__DIR__) . "/tmp", 755);
}

$path = dirname(__DIR__)."/tmp/$ip-".mt_rand(1000000, 9000000).".xlsx";
$success = move_uploaded_file($_FILES['file']['tmp_name'], $path);
$fileSync->createFile($path, false);
$errors = 0;
if($success && file_exists($path))
{
    $callStartTime = microtime(true);
    
    $name_school = '';
    
    // import data school
    // mulai
    $objWorksheetSource = PHPExcel_IOFactory::load($path);

    $importExcel = new \Pico\ImportExcel();

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
            '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 1) ";

            $stmt = $database->executeInsert($sql, true);
            if($stmt->rowCount() == 0)
            {
                $myschool = false;
            }
            else
            {
                $myschool = true;
            }
            
        }
    }
    catch(\Exception $e)
    {
        // Do nothing
        $myschool = false;
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
        WHERE `edu_school`.`school_id` = '$school_id' AND `edu_member_school`.`member_id` = '$admin_id' ";
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
                        $birth_day = excel2MySQLDate($bd);
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
                        if (!empty($name)) {
                            $chk = $picoEdu->getExistsingUser($user_data);
                            $admin_id = addslashes($chk['member_id']);
                            $username = addslashes($chk['username']);
                            $passwordHash = md5(md5($password));

                            $db_fixed = 'NULL';
                            if(!empty($birth_day))
                            {
                                $db_fixed = "'" . $birth_day . "'";
                            }
                            $email = empty($email) || $email == "''" ? 'null' : "'$email'";
        
                            $sql = "INSERT INTO `edu_admin` 
                            (`admin_id`, `school_id`, `admin_level`, `username`, `name`, `token_admin`, `email`, `phone`, `password`, 
                            `password_initial`, `gender`, `birth_day`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, 
                            `ip_create`, `ip_edit`, `blocked`, `active`) VALUES 
                            ('$admin_id', '$school_id', '2', '$username', '$name', '$token_admin', $email, '$phone', '$passwordHash', 
                            '$password', '$gender', $db_fixed, '$time_create', '$time_edit', '$admin_create', '$admin_edit', 
                            '$ip_create', '$ip_edit', '0', '1') ";

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
                    echo $e->getMessage();
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
                                '$time_create', '$time_edit', '$ip_create', '$ip_edit', '$admin_create', '$admin_edit', '$sort_order', 0, 1) ";
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
                WHERE `school_id` = '$school_id' ";
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
                        $birth_day = excel2MySQLDate($bd);

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
                
                            $email = empty($email) || $email == "''" ? 'null' : "'$email'";
                
                            $sql = "INSERT INTO `edu_student` 
                            (`student_id`, `username`, `token_student`, `school_id`, `reg_number`, `reg_number_national`, `class_id`, 
                            `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, 
                            `address`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
                            ('$student_id', '$username', '$token_student', '$school_id', '$reg_number', '$reg_number_national', '$class_id', 
                            '$name', '$gender', '$birth_place', $db_fixed, '$phone', $email, '$password', '$password_initial', 
                            '$address', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 0, 1) ";
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
                    WHERE `school_id` = '$school_id' ";
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
                        $birth_day = excel2MySQLDate($bd);

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

                            $email = empty($email) || $email == "''" ? 'null' : "'$email'";

                            $sql = "INSERT INTO `edu_teacher` 
                            (`teacher_id`, `username`, `token_teacher`, `school_id`, `reg_number`, `reg_number_national`, `name`, 
                            `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, `address`, 
                            `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
                            ('$teacher_id', '$username', '$token_teacher', '$school_id', '$reg_number', '$reg_number_national', '$name', 
                            '$gender', '$birth_place', $db_fixed, '$phone', $email, '$password', '$password_initial', '$address', 
                            '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 1) ";
                            $database->executeInsert($sql, true);

                            $sql2 = "INSERT INTO `edu_member_school` 
                            (`member_id`, `school_id`, `role`, `time_create`, `active`) VALUES
                            ('$teacher_id', '$school_id', 'T', '$time_create', true) ";
                            $database->executeInsert($sql2, true);

                            $sql3 = "UPDATE `edu_teacher` 
                            SET `school_id` = '$school_id' 
                            WHERE `teacher_id` = '$teacher_id' 
                            AND (`school_id` = '' OR `school_id` is null) ";
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
                WHERE `school_id` = '$school_id' ";
                $database->executeUpdate($sql3, true);

                // import data teacher
                // delete file
            }
        }
        
        
        $fileSync->deleteFile($path, false);
        header("Location: ".$picoEdu->gateBaseSelfName()."?option=success&school_id=$school_id");
    }
    else
    {
        $fileSync->deleteFile($path, false);
        // delete file
        header("Location: ".$picoEdu->gateBaseSelfName()."?option=duplicated");
    }
    $fileSync->deleteFile($path, false);

    $database->setSystemVariable('import-'.$school_id, 'true');
    // delete file
}
