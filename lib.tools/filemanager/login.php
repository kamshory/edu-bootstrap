<?php
include __DIR__."/session.php";
include_once __DIR__."/conf.php";

include_once __DIR__."/functions.php";


if($_POST['username'] && $_POST['password'])
{
	$uid = addslashes($_POST['username']);
	$pas = addslashes($_POST['password']);
	$userid = "";
	if(is_array($fmanConfig->users))
	{
		foreach($fmanConfig->users as $user)
		{
			$match = matchUser($user, $uid, $pas);
			if($match)
			{
				$userid = $user[0];
				break;
			}
		}
		if($userid)
		{
			$_SESSION['userid'] = $userid;
			if(strlen(@$_POST['ref']))
			{
				$ref = @$_POST['ref'];
				header("Location: $ref");
				exit();
			}
			else
			{
				header("Location: ./");
				exit();
			}
		}
		else
		{
			if(strlen(@$_POST['ref']))
			{
				$ref = $_POST['ref'];
				header("Location: $ref");
				exit();
			}
		}
	}
}

if(!isset($_SESSION['userid']))
{
	include_once __DIR__."/tool-login-form.php";
}
else
{
	header("Location: ./");
}
