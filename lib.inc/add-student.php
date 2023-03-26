<?php
require_once __DIR__ . '/PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php';

$school_id = $adminLoggedIn->school_id;
$country_id = 'ID';
$admin_id = $adminLoggedIn->admin_id;
$time_create = $time_edit = $database->getLocalDateTime();
$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
$admin_create = $admin_edit = $admin_id;

$myschool = true;
$ip = str_replace(array(':', '.'), '-', $_SERVER['REMOTE_ADDR']);

if (!file_exists(dirname(__DIR__) . "/tmp")) {
    mkdir(dirname(__DIR__) . "/tmp", 755);
}

$path = dirname(__DIR__)."/tmp/$ip-".mt_rand(1000000, 9000000).".xlsx";
$success = move_uploaded_file($_FILES['file']['tmp_name'], $path);
$errors = 0;
$language = 'id';
$useNationalId = $use_national_id;

if ($success && file_exists($path)) {

    $callStartTime = microtime(true);
    $name_school = '';

    // import data school
    // mulai

    $objWorksheetSource = PHPExcel_IOFactory::load($path);

    $importExcel = new \Pico\ImportExcel();


    $sqlc = "SELECT `edu_class`.`class_id`, `edu_class`.`name`, `edu_class`.`school_program_id`, `edu_class`.`grade_id` 
    FROM `edu_class` 
    LEFT JOIN (`edu_school_program`) ON (`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`)
    WHERE `edu_class`.`active` = true AND `edu_class`.`school_id` = '$school_id' AND `edu_class`.`name` != '' 
    ORDER BY `edu_class`.`grade_id` ASC, `edu_school_program`.`sort_order` ASC, `edu_class`.`sort_order` ASC 
    ";
    $arrc = $database->fetchAssocAll($sqlc, array());
    foreach ($arrc as $class) {
        $sheetName = $class['name'];
        $class_id = $class['class_id'];
        $grade_id = $class['grade_id'];
        try {
            $objWorksheet = $objWorksheetSource->setActiveSheetIndexByName($sheetName);
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

                $reg_number = $picoEdu->filterSanitizeDoubleSpace(@$data['nis']);
                $reg_number_national = $picoEdu->filterSanitizeDoubleSpace(@$data['nisn']);

                if ($reg_number != '<NIS>') {
                    $name = $picoEdu->filterSanitizeName(@$data['nama']);
                    
                    if (empty($name)) {
                        continue;
                    }
                    $address = addslashes(trim(@$data['alamat']));
                    $phone = addslashes($picoEdu->fixPhone(@$data['telepon']));
                    $email = addslashes(trim(@$data['email']));
                    $email = $picoEdu->filterEmailAddress($email);
                    $gender = addslashes(trim(@$data['jenis_kelamin']));

                    if($gender == 'L')
                    {
                        $gender = 'M';
                    }
                    else if($gender == 'P')
                    {
                        $gender = 'W';
                    }

                    $birth_place = addslashes(trim(@$data['tempat_lahir']));
                    $bd = isset($data['tanggal_lahir']) ? ((int) $data['tanggal_lahir']) : 0;
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
                        if ($useNationalId && !empty($reg_number_national)) {
                            $student_id = trim($reg_number_national);
                        }

                        $chk = $picoEdu->getExistsingUser($user_data, $student_id);
                        $student_id = addslashes($chk['member_id']);
                        $username = addslashes($chk['username']);

                        $db_fixed = 'NULL';
                        if (!empty($birth_day)) {
                            $db_fixed = "'" . $birth_day . "'";
                        }

                        $email = empty($email) || $email == "''" ? 'null' : "'$email'";

                        $sql = "INSERT INTO `edu_student` 
                        (`student_id`, `username`, `token_student`, `school_id`, `reg_number`, `reg_number_national`, `class_id`, `grade_id`,
                        `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, 
                        `address`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
                        ('$student_id', '$username', '$token_student', '$school_id', '$reg_number', '$reg_number_national', '$class_id', '$grade_id',
                        '$name', '$gender', '$birth_place', $db_fixed, '$phone', $email, '$password', '$password_initial', 
                        '$address', '$time_create', '$time_edit', '$admin_create', '$admin_edit', '$ip_create', '$ip_edit', 0, 1) ";
                        $database->executeInsert($sql, true);

                        $sql2 = "INSERT INTO `edu_member_school` 
                        (`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) VALUES
                        ('$student_id', '$school_id', 'S', '$class_id', '$time_create', true)
                        ";
                        $database->executeInsert($sql2, true);
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }
    }
    $fileSync->deleteFile($path, false);
    header("Location: siswa.php");
}
