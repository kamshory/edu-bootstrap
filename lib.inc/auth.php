<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/sessions.php";

$username = '';
$password = '';
$member_id = '';
if(isset($_SESSION['username']))
{
$username = $_SESSION['username'];
}

if(isset($_SESSION['password']))
{
$password = $_SESSION['password'];
}

$member_login = new MemberAuth($database, $username, $password, false);
if(@$member_login->member_id)
{
	$member_id = $member_login->member_id;
}
if($member_login->member_id)
{
}

