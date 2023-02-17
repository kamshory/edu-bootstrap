<?php
require_once dirname(__FILE__)."/functions-pico.php";
require_once dirname(__FILE__)."/sessions.php";

$username = "";
$password = "";

if(isset($_SESSION['teacher_username']))
{
	$username = $_SESSION['teacher_username'];
}

if(isset($_SESSION['teacher_password']))
{
	$password = $_SESSION['teacher_password'];
}

require_once dirname(__FILE__) . "/classes/TeacherAuth.php";

$teacher_id = "";
$school_id = "";
$auth_teacher_id = "";
$auth_school_id = "";
$auth_teacher_school_id = "";
$school_code = "";
$use_token = false;
$member_create = "";
$member_edit = "";
$admin_create = "";
$admin_edit = "";

$teacherLoggedIn = new \TeacherAuth($database, $username, $password, false);

if(!empty($teacherLoggedIn->getTeacherId()))
{
	$teacher_id 
		= $auth_teacher_id 
		= $member_create
		= $member_edit
		= $teacher_create
		= $teacher_edit
		= $teacherLoggedIn->getTeacherId() . "";
	$school_id 
		= $auth_school_id 
		= $auth_teacher_school_id 
		= $teacherLoggedIn->getSchoolId();
	$school_code = $teacherLoggedIn->getSchoolCode();
	$school_name = $teacherLoggedIn->getSchoolName();
	$use_token = $teacherLoggedIn->getUseToken();
}
