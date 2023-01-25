<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
include_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
	$password = md5(kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD));
									 
	$_SESSION['admin_username'] = $username;
	$_SESSION['admin_password'] = $password;
	$sql = "SELECT `username`, `admin_id` as `member_id`, `admin_id` as `admin_id`
	from `edu_admin`
	where (
		(`email` like '$email' and `email` != '')
		or 
		(`username` like '$username' and `username` != '')
		or 
		(`phone` like '$phone' and `phone` != '')
		) 
		and `password` like md5('$password')
		and `active` = '1'
		and `blocked` = '0'
	";
	$stmt = $database->executeQuery($sql);

	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$_SESSION['admin_username'] = $data['username'];
		$_SESSION['admin_password'] = $password;
		if(isset($_POST['ref']))
		{
			$ref = $_POST['ref'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref);
			}
			else
			{
				header('Location: index.php');
			}
		}
		else if(isset($_SERVER['HTTP_REFERER']))
		{
			$ref = $_SERVER['HTTP_REFERER'];
			if(stripos($ref, 'login.php') === false)
			{
				header('Location: '.$ref);
			}
			else
			{
				header('Location: index.php');
			}
		}
		else
		{
			header('Location: index.php');
		}
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
	include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
	if(@$admin_id)
	{
		header('Location: index.php');
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
