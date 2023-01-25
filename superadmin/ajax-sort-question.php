<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
if(isset($_POST['sort']))
{
	$array_question = kh_filter_input(INPUT_POST, 'array_question', FILTER_SANITIZE_STRING_NEW);
	$arr = explode(",", trim($array_question, " , "));
	foreach($arr as $key=>$val)
	{
		$order = $key+1;
		$val = addslashes($val);
		$sql = "update `edu_question` set `order` = '$order' where `question_id` = '$val'";// and `school_id` = '$school_id'; ";
		$database->executeUpdate($sql);
	}
}

