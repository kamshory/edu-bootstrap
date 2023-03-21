<?php
include_once dirname(__DIR__)."/lib.inc/auth.php";
$mlid = $memberLoggedIn->member_id;
$json = array('registered'=>0);

if(isset($_POST['email']))
{
	$email = kh_filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$sql = "SELECT `member_id`, `email`, `username`
	FROM `member`
	WHERE `email` like '$email'
	AND `member_id` != '$mlid'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		$json = array('registered'=>1);
	}
	echo json_encode($json);
}
