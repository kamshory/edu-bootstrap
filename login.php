<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
if(isset($_SESSION['invalid_login']) && $_SESSION['invalid_login'] > 10)
{
	if(@$_SESSION['last_try'] > (time()-300))
	{
		if(count(@$_POST))
		{
			header("Location: ".$_SERVER['REQUEST_URI']); //NOSONAR
		}
		exit();
	}
	else
	{
		$_SESSION = array();
		
		session_destroy();
	}
}

if(isset($_POST['username']) && isset($_POST['password']))
{
	$post_username = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING_NEW);
	$post_password = kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING_NEW);
	$username = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$phone = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$email = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
	$reg_number = kh_filter_input(INPUT_POST, "username", FILTER_SANITIZE_ALPHANUMERICPUNC);
	$password = md5(kh_filter_input(INPUT_POST, "password", FILTER_SANITIZE_PASSWORD));
	$_SESSION['student_username'] = $username;
	$_SESSION['student_password'] = $password;

	$passwordHash = md5($password);
									 
	$sql = "SELECT `username`, `student_id`
	FROM `edu_student`
	WHERE (
		(`email` LIKE '$email' AND `email` != '')
		OR 
		(`reg_number` LIKE '$reg_number' AND `reg_number` != '')
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
				header('Location: index.php'); //NOSONAR
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
				header('Location: index.php'); //NOSONAR
			}
		}
		else
		{
			header('Location: index.php'); //NOSONAR
		}
	}
	else
	{
		if($post_username != '' && $post_password != '')
		{
			if(!isset($_SESSION['invalid_login']))
			{
				$_SESSION['invalid_login'] = 1;
				$_SESSION['last_try'] = time();
			}
			else
			{
				$_SESSION['invalid_login'] = $_SESSION['invalid_login'] + 1;
				$_SESSION['last_try'] = time();
			}			
		}

		include_once dirname(__FILE__)."/login-form.php";
	}
}
else
{
	include_once dirname(__FILE__)."/lib.inc/auth-siswa.php";
	if(!empty(@$student_id))
	{
		header('Location: index.php'); //NOSONAR
	}
	else
	{
		include_once dirname(__FILE__)."/login-form.php";
	}
}
