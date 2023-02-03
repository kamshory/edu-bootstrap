<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/sessions.php";

$username = '';
$password = '';
$user_id = '';

if(isset($_SESSION['admin_username']))
{
	$username = $_SESSION['admin_username'];
}
if(isset($_SESSION['admin_password']))
{
	$password = $_SESSION['admin_password'];
}
$member_login = new \AdminAuth($database, $username, $password, false);

if(empty($member_login->admin_id))
{
    if(isset($_SESSION['teacher_username']))
    {
        $username = $_SESSION['teacher_username'];
    }
    if(isset($_SESSION['teacher_password']))
    {
        $password = $_SESSION['teacher_password'];
    }
    $member_login = new \TeacherAuth($database, $username, $password, false);
    if(empty($member_login->teacher_id))
    {
        $user_id = $member_login->teacher_id;
    }
}
else
{
    $user_id = $member_login->admin_id;
}

if(empty($user_id))
{
    exit();
}