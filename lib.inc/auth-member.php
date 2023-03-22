<?php
require_once __DIR__."/functions-pico.php";
require_once __DIR__."/sessions.php";

$username = '';
$password = '';

if(isset($_SESSION['admin_username']))
{
	$username = $_SESSION['admin_username'];
}
if(isset($_SESSION['admin_password']))
{
	$password = $_SESSION['admin_password'];
}

$adminLoggedIn = new \Pico\AuthMember($database, $username, $password, false);
$adminLoggedIn->login();
$member_id = "";
$school_id = "";
$real_school_id = "";
$use_token = false;
$admin_create = "";
$admin_edit = "";
$member_create = "";
$member_edit = "";
$use_national_id = true;

if(!empty($adminLoggedIn->member_id))
{
	$admin_create 
		= $admin_edit 
		= $member_create 
		= $member_edit 
		= $member_id 
		= $auth_admin_id 
		= $adminLoggedIn->member_id . '';
	
}

