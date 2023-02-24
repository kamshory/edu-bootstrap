<?php
require_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
require_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
	$reg_number = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$password = md5(kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD));
	$_SESSION['student_username'] = $username;
	$_SESSION['student_password'] = $password;
									 
	$sql = "SELECT `username`, `student_id`
	FROM `edu_student`
	where (
		(`email` like '$email' AND `email` != '')
		or 
		(`reg_number` like '$reg_number' AND `reg_number` != '')
		or 
		(`username` like '$username' AND `username` != '')
		or 
		(`phone` like '$phone' AND `phone` != '')
		) 
		AND `password` like md5('$password')
		AND `active` = true
		AND `blocked` = false
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$_SESSION['student_username'] = $data['username'];
		$_SESSION['student_password'] = $password;
		
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
	require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
	if(!empty(@$auth_student_id))
	{
		header('Location: index.php');
	}
	else
	{
		require_once dirname(__FILE__)."/login-form.php";
	}
}
