<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth.php";

$mlid = $member_login->member_id;

$json = array('registered'=>0);

if(isset($_POST['username']))
{
	$username = ltrim(kh_filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING_NEW), " \r\n\t0 ");
	$username = $picoEdu->getValidUsername($username);
	if($username != '')
	{
		$sql = "SELECT `member_id`, `email`, `username`
		from `member`
		where `username` like '$username'
		and `member_id` != '$mlid'
		";
		$stmt = $database->executeQuery($sql);
		$json = array('registered'=>0, 'corrected'=>$username, 'valid'=>true);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$json = array('registered'=>1, 'corrected'=>$username, 'valid'=>false);
		}
	}
	else
	{
			$json = array('registered'=>0, 'corrected'=>$username, 'valid'=>false);
	}
	echo json_encode($json);
}
