<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/sessions.php";
$username = '';
$password = '';

if(isset($_SESSION['student_username']))
{
$username = $_SESSION['student_username'];
}

if(isset($_SESSION['student_password']))
{
$password = $_SESSION['student_password'];
}

$student_login = new StudenAuth($database, $username, $password, false);

$student_id = 0;
$school_id = 0;
$class_id = 0;
$auth_student_school_id = 0;
$auth_school_id = 0;
$use_token = 0;
if($student_login->student_id)
{
	$student_id = $auth_student_id = $student_login->student_id;
	$student_name = $student_login->name;
	$school_id = $auth_student_school_id = $auth_school_id = $student_login->school_id;
	$school_code = $student_login->school_code;
	$class_id = $student_login->class_id;
	$use_token = $student_login->use_token;
}
