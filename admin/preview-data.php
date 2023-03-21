<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if(empty($admin_id))
{
	require_once __DIR__."/login-form.php";
	exit();
}
$path = __DIR__ . "/planetedu.xlsx";
require_once dirname(__DIR__) . '/lib.inc/PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php';
	

function generateTable($header, $body, $user_data)
{
    $headerKeys = array_keys($user_data[0]);
    $table = '';
    $table .= '<table border="1" style="border-collapse:collapse">';
    $table .= '<thead>';
    $table .= '<tr>';
    $table .= '<td>'.implode('</td><td>', $headerKeys).'</td>';
    $table .= '</tr>';
    $table .= '</thead>';

    $table .= '<tbody>';
    foreach($user_data as $val)
    {
        $table .= '<tr>';
        $table .= '<td>'.implode('</td><td>', $val).'</td>';
        $table .= '</tr>';
    }
    $table .= '</tbody>';

    $table .= '</table>';
    $table = \Pico\PicoDOM::tidyHTML($table);
    return $table;
}

// import data school
// mulai
$objWorksheet = PHPExcel_IOFactory::load($path);
try{
    $objWorksheet = $objWorksheet->setActiveSheetIndexByName('SCHOOL');
    $highestRow = $objWorksheet->getHighestRow(); 
    $highestColumn = $objWorksheet->getHighestColumn(); 
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
    
    $fieldArray = array();
    $rawHeader = array();
    $rawData = array();
    $row = 1;
    for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $rawHeader[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        $fieldArray[$col] = strtolower($rawHeader[$col]);
    }

    $fixedData = array();
    for($row = 2; $row <= $highestRow; ++$row) 
    {
        $data = array();
        $values = array();
        for ($col = 0; $col < $highestColumnIndex; ++$col) 
        {
            $cellValue = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            $values[$col] = $cellValue;
            $data[$fieldArray[$col]] = $picoEdu->trimWhitespace($cellValue);
        }
        $rawData[] = $values;

        $name = $picoEdu->filterSanitizeName(@$data['name'], true);
        
        $school_code = strtolower($name);
        $school_code = preg_replace("/[^a-z\-\d]/i","-",$school_code);
        $school_code = str_replace("---", "-", $school_code);
        $school_code = str_replace("--", "-", $school_code);
        $school_code = str_replace("--", "-", $school_code);
        
        
        $address = @$data['address'];
        $phone = $picoEdu->fixPhone(@$data['phone']);
        $email = @$data['email'];

        $principal = @$data['principal'];
        $public_private = @$data['public_private'];
        $school_grade = @$data['school_grade'];
        $country_id = strtoupper(@$data['country']);
        $principal = @$data['principal'];
        $language = strtolower(@$data['language']);
        $school_grade = strtolower(@$data['school_grade']);
        $use_national_id = strtolower(@$data['use_national_id']);

        $user_data = array();
        $user_data['name'] = $name;
        $user_data['email'] = $email;
        $user_data['phone'] = $phone;
        $user_data['public_private'] = $public_private;
        $user_data['address'] = $address;
        $user_data['country_id'] = $country_id;
        $user_data['language'] = $language;
        $user_data['principal'] = $principal;
        $user_data['school_grade'] = $school_grade;
        $user_data['use_national_id'] = $use_national_id;

        $fixedData[] = $user_data;
    }
    echo generateTable($rawHeader, $rawData, $fixedData);
}
catch(Exception $e)
{
    // Do nothing
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
    $rawHeader = array();
    $rawData = array();
    $row = 1;
    for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $rawHeader[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        $fieldArray[$col] = strtolower($rawHeader[$col]);
    }

    $fixedData = array();

    for($row = 2; $row <= $highestRow; ++$row) 
    {
        $data = array();
        $values = array();
        for ($col = 0; $col < $highestColumnIndex; ++$col) 
        {
            $cellValue = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            $values[$col] = $cellValue;
            $data[$fieldArray[$col]] = $picoEdu->trimWhitespace($cellValue);
        }
        $rawData[] = $values;

        $name = $picoEdu->filterSanitizeName(@$data['name']);

        $gender = $picoEdu->mapGender((trim(@$data['gender'])));
        $bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
        $birth_day = substr(excel2MySQLDate($bd), 0, 10);
        $phone = ($picoEdu->fixPhone(trim(@$data['phone'])));
        $email = (trim(@$data['email']));
        $email = $picoEdu->filterEmailAddress($email);
        $password = (trim(@$data['password']));
        $address = (trim(@$data['address']));

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
        
        $fixedData[] = $user_data;
    }
    echo generateTable($rawHeader, $rawData, $fixedData);
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
    $rawHeader = array();
    $rawData = array();
    $row = 1;
    for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $rawHeader[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        $fieldArray[$col] = strtolower($rawHeader[$col]);
    }

    $fixedData = array();

    for($row = 2; $row <= $highestRow; ++$row) 
    {
        $data = array();
        $values = array();
        for ($col = 0; $col < $highestColumnIndex; ++$col) 
        {
            $cellValue = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            $values[$col] = $cellValue;
            $data[$fieldArray[$col]] = $picoEdu->trimWhitespace($cellValue);
        }
        $rawData[] = $values;

        $name = $picoEdu->filterSanitizeName(@$data['name']);

        $class_code = (trim(str_replace(' ', '', @$data['name'])));
        $grade_id = (trim(@$data['grade']));
        $school_program = (trim(@$data['school_program']));

        $user_data = array();
        $user_data['name'] = $name;
        $user_data['grade_id'] = $grade_id;
        $user_data['school_program'] = $school_program;

        $fixedData[] = $user_data;
    }

    echo generateTable($rawHeader, $rawData, $fixedData);
} catch (Exception $e) {
    // Do nothing
}
// import data class
// selesai
           
// import data student
// mulai


$objWorksheet = PHPExcel_IOFactory::load($path);
try {
    $objWorksheet = $objWorksheet->setActiveSheetIndexByName('STUDENT');
    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    $fieldArray = array();
    $rawHeader = array();
    $rawData = array();
    $row = 1;
    for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $rawHeader[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        $fieldArray[$col] = strtolower($rawHeader[$col]);
    }

    $fixedData = array();

    for($row = 2; $row <= $highestRow; ++$row) 
    {
        $data = array();
        $values = array();
        for ($col = 0; $col < $highestColumnIndex; ++$col) 
        {
            $cellValue = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            $values[$col] = $cellValue;
            $data[$fieldArray[$col]] = $picoEdu->trimWhitespace($cellValue);
        }
        $rawData[] = $values;

        $reg_number = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number']);
        $reg_number_national = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number_national']);
        $name = $picoEdu->filterSanitizeName(@$data['name']);

        $class = (trim(@$data['class']));
        $address = (trim(@$data['address']));
        $phone = ($picoEdu->fixPhone(@$data['phone']));
        $email = (trim(@$data['email']));
        $email = $picoEdu->filterEmailAddress($email);

        $gender = $picoEdu->mapGender((trim(@$data['gender'])));
        $birth_place = (trim(@$data['birth_place']));
        $bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
        $birth_day = substr(excel2MySQLDate($bd), 0, 10);

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
        $reg_number = @$data['reg_number'];
        $reg_number_national = @$data['reg_number_national'];
        $user_data['reg_number'] = $reg_number;
        $user_data['reg_number_national'] = $reg_number_national;

        $user_data['name'] = $name;
        $user_data['gender'] = $gender;
        $user_data['email'] = $email;
        $user_data['phone'] = $phone;
        $user_data['password'] = $password;
        $user_data['birth_day'] = $birth_day;
        $user_data['address'] = $address;
        $user_data['country_id'] = $country_id;
        $user_data['language'] = $language;
        
        $fixedData[] = $user_data;
    }

    echo generateTable($rawHeader, $rawData, $fixedData);
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
    $rawHeader = array();
    $rawData = array();
    $row = 1;
    for ($col = 0; $col < $highestColumnIndex; ++$col) {
        $rawHeader[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        $fieldArray[$col] = strtolower($rawHeader[$col]);
    }

    $fixedData = array();

    for($row = 2; $row <= $highestRow; ++$row) 
    {
        $data = array();
        $values = array();
        for ($col = 0; $col < $highestColumnIndex; ++$col) 
        {
            $cellValue = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            $values[$col] = $cellValue;
            $data[$fieldArray[$col]] = $picoEdu->trimWhitespace($cellValue);
        }
        $rawData[] = $values;

        $reg_number = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number']);
        $reg_number_national = $picoEdu->filterSanitizeDoubleSpace(@$data['reg_number_national']);

        $name = $picoEdu->filterSanitizeName(@$data['name']);

        $address = (trim(@$data['address']));
        $phone = ($picoEdu->fixPhone(trim(@$data['phone'])));
        $email = (trim(@$data['email']));
        $email = $picoEdu->filterEmailAddress($email);

        $gender = $picoEdu->mapGender((trim(@$data['gender'])));
        $birth_place = (trim(@$data['birth_place']));
        $bd = isset($data['birth_day']) ? ((int) $data['birth_day']) : 0;
        $birth_day = substr(excel2MySQLDate($bd), 0, 10);

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

        $reg_number = @$data['reg_number'];
        $reg_number_national = @$data['reg_number_national'];
        $user_data['reg_number'] = $reg_number;
        $user_data['reg_number_national'] = $reg_number_national;

        $user_data['name'] = $name;
        $user_data['gender'] = $gender;
        $user_data['email'] = $email;
        $user_data['phone'] = $phone;
        $user_data['password'] = $password;
        $user_data['birth_day'] = $birth_day;
        $user_data['address'] = $address;
        $user_data['country_id'] = $country_id;
        $user_data['language'] = $language;
        
        $fixedData[] = $user_data;
    }
    echo generateTable($rawHeader, $rawData, $fixedData);
} catch (Exception $e) {
    // Do nothing
}

// import data teacher
