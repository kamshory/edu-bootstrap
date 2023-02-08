<?php
require_once dirname(__FILE__)."/functions-pico.php";
require_once dirname(__FILE__)."/sessions.php";
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

$studentLoggedIn = new \StudenAuth($database, $username, $password, false);

$student_id = '';
$school_id = '';
$class_id = '';
$auth_student_school_id = '';
$auth_school_id = '';
$use_token = false;
if($studentLoggedIn->student_id)
{
	$student_id 
		= $auth_student_id 
		= $studentLoggedIn->student_id;
	$student_name = $studentLoggedIn->name;
	$school_id 
		= $auth_student_school_id 
		= $auth_school_id 
		= $studentLoggedIn->school_id;
	$school_code = $studentLoggedIn->school_code;
	$class_id = $studentLoggedIn->class_id;
	$use_token = $studentLoggedIn->use_token;
}
