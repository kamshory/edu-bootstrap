<?php
include_once dirname(__FILE__)."/functions-pico.php";
include_once dirname(__FILE__)."/member-auth.php";
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

$member_id = "";
$memberLoggedIn = new \Pico\AuthMember($database, $username, $password, false);
$memberLoggedIn->login();

if(!empty($memberLoggedIn->member_id))
{
	$member_id = $memberLoggedIn->member_id;
}
if($memberLoggedIn->member_id)
{
	// Do nothing
}

