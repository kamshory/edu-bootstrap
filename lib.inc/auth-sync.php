<?php
require_once dirname(__FILE__)."/functions-pico.php";
require_once dirname(__FILE__)."/sessions.php";
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
$memberLoggedIn = new \AdminAuth($database, $username, $password, false);

if(empty($memberLoggedIn->admin_id))
{
    if(isset($_SESSION['teacher_username']))
    {
        $username = $_SESSION['teacher_username'];
    }
    if(isset($_SESSION['teacher_password']))
    {
        $password = $_SESSION['teacher_password'];
    }
    $memberLoggedIn = new \TeacherAuth($database, $username, $password, false);
    if(empty($memberLoggedIn->teacher_id))
    {
        $user_id = $memberLoggedIn->teacher_id;
    }
}
else
{
    $user_id = $memberLoggedIn->admin_id;
}

if(empty($user_id))
{
    exit();
}