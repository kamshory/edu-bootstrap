<?php
include dirname(__FILE__)."/session.php";
include_once dirname(__FILE__)."/conf.php";

include_once dirname(__FILE__)."/functions.php";


if($_POST['username'] && $_POST['password'])
{
	$uid = addslashes($_POST['username']);
	$pas = addslashes($_POST['password']);
	$userid = "";
	if(is_array($cfg->users))
	{
		foreach($cfg->users as $user)
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
	include_once dirname(__FILE__)."/tool-login-form.php";
}
else
{
	header("Location: ./");
}
?>