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

$adminLoggedIn = new \Pico\AuthAdmin($database, $username, $password, false);
$adminLoggedIn->login();

$admin_id = "";
$school_id = "";
$real_school_id = "";
$use_token = false;
$admin_create = "";
$admin_edit = "";
$member_create = "";
$member_edit = "";
$use_national_id = true;

if(!empty($adminLoggedIn->admin_id))
{
	$admin_create 
		= $admin_edit 
		= $member_create 
		= $member_edit 
		= $admin_id 
		= $auth_admin_id 
		= $adminLoggedIn->admin_id . '';
	$school_id 
		= $auth_school_id 
		= $adminLoggedIn->school_id . '';
	$real_school_id 
		= $auth_school_id 
		= $adminLoggedIn->real_school_id . '';
	$school_name = $adminLoggedIn->school_name . '';
	$school_code = $adminLoggedIn->school_code . '';
	$use_token = $adminLoggedIn->use_token . '';
	$use_national_id = $adminLoggedIn->use_national_id?true:false;
}

