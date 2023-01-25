<?php
include_once dirname(dirname(__FILE__))."/lib.inc/functions-pico.php";
include_once dirname(dirname(__FILE__))."/lib.inc/sessions.php";
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
	$reg_number = kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_ALPHANUMERICPUNC);
	$password = md5(kh_filter_input(INPUT_POST, 'password', FILTER_SANITIZE_PASSWORD));
	$_SESSION['student_username'] = $username;
	$_SESSION['student_password'] = $password;
									 
	$sql = "SELECT `username`, `student_id`
	from `edu_student`
	where (
		(`email` like '$email' and `email` != '')
		or 
		(`reg_number` like '$reg_number' and `reg_number` != '')
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
		include_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
	include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
	if(@$student_id)
	{
		header('Location: index.php');
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
