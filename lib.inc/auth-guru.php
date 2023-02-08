<?php
require_once dirname(__FILE__)."/functions-pico.php";
require_once dirname(__FILE__)."/sessions.php";

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

$teacherLoggedIn = new \TeacherAuth($database, $username, $password, false);

$teacher_id = '';
$school_id = '';
$auth_teacher_id = '';
$auth_school_id = '';
$auth_teacher_school_id = '';
$school_code = '';
$use_token = false;
$member_create = '';
$member_edit = '';
$admin_create = '';
$admin_edit = '';
if($teacherLoggedIn->teacher_id)
{
	$teacher_id 
		= $auth_teacher_id 
		= $member_create
		= $member_edit
		= $teacher_create
		= $teacher_edit
		= $teacherLoggedIn->teacher_id . '';
	$school_id 
		= $auth_school_id 
		= $auth_teacher_school_id 
		= $teacherLoggedIn->school_id . '';
	$school_code = $teacherLoggedIn->school_code . '';
	$school_name = $teacherLoggedIn->school_name . '';
	$use_token = $teacherLoggedIn->use_token;
}

if(!isset($cfg))
{
	$cfg = new stdClass();
}