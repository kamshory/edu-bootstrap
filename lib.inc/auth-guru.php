<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/sessions.php";

$username = '';
$password = '';

if(isset($_SESSION['teacher_username']))
{
$username = $_SESSION['teacher_username'];
}

if(isset($_SESSION['teacher_password']))
{
$password = $_SESSION['teacher_password'];
}

$teacher_login = new TeacherAuth($database, $username, $password, false);

$teacher_id = 0;
$school_id = 0;
$auth_teacher_id = 0;
$auth_school_id = 0;
$auth_teacher_school_id = 0;
$school_code = '';
$use_token = 0;
if($teacher_login->teacher_id)
{
	$teacher_id = $auth_teacher_id = $teacher_login->teacher_id;
	$school_id = $auth_school_id = $auth_teacher_school_id = $teacher_login->school_id;
	$school_code = $teacher_login->school_code;
	$school_name = $teacher_login->school_name;
	$use_token = $teacher_login->use_token;
}

