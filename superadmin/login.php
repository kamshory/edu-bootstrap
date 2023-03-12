<?php
require_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
require_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
	$password = md5(kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD));
									 
	$_SESSION['admin_username'] = $username;
	$_SESSION['admin_password'] = $password;

	$passwordHash = md5($password);

	$sql = "SELECT `username`, `admin_id` AS `member_id`, `admin_id` AS `admin_id`
	FROM `edu_admin`
	where (
		(`email` LIKE '$email' AND `email` != '')
		OR 
		(`username` LIKE '$username' AND `username` != '')
		OR 
		(`phone` LIKE '$phone' AND `phone` != '')
		) 
		AND `password` LIKE '$passwordHash'
		AND `active` = true
		AND `blocked` = false
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
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
		require_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
	require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
	if($adminLoggedIn->admin_level != 1)
	{
		require_once dirname(__FILE__)."/bukan-super-admin.php";
		exit();
	}
	if(!empty($admin_id))
	{
		header('Location: index.php');
	}
	else
	{
		require_once dirname(__FILE__)."/login-form.php";
	}
}
