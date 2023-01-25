<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-admin.php";
if(@$member_login->member_id == 0)
{
	if(empty(@$school_id))
	{
		include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-guru.php";
	}
	include_once dirname(__FILE__)."/conf.php";
	$userlogin = null;
	if((@$admin_id || @$teacher_id) && @$school_id)
	{
		$userlogin = 1;
		$authblogid = 1;
	}
}
else
{
	include_once dirname(__FILE__)."/conf.php";
	$userlogin = 1;
}
