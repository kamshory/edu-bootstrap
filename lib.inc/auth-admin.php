<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/sessions.php";

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
$admin_login = new AdminAuth($database, $username, $password, false);
$admin_id = '';
$school_id = '';
$real_school_id = '';
$use_token = '';
if(!empty($admin_login->admin_id))
{
	$admin_id = $auth_admin_id = $admin_login->admin_id;
	$school_id = $auth_school_id = $admin_login->school_id;
	$real_school_id = $auth_school_id = $admin_login->real_school_id;
	$school_name = $admin_login->school_name;
	$school_code = $admin_login->school_code;
	$use_token = $admin_login->use_token;
}
