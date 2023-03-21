<?php
include_once dirname(dirname(__DIR__))."/lib.inc/auth-admin.php";
if(empty(@$memberLoggedIn->member_id))
{
	if(!isset($school_id) || empty($school_id))
	{
		include_once dirname(dirname(__DIR__))."/lib.inc/auth-guru.php";
	}
	include_once __DIR__."/conf.php";
	$userlogin = null;
	if((@$admin_id || @$teacher_id) && @$school_id)
	{
		$userlogin = 1;
		$authblogid = 1;
	}
}
else
{
	include_once __DIR__."/conf.php";
	$userlogin = 1;
}
